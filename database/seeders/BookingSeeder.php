<?php

use Illuminate\Database\Seeder;
use App\Models\Booking;

class BookingSeeder extends Seeder
{
    public function run()
    {
        Booking::create([
            'field_id' => 1,
            'booking_date' => now(),
            'time_slot' => '11.00-12.00',
        ]);

        Booking::create([
            'field_id' => 2,
            'booking_date' => now(),
            'time_slot' => '12.00-13.00',
        ]);
    }
}

