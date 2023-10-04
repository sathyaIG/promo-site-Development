<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminDepartment;

class AdminDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insertdata = [
            [
                "department" => "FMCG-Branded-Foods",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
  

        ];

        AdminDepartment::truncate();
        AdminDepartment::insert($insertdata);
    }
}
