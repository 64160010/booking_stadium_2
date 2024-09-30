<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingStadium extends Model
{
    use HasFactory;

    protected $table = 'booking_stadium';

    protected $fillable = ['booking_date', 'booking_status', 'users_id', 'time_slot_stadium_id', 'time_slot_id'];
}