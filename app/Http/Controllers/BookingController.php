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
    // ตรวจสอบข้อมูลที่รับเข้ามา
    $validatedData = $request->validate([
        'date' => 'required|date|after_or_equal:today',
        'timeSlots' => 'required|array',
        'timeSlots.*' => 'array', // ตรวจสอบให้แน่ใจว่า timeSlots เป็น array ของ arrays
        'timeSlots.*.*' => 'string', // timeSlots ต้องเป็น string
    ]);

    $date = $validatedData['date'];
    $timeSlots = $validatedData['timeSlots'];

    // บันทึกข้อมูลการจอง
    foreach ($timeSlots as $stadiumId => $slots) {
        foreach ($slots as $slot) {
            Booking::create([
                'stadium_id' => $stadiumId,
                'booking_date' => $date,
                'time_slot' => $slot,
                // เพิ่มฟิลด์อื่นๆ ที่จำเป็น
            ]);
        }
    }

    return response()->json(['success' => true]);
}


public function confirmation(Request $request)
{
    $date = $request->input('date');
    $stadiumsData = json_decode($request->input('stadiums'), true);

    if (!$date || !$stadiumsData) {
        return redirect()->route('booking')->withErrors('ข้อมูลไม่ครบถ้วน');
    }

    // ดึงข้อมูลสนามและเวลาจากฐานข้อมูล
    $stadiums = Stadium::whereIn('id', array_keys($stadiumsData))->get();

    $totalPrice = 0;
    foreach ($stadiums as $stadium) {
        foreach ($stadiumsData[$stadium->id] as $timeSlot) {
            // คำนวณราคาสำหรับการจอง
            $totalPrice += $stadium->stadium_price;
        }
    }

    return view('confirmation', [
        'date' => $date,
        'stadiumsData' => $stadiumsData,
        'stadiums' => $stadiums,
        'totalPrice' => $totalPrice
    ]);
}


}
