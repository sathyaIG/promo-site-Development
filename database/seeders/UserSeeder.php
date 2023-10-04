<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insertdata = [
            [
                "name" => "Admin User",
                "email" => "promosite@gmail.com",
                "role" => 1,
                "mobile" => "3423423422",
                "profile_image" => "1641291529ib3bigQd7j.png",
                "department" => 1,
                "business_type" => 1,
                "is_active" => 0,
                "remember_token" => "",
                "active_tokan" => "",
                "password" => Hash::make('asdF@1234567'),
                "created_by" => 1,
                "created_date" => "2023-05-17",
                "status" => 1,
                "trash" => "NO",
                "created_at" => null,
                "updated_at" => date('Y-m-d H:i:s')

            ],
        ];

        User::truncate();
        User::insert($insertdata);
    }
}