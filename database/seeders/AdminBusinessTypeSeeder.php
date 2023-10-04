<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminBusinessType;

class AdminBusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insertdata = [
            [
                "business_type" => "B2C, BBNow, BBDaily",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],

           
            

        ];

        AdminBusinessType::truncate();
        AdminBusinessType::insert($insertdata);
    }
}
