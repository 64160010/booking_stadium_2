<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stadium;
use App\Models\BookingStadium;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));

        // Retrieve stadiums and bookings
        $stadiums = Stadium::all();
        $bookings = BookingStadium::where('booking_date', $date)->get();

        return view('booking', compact('stadiums', 'bookings', 'date'));
    }

    public function showBookings(Request $request)
{
    $date = $request->input('date', now()->toDateString());

    $stadiums = Stadium::all();
    $bookings = Booking::whereDate('start_time', $date)->get();

    dd($stadiums, $bookings); // ตรวจสอบข้อมูล
    $stadiums = [
        (object)['id' => 1, 'stadium_name' => 'สนาม 1', 'stadium_price' => 1300],
        (object)['id' => 2, 'stadium_name' => 'สนาม 2', 'stadium_price' => 1500],
    ];
    
    $bookings = collect([
        (object)['stadium_id' => 1, 'start_time' => \Carbon\Carbon::createFromFormat('H:i', '11:00'), 'booking_status' => 1],
        (object)['stadium_id' => 2, 'start_time' => \Carbon\Carbon::createFromFormat('H:i', '12:00'), 'booking_status' => 0],
    ]);
    
    return view('booking', compact('stadiums', 'bookings', 'date'));
    
}



}

