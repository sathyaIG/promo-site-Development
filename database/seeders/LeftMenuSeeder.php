<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeftMenu;

class LeftMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert_array = [

            [
                "id" => "1",
                "name" => "Dashboard",
                "link" => "dashboard",
                "icon" => "mdi mdi-view-dashboard-outline",
                "parent_id" => "0",
                "is_parent" => "0",
                "is_module" => "1",
                "sort_order" => "1",
                "status" => 1,
                "trash" => "NO",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id" => "2",
                "name" => "Upload Promotion",
                "link" => "upload_promotion",
                "icon" => "fas fa-file-upload",
                "parent_id" => "0",
                "is_parent" => "0",
                "is_module" => "1",
                "sort_order" => "2",
                "status" => 1,
                "trash" => "NO",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "id" => "3",
                "name" => "Report",
                "link" => "report",
                "icon" => "fas fa-folder-open",
                "parent_id" => "0",
                "is_parent" => "0",
                "is_module" => "1",
                "sort_order" => "3",
                "status" => 1,
                "trash" => "NO",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            
            [
                "id" => "4",
                "name" => "Product List",
                "link" => "product_list",
                "icon" => "far fa-newspaper",
                "parent_id" => "0",
                "is_parent" => "0",
                "is_module" => "1",
                "sort_order" => "4",
                "status" => 1,
                "trash" => "NO",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "id" => "5",
                "name" => "Region Management",
                "link" => "region_management",
                "icon" => "fas fa-home",
                "parent_id" => "0",
                "is_parent" => "0",
                "is_module" => "1",
                "sort_order" => "5",
                "status" => 1,
                "trash" => "NO",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "id" => "6",
                "name" => "User Management",
                "link" => "user_management",
                "icon" => "fas fa-address-book",
                "parent_id" => "0",
                "is_parent" => "0",
                "is_module" => "1",
                "sort_order" => "6",
                "status" => 1,
                "trash" => "NO",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "id" => "7",
                "name" => "Upload Logs",
                "link" => "upload_logs",
                "icon" => "dripicons-blog",
                "parent_id" => "0",
                "is_parent" => "0",
                "is_module" => "1",
                "sort_order" => "7",
                "status" => 1,
                "trash" => "NO",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

           


        ];
        LeftMenu::truncate();
        LeftMenu::insert($insert_array);
    }
}