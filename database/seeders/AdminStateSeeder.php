<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminStateMaster;

class AdminStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insertdata = [
            [
                "state" => "Tamil nadu",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
            [
                "state" => "Kerala",
                "status" => 1,
                "created_by" => 1,
                "trash" => "NO",
                "created_at" => todayDBdatetime(),
                "updated_at" => todayDBdatetime()
            ],
  

        ];

        AdminStateMaster::truncate();
        AdminStateMaster::insert($insertdata);
    }
}
