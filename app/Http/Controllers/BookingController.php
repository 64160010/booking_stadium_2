<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingStadium;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{

    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $stadiums = Stadium::all();
        $bookings = BookingStadium::where('booking_date', $date)->get();

        return view('booking', compact('stadiums', 'bookings', 'date'));
    }

   
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'timeSlots' => 'required|array'
        ]);
    
        foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
            foreach ($timeSlots as $timeSlot) {
                // ดึง ID ของ time slot
                $timeSlotId = \DB::table('time_slot')
                    ->where('time_slot', $timeSlot) // ช่วงเวลาที่ส่งมาจากฟอร์ม
                    ->value('id'); // ดึง ID ของช่วงเวลานั้น
    
                if (!$timeSlotId) {
                    return response()->json(['success' => false, 'message' => 'เวลานี้ไม่ถูกต้อง']);
                }
    
                // ตรวจสอบการจองซ้ำ
                $existingBooking = BookingStadium::where('booking_date', $validatedData['date'])
                    ->where('time_slot_id', $timeSlotId)
                    ->exists();
    
                if ($existingBooking) {
                    return response()->json(['success' => false, 'message' => 'เวลานี้ถูกจองแล้ว']);
                }
    
                // บันทึกการจอง
                BookingStadium::create([
                    'booking_date' => $validatedData['date'],
                    'time_slot_id' => $timeSlotId,
                    'user_id' => auth()->id(),
                    'booking_status' => 'รอการตรวจสอบ'
                ]);
            }
        }
    
        return response()->json(['success' => true]); 
    }
    

}

