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
        'borrow_status',
        'item_id',
        'users_id',
        'time_slot_id',
        'time_slot_stadium_id',
    ];

    // สัมพันธ์กับตาราง Item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // สัมพันธ์กับตาราง Stadium
    public function stadium()
    {
        return $this->belongsTo(Stadium::class, 'time_slot_stadium_id');
    }
    // สัมพันธ์กับตาราง TimeSlot
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id', 'id');
    }

    // สัมพันธ์กับตาราง Users
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    // สัมพันธ์กับตาราง BorrowDetail
    public function details()
{
    return $this->hasMany(BorrowDetail::class);
}
}

class BorrowDetail extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'borrow_detail';
    protected $guarded = [];
    protected $fillable = [
        'item_id',
        'item_item_type_id',
        'borrow_id',
        'borrow_date',
        'borrow_quantity',
        'borrow_total_hour',
        'borrow_total_price',
        'borrow_status',
        'users_id',
        'time_slot_id',
        'stadium_id',
    ];

    // สัมพันธ์กับตาราง Item
    public function item()
{
    return $this->belongsTo(Item::class);
}

    // สัมพันธ์กับตาราง Borrow
    public function borrow()
{
    return $this->belongsTo(Borrow::class);
}

    // สัมพันธ์กับตาราง TimeSlot
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    // สัมพันธ์กับตาราง Stadium
    public function stadium()
    {
        return $this->belongsTo(Stadium::class, 'stadium_id');
    }

    // สัมพันธ์กับตาราง Users
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
