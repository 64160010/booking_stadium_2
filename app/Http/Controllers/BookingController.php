<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingStadium;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $stadiums = Stadium::all();
    
        return view('booking', compact('stadiums', 'date'));
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'date' => 'required|date',
        'timeSlots' => 'required|array'
    ]);

    try {
        // สร้างข้อมูลใน booking_stadium ก่อน
        $bookingStadium = BookingStadium::create([
            'booking_status' => 'รอการชำระเงิน',
            'booking_date' => $validatedData['date'],
            'users_id' => auth()->id(),
        ]);

        // ใช้ booking_stadium_id จากการบันทึกข้างต้น
        $bookingStadiumId = $bookingStadium->id;

        foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
            foreach ($timeSlots as $timeSlot) {
                // ดึงข้อมูลเวลาที่จอง
                $timeSlotData = \DB::table('time_slot')
                    ->where('time_slot', $timeSlot)
                    ->where('stadium_id', $stadiumId)
                    ->first(['id', 'stadium_id']);

                if (!$timeSlotData) {
                    return response()->json(['success' => false, 'message' => 'เวลาหรือสนามไม่ถูกต้อง.']);
                }

                $timeSlotId = $timeSlotData->id;

                // ดึงราคาจากโมเดล Stadium
                $stadium = Stadium::find($stadiumId);
                if (!$stadium) {
                    return response()->json(['success' => false, 'message' => 'สนามไม่ถูกต้อง.']);
                }

                // คำนวณราคาโดยใช้ราคาจาก stadium และจำนวนชั่วโมง
                $totalPrice = $stadium->stadium_price * count($timeSlots); // คำนวณราคา

                // บันทึกข้อมูลใน booking_detail
                BookingDetail::create([
                    'stadium_id' => $stadiumId,
                    'booking_stadium_id' => $bookingStadiumId,
                    'booking_total_hour' => count($timeSlots), // กำหนดชั่วโมง
                    'booking_total_price' => $totalPrice, // ใช้ราคาจริง
                    'booking_date' => $validatedData['date'],
                    'users_id' => auth()->id(),
                    'time_slot_id' => $timeSlotId,
                    'time_slot_stadium_id' => $stadiumId // หรือค่าอื่น ๆ ที่เหมาะสม
                ]);
            }
        }

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}
}
