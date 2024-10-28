<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Field;

class FieldSeeder extends Seeder
{
    public function run()
    {
        Field::create([
            'name' => 'สนามที่ 1',
            'price' => 1000.00,
            'available' => true,
        ]);

        Field::create([
            'name' => 'สนามที่ 2',
            'price' => 1500.00,
            'available' => false,
        ]);
    }
}
