<?php

namespace App\Jobs;

use App\Models\Admin\CampaignBusinessType;
use App\Models\Admin\CategoryDept;
use App\Models\Admin\CategoryTLC;
use App\Models\AdminBusinessType;
use App\Models\AdminDepartment;
use App\Models\AdminUserRole;
use App\Models\Admin\SettingGeneral;
use App\Models\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use SimpleXLSX;
use Str;

use App\Jobs\SetpasswordJob;


use App\Mail\SetPasswordMail;
use Mail;

class ImportUserJob implements ShouldQueue
{

    use
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $i = 1;
        $error_data = [];

        $error_data = [];
        $update_array = array(
            'extract_status' => 1,
        );

        DB::table('admin_upload_log')
            ->where('id', $this->details['log_id'])
            ->update($update_array);

        $xlsx = SimpleXLSX::parse($this->details['path']);

        foreach ($xlsx->rows() as $row) {

            /*
             * Header column validation
             */
            if ($i == 1) {

                if (count($row) >= 13) {

                    if (
                        $row['0'] != 'S.No' ||
                        $row['1'] != 'Usernamee' ||
                        $row['2'] != 'Contact Number' ||
                        $row['3'] != 'Email' ||
                        /*   $row['4'] != 'Phone' ||
                        $row['5'] != 'Ext' || */
                        $row['4'] != 'Email 1' ||
                        $row['5'] != 'Email 2' ||
                        $row['6'] != 'User Role' ||
                        $row['7'] != 'Department' ||
                        $row['8'] != 'Business Type' ||
                        $row['9'] != 'Manufacturer Lists' 
                    ) {

                        $error_data_1 = array(
                            'log_id' => $this->details['log_id'],
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Match...',
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                    $i++;
                    continue;
                } else {
                    $error_data_1 = array(
                        'log_id' => $this->details['log_id'],
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Header Column Not Match....',
                    );

                    $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                    $i++;
                    break;
                }
            }

            /* Column data validation */

            $cond_error_data = [];
            $manufacturer_id =  $row['10'];
            // if ($row['0'] == '') {

            //     $cond_error_data = array(
            //         'log_id' => $this->details['log_id'],
            //         'file_name' => "Error in the Line Number " . $i,
            //         'error' => 'User First Name Code is missing',
            //     );
            //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
            //     $i++;
            //     continue;
            // }

            if ($row['1'] == '') {
                $cond_error_data = array(
                    'log_id' => $this->details['log_id'],
                    'file_name' => "Error in the Line Number " . $i,
                    'error' => 'User Name is missing',
                );
                $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                $i++;
                continue;
            }

            if ($row['2'] == '') {
                $cond_error_data = array(
                    'log_id' => $this->details['log_id'],
                    'file_name' => "Error in the Line Number " . $i,
                    'error' => 'User Name is missing',
                );
                $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                $i++;
                continue;
            }


            if ($row['3'] == '' || $row['4'] == '' || $row['5'] == '') {
                $cond_error_data = array(
                    'log_id' => $this->details['log_id'],
                    'file_name' => "Error in the Line Number " . $i,
                    'error' => 'Email fields are missing',
                );
                $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                $i++;
                continue;
            } else {
                $emails = array($row['3'], $row['4'], $row['5']);
            
                // Check for duplicate emails
                if (count($emails) !== count(array_unique($emails))) {
                    $cond_error_data = array(
                        'log_id' => $this->details['log_id'],
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'One or more emails are not unique',
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
            
                $user_details = User::whereIn('email', $emails)
                                     ->orWhereIn('email_1', $emails)
                                     ->orWhereIn('email_2', $emails)
                                     ->first();
            
                if ($user_details != null) {
                    $cond_error_data = array(
                        'log_id' => $this->details['log_id'],
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'One or more user emails already exist',
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
            }
            
            if ($row['6'] == '') {
                $cond_error_data = array(
                    'log_id' => $this->details['log_id'],
                    'file_name' => "Error in the Line Number " . $i,
                    'error' => 'User Role is missing',
                );
                $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                $i++;
                continue;
            }else {

                $role_details = AdminUserRole::where('user_role', $row['6'])->first();
                if ($role_details != null) {
                    $role_id = $role_details->id;
                } else {
                    $cond_error_data = array(
                        'log_id' => $this->details['log_id'],
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'In correct user Role ',
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
            }

            if ($row['7'] != '') {

                $department_array = string_to_array($row['5']);

                $department_details = AdminDepartment::whereIn('department', $department_array)->pluck('id');

                if (count($department_details) > 0) {
                    $department_id = array_to_string($department_details->toArray());
                } else {
                    $cond_error_data = array(
                        'log_id' => $this->details['log_id'],
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'In correct user Department',
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
            } else {
                $cond_error_data = array(
                    'log_id' => $this->details['log_id'],
                    'file_name' => "Error in the Line Number " . $i,
                    'error' => 'Department is Missing ',
                );
                $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                $i++;
                continue;
            }

            if ($row['8'] != '') {

                $business_array = string_to_array($row['6']);

                $business_details = AdminBusinessType::whereIn('business_type', $business_array)->pluck('id');

                if (count($business_details) > 0) {
                    $business_id = array_to_string($business_details->toArray());
                } else {
                    $cond_error_data = array(
                        'log_id' => $this->details['log_id'],
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'In correct user Business Type ',
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
            } else {
                $cond_error_data = array(
                    'log_id' => $this->details['log_id'],
                    'file_name' => "Error in the Line Number " . $i,
                    'error' => 'Business Type is Missing ',
                );
                $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                $i++;
                continue;
            }

            if ($user_details != null) {

                $update_data = array(
                    'name' => $row['1'],
                    'email' => $row['3'],
                    'email_1' => $row['4'],
                    'email_2' => $row['5'],
                    'mobile' => $row['2'],
                    'department' => $department_id,
                    'business_type' => $business_id,
                    'role' => $role_id,
                    'manufacturerLists' => $row['9'],
                );

                User::where('email', $row['3'])->update($update_data);
            } else {

                /* User Insert */
                $password = Str::random(12);
                $insert_data = array(
                    'name' => $row['1'],
                    'email' => $row['3'],
                    'email_1' => $row['4'],
                    'email_2' => $row['5'],
                    'mobile' => $row['2'],
                    'department' => $department_id,
                    'business_type' => $business_id,
                    'role' => $role_id,
                    'manufacturerLists' => $row['9'],
                    'password' => Hash::make($password),
                    'is_active' => '0',
                    'active_tokan' => Str::random(60),
                    'created_by' => $this->details['user_id'],
                    'created_date' => date('Y-m-d'),
                );

                $userdetails = User::create($insert_data);

                // $link = getHost() . 'Account_Activate/' . $userdetails->active_tokan . '?email=' . urlencode($userdetails->email);

                // try {

                //     $details = [
                //         "email" => $row['2'],
                //         "name" => $row['0'] . " " . $row['1'],
                //         "link" => $link,
                //         "expire" => get_constant('RESET_PASSWORD_EXPIRE'),
                //     ];

                //     dispatch((new SetpasswordJob($details))->onQueue('high')); 

                //     Log::channel('user-info')->info("User Successfully Added", $userdetails->toArray());
                    
                // } catch (\Exception $e) {

                //     report($e);

                //     $cond_error_data = array(
                //         'log_id' => $this->details['log_id'],
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Email Not send',
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
            }
            //Log::info('not');
            $i++;
        }

        $final_update_array = array(
            'extract_status' => 2,
        );

        DB::table('admin_upload_log')
            ->where('id', $this->details['log_id'])
            ->update($final_update_array);
    }
}
