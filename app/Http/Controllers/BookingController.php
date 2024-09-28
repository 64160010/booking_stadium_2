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
        $bookings = BookingDetail::where('booking_date', $date)->get();
    
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
            $bookingStadiumId = \DB::table('booking_stadium')->insertGetId([
                'booking_status' => 'รอการตรวจสอบ',  
                'booking_date' => $validatedData['date'],
                'users_id' => auth()->id(),
                'time_slot_id' => $timeSlotId,  
                'time_slot_stadium_id' => $timeSlotStadiumId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create BookingDetail including booking_stadium_id, time_slot_id, and time_slot_stadium_id
            $bookingDetail = BookingDetail::create([
                'stadium_id' => $stadiumId,
                'booking_stadium_id' => $bookingStadiumId, // Add the booking stadium ID here
                'time_slot_id' => $timeSlotId, // Ensure to add time_slot_id
                'time_slot_stadium_id' => $timeSlotStadiumId, // Add time_slot_stadium_id here
                'booking_total_hour' => 1, 
                'booking_total_price' => 100, 
                'booking_date' => $validatedData['date'],
                'users_id' => auth()->id(),
            ]);
            
        }
    }

    return response()->json(['success' => true]); 
}




}