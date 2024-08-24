<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stadium;
use App\Models\BookingStadium;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // รับวันที่จาก query string หรือใช้วันที่ปัจจุบัน
        $date = $request->query('date', date('Y-m-d'));

        // ดึงข้อมูลสนามและการจอง
        $stadiums = Stadium::all();
        $bookings = BookingStadium::where('booking_date', $date)->get();

        // ส่งข้อมูลไปยัง view
        return view('booking', compact('stadiums', 'bookings', 'date'));
    }

    // ลบหรือปรับปรุงวิธีการ showBookings ตามความต้องการของคุณ
    public function showBookings(Request $request)
    {
        // รับวันที่จาก request หรือใช้วันที่ปัจจุบัน
        $date = $request->input('date', now()->toDateString());

        // ดึงข้อมูลสนามและการจอง
        $stadiums = Stadium::all();
        $bookings = BookingStadium::whereDate('start_time', $date)->get();

        // ตรวจสอบข้อมูล (ลบหลังจากทดสอบเสร็จ)
        dd($stadiums, $bookings);

        // ตัวอย่างข้อมูลสนามและการจอง (ลบหากใช้งานจริง)
        $stadiums = [
            (object)['id' => 1, 'stadium_name' => 'สนาม 1', 'stadium_price' => 1300],
            (object)['id' => 2, 'stadium_name' => 'สนาม 2', 'stadium_price' => 1500],
        ];

        $bookings = collect([
            (object)['stadium_id' => 1, 'start_time' => \Carbon\Carbon::createFromFormat('H:i', '11:00'), 'booking_status' => 1],
            (object)['stadium_id' => 2, 'start_time' => \Carbon\Carbon::createFromFormat('H:i', '12:00'), 'booking_status' => 0],
        ]);

        // ส่งข้อมูลไปยัง view
        return view('booking', compact('stadiums', 'bookings', 'date'));
    }
}
