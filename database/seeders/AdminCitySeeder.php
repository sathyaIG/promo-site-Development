<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminCityMaster;

class AdminCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insertdata = [
            [
                "city" => "Madurai",
                "state_id" =>  1,
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
            [
                "city" => "Coimbatore",
                "state_id" =>  1,
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
            [
                "city" => "palakkad",
                "state_id" =>  2,
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
            [
                "city" => "varkala",
                "state_id" =>  2,
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
  

        ];

        AdminCityMaster::truncate();
        AdminCityMaster::insert($insertdata);
    }
}
