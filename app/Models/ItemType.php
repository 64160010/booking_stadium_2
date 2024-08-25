<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemType extends Model
{
    use HasFactory;

    protected $table = 'item_type'; // ตรวจสอบชื่อของตาราง

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'type_name',
    ];
}

