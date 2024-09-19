<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $table = 'borrow';
    protected $guarded = []; // ทำให้ทุกคอลัมน์สามารถ fill ได้
    protected $fillable = [
        'borrow_date',
        'borrow_quantity',
        'item_id',
        'user_id',
        'time_slot_id',
        'stadium_id',
        'borrow_price',
        'borrow_total_price',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function stadium()
    {
        return $this->belongsTo(Stadium::class, 'stadium_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
}

class BorrowDetail extends Model
{
    protected $fillable = [
        'borrow_id', 'time_slot',
    ];
}
