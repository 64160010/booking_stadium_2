<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingStadium extends Model
{
    use HasFactory;
    protected $table = 'booking_stadium'; // ชื่อของตารางในฐานข้อมูล
    protected $fillable = ['booking_date', 'start_time', 'end_time', 'booking_status'];
}
