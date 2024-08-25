<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Item extends Model
// {
//     public $timestamps = false; 
//     use HasFactory;

//     protected $table = 'item';
//     protected $fillable = [
//         'item_code',
//         'item_name',
//         'item_picture',
//         'price',
//         'item_quantity',
//         'item_type_id',
//     ];
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // ระบุชื่อตารางที่ใช้งาน
    protected $table = 'item';

    protected $fillable = [
        'item_code', 'item_name', 'item_picture', 'item_type_id', 'price', 'item_quantity'
    ];

    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }
}


