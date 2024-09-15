<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStadium extends Model
{
    protected $table = 'booking_stadium'; // ชื่อของตารางในฐานข้อมูล
   
    protected $fillable = [
        'booking_date',
        'booking_status',
        'user_id',
        'time_slot_id',
        'stadium_id'
    ];

    public $timestamps = false; // กำหนดเป็น false หากไม่ใช้ timestamps
}

