<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stadium extends Model
{
    public function timeSlots()
{
    return $this->hasMany(StadiumTime::class);
}

    use HasFactory;

    // ระบุชื่อตารางที่ต้องการใช้
    protected $table = 'stadium';

    protected $fillable = [
        'stadium_name', 
        'stadium_price', 
        'stadium_picture', 
        'stadium_status'
    ];
}

