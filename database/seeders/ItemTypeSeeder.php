<?php

// database/seeders/ItemTypeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('item_type')->insert([
            ['type_name' => 'ลูกฟุตบอล'],
            ['type_name' => 'รองเท้าฟุตบอล'],
            ['type_name' => 'เสื้อกั๊ก'],
            
        ]);
    }
}
