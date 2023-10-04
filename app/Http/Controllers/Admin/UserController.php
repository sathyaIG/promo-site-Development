<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UserImport;
use App\Jobs\AccountStatusJob;
use App\Jobs\ImportUserJob;
use App\Jobs\SetpasswordJob;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Jobs\RegistermailJob;

use App\Exports\UsersProfileExport;
use App\Jobs\ActivateJob;
use Maatwebsite\Excel\Facades\Excel;

use Auth;
use DataTables;
use DB;
use Exception;
use Session;
use SimpleXLSX;
use Str;
use Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use App\Models\AdminUserRole;
use App\Models\AdminBusinessType;
use App\Models\AdminDepartment;



class UserController extends Controller
{
    protected $user;
    protected $user_role;
    protected $department;
    protected $business_type;

    public function __construct(User $user, AdminUserRole $user_role, AdminDepartment $department, AdminBusinessType $business_type)
    {
        $this->user = $user;
        $this->user_role = $user_role;
        $this->department = $department;
        $this->business_type = $business_type;
    }

    public function index(Request $request)
    {

        if (Auth::check()) {

            if ($request->ajax()) {

                try {

                    $data = $this->user->AllUserList();

                    $datatables = Datatables::of($data)
                        ->addIndexColumn()


                        ->addColumn('profile_status', function ($row) {

                            $text = "<span style='color:red'>In-Active<span>";
                            if ($row->status == 1) {

                                $text = "<span style='color:green;cursor:pointer' class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' >Active<span>";
                            } else if ($row->status == 0) {
                                $text = "<span style='color:red;cursor:pointer' class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '0' >In-Active<span>";
                            }

                            return $text;
                        })
                        ->addColumn('created_date', function ($row) {

                            $d = strtotime($row->created_date);
                            $btn = date("d/m/Y", $d);

                            return $btn;
                        })
                        ->addColumn('created_by', function ($row) {

                            $btn = getUsername($row->created_by);

                            return $btn;
                        })
                        ->addColumn('role', function ($row) {

                            $btn = getCustomValue('admin_user_role', 'user_role', $row->role);

                            return $btn;
                        })
                        ->addColumn('department', function ($row) {

                            $btn = getMultipleValue('admin_department',$row->department,'id','department');

                            return $btn;
                        })

                        ->addColumn('business_type', function ($row) {

                            $btn = getMultipleValue('admin_business_type', $row->business_type, 'id', 'business_type');

                            return $btn;
                        })

                        ->addColumn('action', function ($row) {
                            $btn = '';
                            $btn = '<a href="' . admin_url('user_management/' . encryptId($row->id)) . '"   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                            $btn .= '<a href="' . admin_url('user_management/edit/' . encryptId($row->id)) . '" class=" " title="Edit"><i class="fa fa-edit" style="color:#43a047;"></i></a> ';
                            if ($row->role != 1) {
                                $btn .= '<a href="javascript:void(0);"  data-id="' . encryptId($row->id) . '"  class="UserDelete" title="Delete"><i class="fa fa-trash-alt" style="color:#d81821;"></i></a> ';
                            }


                            return $btn;
                        })
                        ->rawColumns(['action', 'created_date', 'created_by', 'profile_status'])
                        ->make(true);

                    return $datatables;
                } catch (Exception $ex) {
                    report($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }


        $user = $this->user->UserList();

        $data = array(
            'user_details' => $user,

        );

        return view('admin.User_details_list', $data);
    }

    public function UserAdd(Request $request)
    {
        $checkAdminExists = $this->user->checkAdminExists();
        if ($checkAdminExists == '') {

            $user_role = $this->user_role->getUserRole('yes');
        } else {

            $user_role = $this->user_role->getUserRole('no');
        }
        $user = $this->user->UserList();
        $department = $this->department->DepartmentList();
        $business_type = $this->business_type->BusinessTypeList();
        $getManufacturer = $this->user->Getmanufacturer();
        $data = array(
            'user_details' => $user,
            'userrole_details' => $user_role,
            'department_details' =>  $department,
            'business_type_details' =>  $business_type,
            'getManufacturer' => $getManufacturer

        );
        return view('admin.User_details_add', $data);
    }
    public function Useremailcheck(Request $request)
    {
        if ($request->ajax()) {
            $email = $request->email;
            $userid = $request->userid;
            if ($userid == '') {
                $user = $this->user->EmailCheck($email);
            } else {
                $user = $this->user->ExistEmailCheck($email, $userid);
            }
            if ($user->count()) {
                return Response::json(array('msg' => 'true'));
            }
            return Response::json(array('msg' => 'false'));
        }
    }
    public function UserAddSubmit(Request $request)
    {

        try {
            $rules = [
                'user_name' => 'required',
                'mobile' => 'required',
                'email' => 'required|email',
                'user_role' => 'required',
                'business_type' => 'required',
                'department' => 'required',
            ];
            $messages = [
                'user_name.required' => 'Please enter partner name',
                'mobile.required' => 'Please enter mobile',
                'email.required' => 'Please enter email address',
                'email.email' => 'Please enter a valid email address',
                'business_type.required' => 'Please select Business Type',
                'department.required' => 'Please select Department',
            ];
            $validator = \Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                // dd($validator->errors());
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $password = Str::random(12);

            $insert_data = array(
                'name' => $request->user_name,
                'email' => $request->email,
                'email_1' => $request->email1,
                'email_2' => $request->email2,
                'mobile' => $request->mobile,
                'status' => "0",
                'role' => $request->user_role,
                'department' => implode(',',arrayDecrypt($request->department)),
                'business_type' => implode(',',arrayDecrypt($request->business_type)),
                'password' => Hash::make($password),
                'is_active' => '0',
                'active_tokan' => Str::random(60),
                'created_by' => Auth::user()->id,
                'created_date' => date('Y-m-d'),
                'categories' => $request->category,
                'fundingCategory' => $request->category,
                'invoice' => $request->invoice ? $request->invoice : '0',
                'redemptionPerMember' => $request->member,
                'redemptionPerOrder' => $request->order,
                'manufacturerLists' => ($request->manufacturer != null ? implode(',', arrayDecrypt($request->manufacturer)) : ''),

            );

            $file = $request->file('profile_image');
            if ($file != null) {
                $uploadpath = 'public/uploads/profile';
                $filenewname = time() . Str::random('10') . '.' . $file->getClientOriginalExtension();
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileMimetype = $file->getMimeType();
                $fileExt = $file->getClientOriginalExtension();
                $file->move($uploadpath, $filenewname);
                $insert_data['profile_image'] = $filenewname;
            } else {
                $insert_data['profile_image'] = '';
            }

            $userdetails = $this->user->InsertUser($insert_data);

            $link = getHost() . 'Account_Activate/' . $userdetails->active_tokan . '?email=' . urlencode($userdetails->email);
            try {
                $details = [
                    "email" => $request->email,
                    "name" => $request->user_name,
                    "link" => $link,
                    "expire" => get_constant('RESET_PASSWORD_EXPIRE'),
                ];
                dispatch((new SetpasswordJob($details))->onQueue('high'));
                Log::channel('user-info')->info("User Successfully Added", $userdetails->toArray());
                Session::flash('success', 'User added successfully!');
                return redirect(admin_url('user_management'));
            } catch (\Exception $e) {
                report($e);
                return redirect(admin_url('user_management'));
            }
        } catch (Exception $ex) {
            dd($ex);
            Log::channel('user-info')->alert($ex);
            return redirect(admin_url('user_management'));
        }
    }

    public function UserView(Request $request, $userid = '')
    {
        $id = decryptId($request->userid);

        if (Auth::check()) {
            $user = $this->user->GetUser($id);
            $department = $this->department->DepartmentName($user);
            $business_type = $this->business_type->BusinessTypeName($user);
            $user_role = $this->user_role->UserRoleName($user);

            $data = array(
                'user_details' => $user,
                'department_details' => $department,
                'business_type_details' => $business_type,
                'user_role_details' => $user_role,
            );
        }
        try {

            return view('admin.User_details_view', $data);
        } catch (Exception $ex) {
            report($ex);
        }
    }
    public function UserEdit(Request $request, $userid = '')
    {
        try {
            $id = decryptId($request->userid);
            $user = $this->user->GetUser($id);
            $selected_business = explode(',', $user->business_type);
            $selected_department = explode(',', $user->department);
            $selected_manufacturer = explode(',', $user->manufacturerLists);                       
            $user_role = $this->user_role->UserRoleList();
            $department = $this->department->DepartmentList();
            $business_type = $this->business_type->BusinessTypeList();
            $getManufacturer = $this->user->Getmanufacturer();
            $data = array(
                'user_details' => $user,
                'userrole_details' => $user_role,
                'department_details' =>  $department,
                'business_type_details' =>  $business_type,
                'selected_department' => $selected_department,
                'selected_business' => $selected_business,
                'getManufacturer' => $getManufacturer,
                'selected_manufacturer' => $selected_manufacturer
            );
            return view('admin.User_details_edit', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }
    public function UserUpdate(Request $request)
    {
        try {
            $id = decryptId($request->userid);
            $update_data = array(
                'name' => $request->user_name,
                'email' => $request->email,
                'email_1' => $request->email1,
                'email_2' => $request->email2,
                'mobile' => $request->mobile,
                'role' => $request->user_role,
                'department' => implode(',',arrayDecrypt($request->department)),
                'business_type' => implode(',',arrayDecrypt($request->business_type)),
                'categories' => $request->category,
                'fundingCategory' => $request->category,
                'invoice' => ($request->user_role == 5 ? $request->invoice : 0),
                'redemptionPerMember' => ($request->user_role == 5 ? $request->member : ''),
                'redemptionPerOrder' => ($request->user_role == 5 ? $request->order : ''),
                'manufacturerLists' => ($request->manufacturer != null ? implode(',', arrayDecrypt($request->manufacturer)) : ''),
            );
            $file = $request->file('profile_image');
            if ($file != null) {
                $uploadpath = 'public/uploads/profile';
                $filenewname = time() . Str::random('10') . '.' . $file->getClientOriginalExtension();
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileMimetype = $file->getMimeType();
                $fileExt = $file->getClientOriginalExtension();
                $file->move($uploadpath, $filenewname);
                $update_data['profile_image'] = $filenewname;
            }
            $userdetails =   $this->user->UpdateUser($id, $update_data);
            if ($userdetails) {
                Log::channel('user-info')->info("User Successfully Updated", $update_data);
            } else {
                Log::channel('user-info')->info("User Updated Failed", $update_data);
            }
            Session::flash('success', 'User updated successfully!');
            return redirect(admin_url('user_management'));
        } catch (Exception $ex) {
            dd($ex);
            Log::channel('user-info')->alert($ex);
            return "Error";
        }
    }
    public function UserStatus(Request $request)
    {
        try {
            $userid = decryptId($request->user_id);
            $type = $request->types;
            if ($type == 1) {
                $update_data = array(
                    'status' => "0",
                    'is_active' => 0,
                );
                $message = 'Your Account was In-Activated';
                $successMsg = 'Successfully user Account was In-Activated';
                $this->user->UpdateUser($userid, $update_data);
                try {
                    $user_details = $this->user->GetUser($userid);
                    if ($user_details != null) {
                        $details = array(
                            "email" => $user_details['email'],
                            "name" => $user_details['name'],
                            'message' => $message,
                        );
                        dispatch((new AccountStatusJob($details))->onQueue('high'));
                    }
                } catch (Exception $ex) {
                    report($ex);
                    return response()->json(['error' => '1', 'status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            } else {
                $update_data = array(
                    'status' => "1",
                    'is_active' => 1,
                    'active_tokan' => Str::random(60),
                );
                $message = 'Activation email successfully sent';
                $successMsg = 'Activation email successfully sent';
                $this->user->UpdateUser($userid, $update_data);
                $userdetails = $this->user->GetUser($userid);
                $link = getHost() . 'Account_Activate/' . $userdetails->active_tokan . '?email=' . urlencode($userdetails->email);
                try {
                    $details = [
                        "email" => $userdetails->email,
                        "name" => $userdetails->name,
                        "link" => $link,
                    ];
                    dispatch((new ActivateJob($details))->onQueue('high'));
                } catch (\Exception $e) {

                    report($e);
                    return response()->json(['error' => '2', 'status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
            return response()->json(['status' => 'success', 'msg' => $successMsg], 200);
        } catch (Exception $ex) {
            return response()->json(['error' => '2', 'status' => 'error', 'msg' => 'Please try after some time'], 406);
        }
    }
    public function UserDelete(Request $request)
    {
        try {
            $userid = decryptId($request->user_id);
            $update_data = array(
                'status' => "0",
                'trash' => "YES",
            );

            $this->user->UpdateUser($userid, $update_data);
            $message = 'Your Account has been Deleted';
            try {
                $user_details = $this->user->GetUser($userid);
                if ($user_details != null) {
                    $details = array(
                        "email" => $user_details['email'],
                        "name" => $user_details['name'],
                        'message' => $message,
                    );
                    dispatch((new AccountStatusJob($details))->onQueue('high'));
                }
            } catch (Exception $ex) {
                report($ex);
                return response()->json(['error' => '1', 'status' => 'error', 'msg' => 'Please try after some time'], 406);
            }
            $update_data['user_id'] = $userid;
            $update_data['Deleted_by'] = Auth::user()->toArray();
            Log::channel('user-info')->info("User deleted successfully", $update_data);
            Session::flash('success', 'User deleted successfully!');
            return response()->json(['status' => 'success', 'msg' => 'User deleted successfully'], 200);
        } catch (Exception $ex) {
            Log::channel('user-info')->alert($ex);
            return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
        }
    }
    public function UserImport(Request $request)
    {
        $data = array();
        return view('admin.User_details_Import', $data);
    }
    public function UserImportSubmit(Request $request)
    {
        try {
            $file = $request->file('user_file');
            if ($file != null) {
                $uploadpath = 'public/uploads/user';
                $filenewname = time() . Str::random('10') . '.' . $file->getClientOriginalExtension();
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileMimetype = $file->getMimeType();
                $fileExt = $file->getClientOriginalExtension();
                $file->move($uploadpath, $filenewname);
                $path = $uploadpath . "/" . $filenewname;
                $user_id = auth()->user()->id;
                $insert_data = array(
                    'file_path' => $path,
                    'source_path' => $path,
                    'dest-path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'extract_status' => 0,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                );
                $insert_id = DB::table('admin_upload_log')->insertGetId($insert_data);
                $details = [
                    "user_id" => $user_id,
                    "log_id" => $insert_id,
                    "path" => $path,
                    "expire" => get_constant('RESET_PASSWORD_EXPIRE'),
                ];
                dispatch((new ImportUserJob($details))->onQueue('high'));
                // \Excel::import(new UserImport($user_id, $insert_id), $path);
            }
            $insert_data['log_id'] = $insert_id;
            $insert_data['Uploded_by'] = Auth::user()->toArray();
            Log::channel('user-info')->info("User Successfully Uploaded", $insert_data);
            Session::flash('success', 'Successfully User upload !');
            return redirect(admin_url('user_management'));
        } catch (Exception $ex) {
            dd($ex);
            Log::channel('user-info')->alert($ex);
            Session::flash('error', 'User upload failed!');
            return redirect(admin_url('user_management'));
        }
    }

    public function UserDownload(Request $request)
    {
        try {

            $filename = 'Userimport.xlsx';

            $path = ('public/uploads/template/' . $filename);
            return response()->json(['fileContent' => $path, 'filename' => $filename]);
            // return response()->json(['fileContent' => $path,'filename'=> $filename]);
            // return response()->download($path);
        } catch (Exception $ex) {
            return redirect(admin_url('user_management'));
        }
    }



    public function UserFileUpload(Request $request)
    {
        try {
            $file = $request->file('user_file');

            if ($file != null) {
                $uploadpath = 'public/uploads/user';
                $filenewname = time() . Str::random('10') . '.' . $file->getClientOriginalExtension();
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileMimetype = $file->getMimeType();
                $fileExt = $file->getClientOriginalExtension();
                $file->move($uploadpath, $filenewname);
                $path = $uploadpath . "/" . $filenewname;
                $user_id = auth()->user()->id;

                $xlsx = SimpleXLSX::parseFile($path);
                if (count($xlsx->rows()) == 1) {
                    // No data rows, only a heading row, so don't proceed with insertion
                    unlink($path); // Delete the uploaded file
                    Session::flash('error', 'The uploaded file contains only a heading row and no data. Please upload a valid file.');
                    return redirect(admin_url('user_management'));
                }

                $insert_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'extract_status' => 0,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                    // 'file_uploaded_id' => $data->id,
                    // 'promoType' => $promoType,
                    'file_orgname' => $fileName,
                );
                $insert_id = DB::table('admin_user_upload_log')->insertGetId($insert_data);
                $this->uploaduser($insert_data, $insert_id);
                Session::flash('success', 'Successfully User uploaded !');
                return redirect(admin_url('user_management/'));
            }
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('user_management'));
        }
    }

    public function uploaduser($insert_data, $log_id)
    {
        $user_id = auth()->user()->id;
        $error_data = [];
        $update_array = array(
            'extract_status' => 1,

        );
        DB::table('admin_upload_log')
            ->where('id', $log_id)
            ->update($update_array);


        $xlsx = SimpleXLSX::parseFile($insert_data['file_path']);
        try {
            $expectedColumns = [
                0 => 'S.No',
                1 => 'Username',
                2 => 'Contact Number',
                3 => 'Email',
                4 => 'Email 1',
                5 => 'Email 2',
                6 => 'User Role',
                7 => 'Department',
                8 => 'Business Type',
                9 => 'Manufacturer Lists',
            ];
            $i = 1;
            $insert_data_array = [];
            foreach ($xlsx->rows() as $key => $row) {
                if ($i == 1) {

                    if (count($row) >= count($expectedColumns)) {

                        foreach ($expectedColumns as $index => $columnName) {

                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    // 'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_user_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            // 'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_user_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                }

                if ($key === 0) {

                    continue;
                }
                $cond_error_data = [];

                /**Mandatory Field Check */
                if ($row['1'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'User Name is missing',
                    );
                    $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Contact Number is missing',
                    );
                    $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }else{
                    if (!preg_match('/^\d{10}$/', $row['2'])) {
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Invalid Contact Number Format (Must be 10 digits)',
                        );
                        $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }
              
                    // Check if any of the email fields is missing
                    if ($row['3'] == '' || $row['4'] == '' || $row['5'] == '') {
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Email fields are missing',
                        );
                        $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    } else {
                        $emailAddresses = array($row['3'], $row['4'], $row['5']);
                        // Validate email format for each email field
                        foreach ($emailAddresses as $emailAddress) {
                            if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                                $cond_error_data = array(
                                    'log_id' => $log_id,
                                    'file_name' => "Error in the Line Number " . $i,
                                    'error' => 'Invalid Email Format',
                                );
                                $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                                $i++;
                                continue 2; // Continue with the outer loop (skipping the remaining email fields for this row)
                            }
                        }
                
                        // Check for duplicate emails
                        if (count($emailAddresses) !== count(array_unique($emailAddresses))) {
                            $cond_error_data = array(
                                'log_id' => $log_id,
                                'file_name' => "Error in the Line Number " . $i,
                                'error' => 'Emails are not unique',
                            );
                            $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                            $i++;
                            continue;
                        }
                
                        // Check if emails already exist in the database
                        $user_details = User::whereIn('email', $emailAddresses)
                            ->orWhereIn('email_1', $emailAddresses)
                            ->orWhereIn('email_2', $emailAddresses)
                            ->first();
                
                        if ($user_details != null) {
                            $cond_error_data = array(
                                'log_id' => $log_id,
                                'file_name' => "Error in the Line Number " . $i,
                                'error' => 'Emails already exist',
                            );
                            $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                            $i++;
                            continue;
                        }
                    }
                
                    $i++; // Increment the counter if everything is okay
                

                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'User Role is missing',
                    );
                    $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {

                    $role_details = AdminUserRole::where('user_role', $row['6'])->first();
                    if ($role_details != null) {
                        $role_id = $role_details->id;
                    } else {
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'In correct user Role ',
                        );
                        $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }

                if ($row['7'] != '') {

                    $department_array = string_to_array($row['7']);

                    $department_details = AdminDepartment::whereIn('department', $department_array)->pluck('id');
                    if (count($department_details) > 0) {
                        $department_id = array_to_string($department_details->toArray());
                    } else {
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'In correct user Department',
                        );
                        $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                } else {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Department is Missing ',
                    );
                    $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if ($row['8'] != '') {

                    $business_array = string_to_array($row['8']);

                    $business_details = AdminBusinessType::whereIn('business_type', $business_array)->pluck('id');

                    if (count($business_details) > 0) {
                        $business_id = array_to_string($business_details->toArray());
                    } else {
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'In correct user Business Type ',
                        );
                        $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                } else {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Business Type is Missing ',
                    );
                    $insert_id = DB::table('admin_user_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

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
                        'created_by' => $user_id,
                        'created_date' => date('Y-m-d'),
                    );
                    $userdetails = User::create($insert_data);
                    $details = [
                        'emails' => [
                            $userdetails->email,
                            $userdetails->email_1,
                            $userdetails->email_2,
                        ],
                        'name' => $userdetails->name,
                        'password' => $password,
                        'link' => env('APP_URL'),

                    ];
                    try {
                        $mail = dispatch((new RegistermailJob($details))->onQueue('high'));
    
                    } catch (Exception $ex) {
                        dd($ex->getMessage());
                    }
                    Session::flash('success', 'Mail Send Successfully');
                    return redirect(admin_url('report'));

                $i++;
            }
            if (count($insert_data_array) > 0) {
                try {
                    DB::table('user')->insert($insert_data_array);
                } catch (\Exception $e) {
                    dd($e);
                }
            }
            $final_update_array = array(
                'extract_status' => 2
            );

            DB::table('admin_upload_log')
                ->where('id', $log_id)
                ->update($final_update_array);
        } catch (\Exception $ex) {
            dd($ex);
            report($ex);
        }
    }


    protected function validatePhoneNumbers($phoneNumbers)
    {
        // Check if $phoneNumbers is an array
        if (!is_array($phoneNumbers)) {
            // Handle the case where $phoneNumbers is not an array (e.g., throw an exception, log an error)
            return false;
        }
    
        $phoneRegex = '/^\d{10}$/';
    
        foreach ($phoneNumbers as $phoneNumber) {
            if (!is_string($phoneNumber) || !preg_match($phoneRegex, $phoneNumber)) {
                return false;
            }
        }
    
        return true;
    }
    

    protected function validateEmailAddresses($emailAddresses)
    {
        foreach ($emailAddresses as $email) {
            $email = trim($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }
        return true;
    }
}
