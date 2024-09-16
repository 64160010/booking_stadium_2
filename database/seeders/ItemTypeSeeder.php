<?php

// database/seeders/ItemTypeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemType;

class ItemTypeSeeder extends Seeder
{
    public function run()
    {
        // อัพเดตคอลัมน์ type_code ของประเภทอุปกรณ์
        ItemType::where('id', 6)->update(['type_code' => 'FB']);
        ItemType::where('id', 7)->update(['type_code' => 'SF']);
        ItemType::where('id', 8)->update(['type_code' => 'SH']);
    }
}