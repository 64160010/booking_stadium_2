<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingStadium extends Model
{
    use HasFactory;

    protected $table = 'booking_stadium';

    protected $fillable = [
        'booking_date', 
        'booking_status', 
        'users_id'
    ];

    // ความสัมพันธ์กับตาราง Users
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function details()
{
    return $this->hasMany(BookingDetail::class, 'booking_stadium_id', 'id');
}

public function payment()
{
    return $this->hasOne(PaymentBooking::class, 'booking_stadium_id', 'id');
}

public function borrow()
{
    return $this->hasMany(Borrow::class, 'booking_stadium_id');

    
}

public function stadium() {
    return $this->belongsTo(Stadium::class, 'stadium_id'); // หรือใช้ชื่อฟิลด์ที่ถูกต้อง
}



}