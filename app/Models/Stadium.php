<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stadium extends Model
{
    use HasFactory;

    protected $table = 'stadium';
    public $timestamps = false;
    protected $fillable = [
        'stadium_name',
        'stadium_price',
        'stadium_status',
    ];

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class, 'stadium_id', 'id');
    }
}
