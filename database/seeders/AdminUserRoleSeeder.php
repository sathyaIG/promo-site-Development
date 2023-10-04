<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminUserRole;

class AdminUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insertdata = [
            [
                "user_role" => "Super Admin",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
            [
                "user_role" => "HOD",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],

            [
                "user_role" => "Category Head",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],

            [
                "user_role" => "Category Manager",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],

            [
                "user_role" => "Manufacturer",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],

            
            

        ];

        AdminUserRole::truncate();
        AdminUserRole::insert($insertdata);
    }
}
