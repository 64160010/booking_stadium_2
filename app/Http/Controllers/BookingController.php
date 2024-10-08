<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingStadium;
use App\Models\BookingDetail;
use App\Models\Borrow;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $stadiums = Stadium::all();

    // กำหนดค่า $bookingStadiumId ถ้าต้องการ
    // ตัวอย่างกำหนดค่าเป็น null หรือค่าที่เหมาะสม
        $bookingStadiumId = null;
    
        return view('booking', compact('stadiums', 'date', 'bookingStadiumId'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'timeSlots' => 'required|array' // ตรวจสอบว่า timeSlots เป็น array หรือไม่
        ]);

        try {
            // ตรวจสอบว่ามีการสร้าง booking_stadium_id ใน session หรือยัง
            $existingBookingStadiumId = session('booking_stadium_id');

            // หากยังไม่มี booking_stadium_id ให้สร้างใหม่
            if (!$existingBookingStadiumId) {
                $bookingStadium = BookingStadium::create([
                    'booking_status' => 'รอการชำระเงิน',
                    'booking_date' => $validatedData['date'],
                    'users_id' => auth()->id(),
                ]);

                // เก็บ booking_stadium_id ใน session
                $existingBookingStadiumId = $bookingStadium->id;
                session(['booking_stadium_id' => $existingBookingStadiumId]);
            }

            // วนลูปตาม stadiumId และช่วงเวลาที่เลือก
            foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
                foreach ($timeSlots as $timeSlot) {
                    // ดึงข้อมูลของช่วงเวลา
                    $timeSlotData = \DB::table('time_slot')
                        ->where('time_slot', $timeSlot)
                        ->where('stadium_id', $stadiumId)
                        ->first();

                    // ถ้าไม่พบข้อมูลช่วงเวลา ให้คืนค่าผิดพลาด
                    if (!$timeSlotData) {
                        return response()->json(['success' => false, 'message' => 'เวลาหรือสนามไม่ถูกต้อง.']);
                    }

                    // ดึงข้อมูลของสนามเพื่อคำนวณราคา
                    $stadium = Stadium::find($stadiumId);
                    if (!$stadium) {
                        return response()->json(['success' => false, 'message' => 'สนามไม่ถูกต้อง.']);
                    }

                    // คำนวณราคาทั้งหมดโดยใช้ราคาต่อชั่วโมง
                    $totalPrice = $stadium->stadium_price * count($timeSlots);

                    // บันทึกข้อมูลใน booking_detail
                    BookingDetail::create([
                        'stadium_id' => $stadiumId,
                        'booking_stadium_id' => $existingBookingStadiumId,
                        'booking_total_hour' => count($timeSlots), // จำนวนชั่วโมง
                        'booking_total_price' => $totalPrice, // ราคาทั้งหมด
                        'booking_date' => $validatedData['date'],
                        'users_id' => auth()->id(),
                        'time_slot_id' => $timeSlotData->id, // บันทึก time_slot_id จากข้อมูล time_slot
                    ]);
                }
            }

            // ส่งคืนค่า JSON เมื่อสำเร็จ พร้อม booking_stadium_id
            return response()->json([
                'success' => true,
                'booking_stadium_id' => $existingBookingStadiumId
            ]);
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    public function show($booking_stadium_id)
{
    // ดึงข้อมูลการจองของผู้ใช้ที่ล็อกอินอยู่
    $userId = auth()->id(); // รับ ID ของผู้ใช้ที่ล็อกอิน
    $bookingDetails = BookingDetail::where('users_id', $userId)
                                    ->where('booking_stadium_id', $booking_stadium_id)
                                    ->get();

    // รวมข้อมูลตามสนามและวันที่
    $groupedBookingDetails = $bookingDetails->groupBy(function ($item) {
        return $item->stadium_id . '|' . $item->booking_date; // รวมตาม stadium_id และ booking_date
    })->map(function ($group) {
        // รวมช่วงเวลาในแต่ละกลุ่ม
        $timeSlots = $group->pluck('timeSlot.time_slot')->join(', ');
        return [
            'stadium_name' => $group->first()->stadium->stadium_name,
            'booking_date' => $group->first()->booking_date,
            'time_slots' => $timeSlots,
            'total_price' => $group->sum('booking_total_price'),
            'total_hours' => $group->sum('booking_total_hour'),
        ];
    })->values();

    // ดึงรายละเอียดการยืมตาม booking_stadium_id
    $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get();

    // กำหนดข้อความเมื่อไม่มีข้อมูลการจอง
    $message = $bookingDetails->isEmpty() ? 'ไม่มีรายการจอง' : null;

    return view('bookingDetail', compact('groupedBookingDetails', 'borrowingDetails', 'message', 'bookingDetails'));
}

    

// ฟังก์ชันนี้ใช้เพื่อส่งค่าที่ดึงมาจากการยืมไปยัง borrow-item
public function showBorrowItem($booking_stadium_id)
{
    // ดึงรายละเอียดการยืมตาม booking_stadium_id
    $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get();
    
    // ดึงวันที่จาก borrowingDetails
    $availableBorrowDates = $borrowingDetails->pluck('borrow_date')->unique(); // สมมติว่าคุณมีฟิลด์ borrow_date

    // กำหนดข้อความเมื่อไม่มีข้อมูลการยืม
    $borrowMessage = $borrowingDetails->isEmpty() ? 'ไม่มีรายการยืม' : null;

    // ส่งค่าผ่านมุมมอง borrow-item
    return view('borrow-item', compact('availableBorrowDates', 'borrowingDetails', 'borrowMessage'));
}
    



public function destroy($id)
{
    // ค้นหาข้อมูลการจอง
    $bookingDetail = BookingDetail::findOrFail($id);

    // ลบข้อมูลการจอง
    $bookingDetail->delete();

    // ส่ง response กลับไปที่หน้าเว็บ
    return response()->json(['success' => 'ลบรายการจองสำเร็จ']);
}



}