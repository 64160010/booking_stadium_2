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
        'booking_date', 
        'users_id',
        'time_slot_id',
        
        // คอลัมน์ time_slot_stadium_id อาจจะไม่จำเป็น ถ้าไม่ใช้ในการเชื่อมโยง
    ];

    // ความสัมพันธ์กับตาราง Stadium
    public function stadium()
    {
        return $this->belongsTo(Stadium::class, 'stadium_id');
    }
    

    // ความสัมพันธ์กับตาราง BookingStadium
    public function bookingStadium()
    {
        return $this->belongsTo(BookingStadium::class, 'booking_stadium_id');
    }

    // ความสัมพันธ์กับตาราง Users
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    // ความสัมพันธ์กับตาราง TimeSlot
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id'); // ใช้เพียง time_slot_id
    }
}