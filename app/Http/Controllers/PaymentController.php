<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentBooking;
use App\Models\BookingStadium;
use App\Models\Borrow;

class PaymentController extends Controller
{
    public function showPaymentForm($bookingId)
    {
        // ดึงข้อมูลการจองสนาม
        $booking = BookingStadium::find($bookingId);
    
        // ดึงรายการการจองทั้งหมดของผู้ใช้
        $bookings = BookingStadium::where('users_id', auth()->id())->get();
    
        // ส่งข้อมูลไปยัง view
        return view('paymentBooking', [
            'booking' => $booking,
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

    // ตรวจสอบว่ามีรายการยืมอุปกรณ์ที่เชื่อมโยงกับการจองนี้หรือไม่
$borrowItems = Borrow::where('booking_stadium_id', $request->input('booking_code'))->get();
if ($borrowItems->isNotEmpty()) {
    foreach ($borrowItems as $borrow) {
        $borrow->borrow_status = 'รอการตรวจสอบ'; // เปลี่ยนสถานะการยืมเป็น รอการตรวจสอบ
        $borrow->save();
    }
}


    return redirect()->route('history.booking')->with('success', 'การชำระเงินถูกบันทึกเรียบร้อยแล้ว');
}

public function historyBooking()
{
    // ดึงข้อมูลการจองของผู้ใช้ที่มีสถานะ 'รอการตรวจสอบ' เท่านั้น
    $bookings = BookingStadium::where('users_id', auth()->id())
                    ->where('booking_status', 'รอการตรวจสอบ')
                    ->with(['payment', 'borrow', 'details'])
                    ->get();

    return view('history-booking', compact('bookings'));
}





}
