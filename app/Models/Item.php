<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // ระบุชื่อตารางที่ใช้งาน
    protected $table = 'item';

    protected $fillable = [
        'item_code', 'item_name', 'item_picture', 'item_type_id', 'price', 'item_quantity'
    ];

    // ความสัมพันธ์กับ item_type
    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    // ฟังก์ชันสำหรับสร้าง item_code อัตโนมัติ
    public static function boot()
    {
        parent::boot();
    
        static::creating(function ($item) {
            // ดึงข้อมูล item ล่าสุดที่อยู่ในประเภทเดียวกัน
            $latestItem = Item::where('item_type_id', $item->item_type_id)
                ->orderBy('item_code', 'desc')
                ->first();
    
            // กำหนดค่าเริ่มต้นสำหรับ item_code ตามประเภท
            $typeCode = '';
            switch ($item->item_type_id) {
                case 6:
                    $typeCode = 'FB'; // ลูกฟุตบอล
                    break;
                case 7:
                    $typeCode = 'SF'; // รองเท้าฟุตบอล
                    break;
                case 8:
                    $typeCode = 'SH'; // เสื้อกั๊ก
                    break;
                default:
                    $typeCode = 'XX'; // Default กรณีไม่มีประเภท
            }
    
            // กำหนดรหัส item_code โดยรันเลขตามประเภทอุปกรณ์
            // ใช้ item_code ล่าสุดเพื่อสร้างเลขลำดับ
            $latestCode = $latestItem ? $latestItem->item_code : $typeCode . '000';
            $nextNumber = intval(substr($latestCode, 2)) + 1;
            $item->item_code = $typeCode . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        });
    }
    
}



