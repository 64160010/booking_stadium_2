<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentBooking;
use App\Models\BookingStadium;
use App\Models\Borrow;
use App\Models\BorrowDetail;
use App\Models\Item;

class PaymentController extends Controller
{
    public function showPaymentForm()
    {
        // ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบ');
        }
    
        // ดึงข้อมูลการจองล่าสุดของผู้ใช้
        $latestBooking = BookingStadium::where('users_id', $userId)
                                        ->orderBy('created_at', 'desc')
                                        ->first();
    
        // หากไม่มีการจอง ให้แสดงข้อความหรือจัดการตามที่ต้องการ
        if (!$latestBooking) {
            return redirect()->route('home')->with('error', 'คุณยังไม่มีการจองสนาม');
        }
    
        // ดึงรายการการจองทั้งหมดของผู้ใช้ (ถ้าจำเป็น)
        $bookings = BookingStadium::where('users_id', $userId)->get();
    
        // ส่งข้อมูลไปยัง view
        return view('paymentBooking', [
            'booking' => $latestBooking, // ใช้รหัสการจองล่าสุด
            'bookings' => $bookings, // ส่งตัวแปร bookings ไปยังวิว
        ]);
    }
    
    
   // ประมวลผลการชำระเงิน
public function processPayment(Request $request)
{
    // ตรวจสอบข้อมูลที่รับมา
    $validatedData = $request->validate([
        'booking_code' => 'required|exists:booking_stadium,id',
        'payer_name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:15',
        'select_bank' => 'required',
        'transfer_datetime' => 'required|date',
        'transfer_amount' => 'required|numeric',
        'transfer_slip' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // จัดการการอัปโหลดไฟล์สลิป
    if ($request->hasFile('transfer_slip')) {
        // อัปโหลดไฟล์ไปที่ storage/app/public/slips
        $fileName = time() . '.' . $request->transfer_slip->extension();
        $filePath = $request->file('transfer_slip')->storeAs('public/slips', $fileName);
    }

    // ตรวจสอบว่ามีการยืมอุปกรณ์ใน booking_stadium_id นี้หรือไม่
    $borrow = Borrow::where('booking_stadium_id', $request->input('booking_code'))->first();

    // บันทึกข้อมูลการชำระเงิน
    $payment = new PaymentBooking();
    $payment->amount = $request->input('transfer_amount');
    $payment->confirmation_pic = $fileName;
    $payment->booking_stadium_id = $request->input('booking_code');
    $payment->payer_name = $request->input('payer_name');
    $payment->phone_number = $request->input('phone_number');
    $payment->bank_name = $request->input('select_bank'); // สมมุติว่าชื่อธนาคารถูกส่งมาใน select
    $payment->transfer_datetime = $request->input('transfer_datetime');

    if ($borrow) {
        $payment->borrow_id = $borrow->id; // เก็บ borrow_id ถ้ามี
    }

    $payment->save();

    // เปลี่ยนสถานะของ booking_stadium เป็น 'รอการตรวจสอบ'
    $booking = BookingStadium::find($request->input('booking_code'));
    $booking->booking_status = 'รอการตรวจสอบ';
    $booking->save();

    // เช็คว่าการจองยังไม่หมดอายุ
    if ($booking->booking_status == 'หมดอายุการชำระเงิน') {
        return redirect()->back()->withErrors('การจองนี้หมดอายุการชำระเงินแล้ว ไม่สามารถทำรายการได้');
    }

    // ตรวจสอบว่ามีรายการยืมอุปกรณ์ที่เชื่อมโยงกับการจองนี้หรือไม่
    $borrowItems = Borrow::where('booking_stadium_id', $request->input('booking_code'))->get();
    if ($borrowItems->isNotEmpty()) {
        foreach ($borrowItems as $borrow) {
            $borrow->borrow_status = 'รอการตรวจสอบ'; // เปลี่ยนสถานะการยืมเป็น รอการตรวจสอบ
            $borrow->save();
        }
    }

    // ดึงข้อมูลการยืมอุปกรณ์ที่เกี่ยวข้องกับการจองนี้
    $borrowDetails = BorrowDetail::whereIn('borrow_id', $borrowItems->pluck('id'))->get();

    foreach ($borrowDetails as $borrowDetail) {
        $item = Item::find($borrowDetail->item_id);

        if ($item) {
            // ลดจำนวนอุปกรณ์ตามจำนวนที่ยืมใน borrow_detail
            $item->item_quantity -= $borrowDetail->borrow_quantity;

            // ตรวจสอบไม่ให้ยอดคงเหลือติดลบ
            if ($item->item_quantity < 0) {
                return redirect()->back()->withErrors("จำนวนอุปกรณ์ในสต็อกไม่เพียงพอสำหรับการยืม: {$item->name}");
            }

            $item->save();
        }
    }

    return redirect()->route('history.booking')->with('success', 'การชำระเงินถูกบันทึกเรียบร้อยแล้ว');
}


public function historyBooking(Request $request)
{
    $user = auth()->user();
    $status = $request->input('status'); // รับค่าจากคำขอ

    // ตรวจสอบว่าผู้ใช้เป็น admin หรือไม่
    if ($user->is_admin == 1) {
        // ถ้ามีการเลือกสถานะ ให้ดึงข้อมูลตามสถานะนั้น แต่ต้องไม่รวมสถานะ 'รอการชำระเงิน'
        $bookings = BookingStadium::when($status, function ($query) use ($status) {
                return $query->where('booking_status', $status)
                    ->where('booking_status', '!=', 'รอการชำระเงิน'); // กรองสถานะ 'รอการชำระเงิน'
            })
            ->where('booking_status', '!=', 'รอการชำระเงิน') // กรองสถานะ 'รอการชำระเงิน'
            ->with(['payment', 'borrow', 'details', 'user'])
            ->get();
    } else {
        // ดึงข้อมูลการจองของผู้ใช้ทั่วไปที่มีสถานะไม่เป็น 'รอการชำระเงิน'
        $bookings = BookingStadium::where('users_id', $user->id)
            ->where('booking_status', '!=', 'รอการชำระเงิน') // กรองสถานะ 'รอการชำระเงิน'
            ->with(['payment', 'borrow', 'details'])
            ->get();
    }

    return view('history-booking', compact('bookings'));
}


public function expirePayment(Request $request)
{
    $bookingCode = $request->input('booking_code');

    // อัปเดตสถานะการจองในตาราง booking_stadium
    $booking = BookingStadium::where('id', $bookingCode)->first();
    if ($booking) {
        $booking->booking_status = 'หมดอายุการชำระเงิน';
        $booking->save();
    } else {
        return response()->json(['status' => 'error', 'message' => 'ไม่พบการจองนี้']);
    }

    // อัปเดตสถานะการยืมในตาราง borrow
    $borrows = Borrow::where('booking_stadium_id', $bookingCode)->get(); // ใช้ get() เพื่ออัปเดตทุกแถวที่ตรงกับ booking_id
    if ($borrows->isNotEmpty()) {
        foreach ($borrows as $borrow) {
            $borrow->borrow_status = 'หมดอายุการชำระเงิน';
            $borrow->save();
        }
    }

    return response()->json(['status' => 'success']); // แก้ไขให้เป็น 'success' แทน 'expired'
}








}
