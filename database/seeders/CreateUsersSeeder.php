<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user = [
            [
                'fname' => 'Admin',
                'lname' => 'Administrator',
                'email' => 'newadmin@admin.com',
                'phone' => '1234567890',
                'is_admin' => '1',
                'password' => Hash::make('1234')
            ],
            [
                'fname' => 'User',
                'lname' => 'Usernormal',
                'email' => 'user@user.com',
                'phone' => '0987654321',
                'is_admin' => '0',
                'password' => Hash::make('1234')
            ]
            ];

            foreach($user as $key =>$value) {
                User::create($value);
            }
    }
}
