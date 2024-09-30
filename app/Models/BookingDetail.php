<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $table = 'booking_detail';

    protected $fillable = [
        'stadium_id', 
        'booking_stadium_id', 
        'booking_total_hour', 
        'booking_total_price', 
        'booking_status', 
        'booking_date', 
        'users_id',
        'time_slot_id ',
        'time_slot_stadium_id ',
    ];

    // ความสัมพันธ์กับตาราง Stadium
    public function stadium()
    {
        return $this->belongsTo(Stadium::class, 'stadium_id');
    }

    // ความสัมพันธ์กับตาราง BookingStadium
    public function bookingStadium()
    {
        return $this->belongsTo(BookingStadium::class);
    }

    // ความสัมพันธ์กับตาราง Users
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}