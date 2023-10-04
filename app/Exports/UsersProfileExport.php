<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersProfileExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function headings(): array
    {
        return [
            'S NO',
            'User Name',
            'Email ID',
            'Mobile No',           
            'User Role',
            'Department',
            'Categories',
            'Business Type',
            'Campaign Booking Type', 
            'Reporting Managers',
            'Manufacturer',
            'Profile Status',
            'Website Access',
            'Created By',
            'Created At',
        ];
    }

    public function collection()
    {

        $data = DB::table(DB::raw(' users, (SELECT @a:= 0) AS a'))
            ->select(
                DB::raw(' @a:=@a+1 "S No" , name as "User Name", email as "Email ID", mobile as "Mobile No", FN_GETUSERROLE(role) as "User Role", FN_GETDEPARTMENT(department) as "Department",FN_GETMULTLCNAME(category) as "Categories", FN_GETBUSINESSTYPE(business_type) as "Business Type",  FN_GETCAMPAIGNTYPE(campaign_booking_type) as "Campaign Booking Type", FN_GETMULUSERNAME(report_manager) as "Reporting Managers", FN_GETMANUFACTURER(manufacture_name) as "Manufacturer", 
                 (CASE 
                WHEN users.status = "0" THEN "In-Active" 
                WHEN users.status = "1" THEN "Active" 
                ELSE "In-Active" 
                END) as "Profile Status",
                (CASE 
                WHEN users.website_access = "1" THEN "Product Ads" 
                WHEN users.website_access = "2" THEN "Banner Ads" 
                ELSE "Product Ads,Banner Ads" 
                END) as "Website Access", FN_GETUSERNAME(created_by) as "Created by", DATE_FORMAT(created_at,"%d-%m-%Y") as "Created At"')
            );



        if ($this->details['name'] != null && $this->details['name'] != '') {
            $data = $data->where('name', 'like', '%' . $this->details['name'] . '%');
        }
        if ($this->details['email'] != null && $this->details['email']) {
            $data = $data->where('email', 'like', '%' . $this->details['email'] . '%');
        }
        if ($this->details['phone'] != null && $this->details['phone']) {
            $data = $data->where('mobile', $this->details['phone']);
        }
        if ($this->details['role'] != null && $this->details['role']) {

            $data = $data->whereRaw('FIND_IN_SET(?, role)', decryptId($this->details['role']));
        }
        if ($this->details['department'] != null && $this->details['department']) {
            $data = $data->whereRaw('FIND_IN_SET(?, department)', decryptId($this->details['department']));
        }
        if ($this->details['report_manager'] != null && $this->details['report_manager']) {
            $data = $data->whereRaw('FIND_IN_SET(?, report_manager)', decryptId($this->details['report_manager']));
        }
        if ($this->details['business_type'] != null && $this->details['business_type']) {
            $data = $data->whereRaw('FIND_IN_SET(?, business_type)', decryptId($this->details['business_type']));
        }


        $data = $data->where('trash', 'NO');
        $data = $data->orderBy('created_at', 'Desc');
        $data = $data->get();

        return $data;
    }
}
