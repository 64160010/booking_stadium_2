<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingDetail;
use App\Models\BookingStadium;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{

    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $stadiums = Stadium::all();
        $bookings = BookingDetail::where('booking_date', $date)->get();
    
        return view('booking', compact('stadiums', 'bookings', 'date'));
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'date' => 'required|date',
        'timeSlots' => 'required|array'
    ]);

    try {
        foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
            foreach ($timeSlots as $timeSlot) {
                // Retrieve the time slot data
                $timeSlotData = \DB::table('time_slot')
                    ->where('time_slot', $timeSlot)
                    ->where('stadium_id', $stadiumId)
                    ->first(['id', 'stadium_id']); 

                if (!$timeSlotData) {
                    return response()->json(['success' => false, 'message' => 'Invalid time or stadium.']);
                }

                $timeSlotId = $timeSlotData->id;
                $timeSlotStadiumId = $timeSlotData->stadium_id;

                // Save to booking_stadium and get the ID
                $bookingStadium = BookingStadium::create([
                    'booking_status' => 'รอการชำระเงิน',  
                    'booking_date' => $validatedData['date'],
                    'users_id' => auth()->id(),
                    'time_slot_id' => $timeSlotId,  
                    'time_slot_stadium_id' => $timeSlotStadiumId,
                ]);

                // บันทึก id ลงใน session
                session(['booking_stadium_id' => $bookingStadium->id]);

                // Create BookingDetail
                BookingDetail::create([
                    'stadium_id' => $stadiumId,
                    'booking_stadium_id' => $bookingStadium->id,
                    'time_slot_id' => $timeSlotId,
                    'time_slot_stadium_id' => $timeSlotStadiumId,
                    'booking_total_hour' => 1, 
                    'booking_total_price' => 100, 
                    'booking_date' => $validatedData['date'],
                    'users_id' => auth()->id(),
                ]);
            }
        }

        if ($bookingStadium) {
            return redirect()->route('bookingdetail', ['id' => $bookingStadium->id])
                             ->with('success', 'ระบบเพิ่มรายการแล้ว โปรดตรวจสอบรายการอีกครั้งก่อนชำระเงิน');
        } else {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการสร้างการจอง');
        }

    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}

public function confirmBooking(Request $request)
{
    // บันทึกการจอง
    $bookingStadium = BookingStadium::create([
        'booking_status' => 'รอการชำระเงิน',  
        'booking_date' => $request->date,
        'users_id' => auth()->id(),
        // ... (ข้อมูลเพิ่มเติมที่ต้องการ)
    ]);

    // เก็บ id การจองใน session
    session(['booking_stadium_id' => $bookingStadium->id]);
    session(['booking_detail' => $bookingDetail]);

    return response()->json(['success' => true]);
}

public function showBookingDetails()
{
    $bookingDetail = session('booking_detail');

    if (!$bookingDetail) {
        return redirect()->back()->with('error', 'ไม่พบข้อมูลการจอง');
    }

    // ทำการแสดงรายละเอียดการจอง
    return view('bookingdetail', compact('bookingDetail'));
}








}



