<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{

    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $stadiums = Stadium::all();
        $bookings = BookingDetail::where('booking_date', $date)->get(); // เปลี่ยนจาก BookingStadium เป็น BookingDetail
    
        return view('booking', compact('stadiums', 'bookings', 'date'));
    }
    

   
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'date' => 'required|date',
        'timeSlots' => 'required|array'
    ]);

    foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
        // สร้าง BookingDetail ก่อน
        $bookingDetail = BookingDetail::create([
            'stadium_id' => $stadiumId,
            'booking_total_hour' => 1, // กำหนดจำนวนชั่วโมงการจอง
            'booking_total_price' => 100, // กำหนดราคาการจอง
            'booking_date' => $validatedData['date'],
            'booking_status' => 'รอการตรวจสอบ',
            'users_id' => auth()->id(),
        ]);

        foreach ($timeSlots as $timeSlot) {
            // ดึง ID ของ time slot
            $timeSlotId = \DB::table('time_slot')
                ->where('time_slot', $timeSlot) // ช่วงเวลาที่ส่งมาจากฟอร์ม
                ->value('id'); // ดึง ID ของช่วงเวลานั้น

            if (!$timeSlotId) {
                return response()->json(['success' => false, 'message' => 'เวลานี้ไม่ถูกต้อง']);
            }

            // ตรวจสอบการจองซ้ำ
            $existingBooking = \DB::table('time_slot_has_booking_detail')
                ->where('time_slot_id', $timeSlotId)
                ->where('booking_detail_id', $bookingDetail->id) // เช็คกับ booking_detail ที่เพิ่งสร้าง
                ->exists();

            if ($existingBooking) {
                return response()->json(['success' => false, 'message' => 'เวลานี้ถูกจองแล้ว']);
            }

            // บันทึกความสัมพันธ์ใน time_slot_has_booking_detail
            \DB::table('time_slot_has_booking_detail')->insert([
                'time_slot_id' => $timeSlotId,
                'booking_detail_id' => $bookingDetail->id,
            ]);
        }
    }

    return response()->json(['success' => true]); 
}

    
    
}