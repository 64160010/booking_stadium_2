<?php

// database/seeders/ItemTypeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemType;
use DB;

class ItemTypeSeeder extends Seeder
{
    public function run()
    {
        // สร้างข้อมูลประเภทอุปกรณ์ใหม่
        ItemType::firstOrCreate([
            'id' => 1,
            'type_name' => 'ลูกฟุตบอล',
            'type_code' => 'FB',
        ]);

        ItemType::firstOrCreate([
            'id' => 2,
            'type_name' => 'รองเท้าฟุตบอล',
            'type_code' => 'SF',
        ]);

        ItemType::firstOrCreate([
            'id' => 3,
            'type_name' => 'เสื้อกั๊ก',
            'type_code' => 'SH',
        ]);
    }
}
