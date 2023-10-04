<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;


use App\Exports\UsersProfileExport;
use App\Exports\PreviewExport;
use App\Imports\ExcelImport;
use App\Imports\PromotypeImport;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;


use Auth;
use DataTables;
use DB;
use Exception;
use Session;
use Str;
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use App\Models\AdminRegionMaster;
use App\Models\AdminCityMaster;
use App\Models\AdminStateMaster;
use App\Models\AdminUploadPromotion;
use App\Models\AdminBusinessType;
use App\Models\AdminPreview;
use App\Models\AdminPreviewCombo;
use App\Models\AdminPreviewCartLevel;
use App\Models\AdminPreviewCartFree;
use App\Models\AdminPreviewCartGroup;
use App\Models\UploadLog;
use App\Models\User;
use SimpleXLSX;

use App\Models\Promotion;
use Carbon\Carbon;
use COM;

class UploadPromotionController extends Controller
{
    protected $user;
    protected $promotion;
    protected $business_type;
    protected $region;
    protected $adminPreview;
    protected $uploadLog;
    protected $adminPreviewCombo;
    protected $adminPreviewCartlevel;
    protected $adminPreviewCartfree;
    protected $adminPreviewCartgroup;

    public function __construct(AdminPreviewCombo $adminPreviewCombo, UploadLog $uploadLog, AdminPreview $adminPreview, User $user, AdminUploadPromotion $promotion, AdminBusinessType $business_type, AdminRegionMaster $region , AdminPreviewCartLevel $adminPreviewCartlevel , AdminPreviewCartFree $adminPreviewCartfree ,AdminPreviewCartGroup $adminPreviewCartgroup)
    {
        $this->user = $user;
        $this->promotion = $promotion;
        $this->business_type = $business_type;
        $this->region = $region;
        $this->adminPreview = $adminPreview;
        $this->uploadLog = $uploadLog;
        $this->adminPreviewCombo = $adminPreviewCombo;
        $this->adminPreviewCartlevel = $adminPreviewCartlevel;
        $this->adminPreviewCartfree = $adminPreviewCartfree;
        $this->adminPreviewCartgroup = $adminPreviewCartgroup;
    }

    public function index(Request $request)
    {
        $id = Auth::user()->id;
        if (Auth::check()) {
            if ($request->ajax()) {
                try {
                    $data = $this->promotion->AllUserPromotionList($id);
                    $datatables = Datatables::of($data)
                        ->addIndexColumn()

                        ->addColumn('created_date', function ($row) {

                            $d = strtotime($row->created_date);
                            $btn = date("d/m/Y", $d);

                            return $btn;
                        })

                        ->addColumn('action', function ($row) {
                            $btn = '';
                            $btn = '<a href="' . admin_url('upload_promotion/preview/' . encryptId($row->id)) . '"   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                            $btn .= '<a href="' . admin_url('upload_promotion/edit/' . encryptId($row->id)) . '" class=" " title="Edit"><i class="fa fa-edit" style="color:#43a047;"></i></a> ';
                            // $btn .= '<a href="javascript:void(0);"  data-id="' . encryptId($row->id) . '"  class="RegionDelete" title="Delete"><i class="fa fa-trash-alt" style="color:#d81821;"></i></a> ';

                            return $btn;
                        })
                        ->rawColumns(['action'])
                        ->make(true);

                    return $datatables;
                } catch (Exception $ex) {
                    dd($ex);
                    report($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }

        $user = $this->user->GetUser($id);
        $selected_business = explode(',', $user->business_type);
        $getManufacturerBusiness = $this->business_type->BussinessTypeMultiple($selected_business);
        $promotion = $this->promotion->AllUserPromotionList($id);
        $business_type = $this->business_type->BusinessTypeList();
        $region = $this->region->RegionList();
        $data = array(
            'promotion_details' => $promotion,
            'business_type_details' =>  $business_type,
            'region_details' => $region,
            'getManufacturerBusiness' => $getManufacturerBusiness
        );
        return view('admin.Upload_promotion_details', $data);
    }

    public function PromotypeDownload(Request $request)
    {
        try {
            if (decryptId($request->promotype) == 1) {
                $filename = 'single_promotype.xlsx';
            } else if (decryptId($request->promotype) == 2) {
                $filename = 'combo_promotype.xlsx';
            } else if (decryptId($request->promotype) == 3) {
                $filename = 'cartlevel_promotype.xlsx';
            } else if (decryptId($request->promotype) == 4) {
                $filename = 'cartfree_promotype.xlsx';
            } else {
                $filename = 'group_promos_promotype.xlsx';
            }

            $path = ('public/uploads/template/' . $filename);
            return response()->json(['fileContent' => $path, 'filename' => $filename]);
            // return response()->json(['fileContent' => $path,'filename'=> $filename]);
            // return response()->download($path);
        } catch (Exception $ex) {
            return redirect(admin_url('upload_promotion'));
        }
    }

    /* Single and Combo Promo for Upload */
    public function PromotypeFileUpload(Request $request)
    {
        try {
            $file = $request->file('promotype_file');
            // $region = arrayDecrypt($request->region);

            $business_type = $request->business_type;
            $theme = $request->theme;

            $promoType = decryptId($request->promoType);

            if ($file != null) {
                $uploadpath = 'public/uploads/promotion';
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
                    return redirect(admin_url('upload_promotion'));
                }


                $promotion_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'manufacturer_id' => $user_id,
                    'created_by' => $user_id,
                    'created_date' => date('Y-m-d'),
                    'status' => 1,
                    'promo_details_status' => 0,
                    'promo_type' => $promoType,
                    // 'region_id' => implode(',',   $region),
                    'business_type_id' => implode(',', $business_type),
                    'theme_id' => implode(',',  $theme),
                    'file_orgname' => $fileName,
                    'minimum_value' => $request->minimum_value,
                    'minimum_purchase' => $request->minimum_purchase,

                );
                // dd($promotion_data,$request->all());
                $data = $this->promotion->InsertPromotion($promotion_data);

                $insert_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'extract_status' => 0,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                    'file_uploaded_id' => $data->id,
                    'promoType' => $promoType,
                    'file_orgname' => $fileName,
                );

                $insert_id = DB::table('admin_upload_log')->insertGetId($insert_data);
                if ($promoType == 1) {
                    $this->uploadpromo($insert_data, $insert_id, $data->id, $data->promo_type);
                    Session::flash('success', 'Successfully Promotype uploaded !');
                    return redirect(admin_url('upload_promotion/preview/' . encryptId($data->id)));
                } else if($promoType == 2) {
                    $this->uploadpromoCombo($insert_data, $insert_id, $data->id, $data->promo_type);
                    Session::flash('success', 'Successfully Promotype uploaded !');
                    return redirect(admin_url('upload_promotion/preview_combo/' . encryptId($data->id)));
                }else if($promoType == 3) {
                    $this->uploadpromoCartLevel($insert_data, $insert_id, $data->id, $data->promo_type);
                    Session::flash('success', 'Successfully Promotype uploaded !');
                    return redirect(admin_url('upload_promotion/preview_cart_level/' . encryptId($data->id)));
                }else if($promoType == 4){
                    $this->uploadpromoCartFree($insert_data, $insert_id, $data->id, $data->promo_type);
                    Session::flash('success', 'Successfully Promotype uploaded !');
                    return redirect(admin_url('upload_promotion/preview_cart_free/' . encryptId($data->id)));
                }else {
                    $this->uploadpromoCartGroup($insert_data, $insert_id, $data->id, $data->promo_type);
                    Session::flash('success', 'Successfully Promotype uploaded !');
                    return redirect(admin_url('upload_promotion/preview_cart_group/' . encryptId($data->id)));
                }
                // Excel::import(new PromotypeImport($insert_data, $insert_id), $path);
                // dispatch((new SetpasswordJob($details))->onQueue('high'));

            }
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('upload_promotion'));
        }
    }

    public function uploadpromo($insert_data, $log_id, $uploadedId, $promoType)
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
        // dd($insert_data, $log_id, $uploadedId,$xlsx,$insert_data['file_orgname'] );
        // dd($promoType== 2,$insert_data['file_path'] == 'single_promotype',strpos($insert_data['file_path'], 'single_promotype'));
        try {
            $expectedColumns = [
                0 => 'Sr. No.',
                1 => 'Manufacturer / Suppler Name',
                2 => 'Code',
                3 => 'Product Description',
                4 => 'MRP',
                5 => 'Start Date (DD-MMM-YY)',
                6 => 'End Date (DD-MMM-YY)',
                7 => 'Offer Details(100% Vendor Funded)',
                8 => 'Redemption Limit - Qty Per Campaign',
                9 => 'Selections of Cities',
                10 => 'Pan India',
                11 => 'South',
                12 => 'North',
                13 => 'East',
                14 => 'West',
                15 => 'Central',
                16 => 'ANDHRA PRADESH',
                17 => 'TELANGANA',
                18 => 'ASSAM',
                19 => 'BIHAR',
                20 => 'CHHATTISGARH',
                21 => 'GUJARAT',
                22 => 'DELHI-NCR',
                23 => 'JHARKHAND',
                24 => 'KARNATAKA',
                25 => 'KERALA',
                26 => 'MADHYA PRADESH',
                27 => 'MAHARASHTRA - Mumbai',
                28 => 'MAHARASHTRA - Pune',
                29 => 'ORISSA',
                30 => 'PUNJAB',
                31 => 'RAJASTHAN',
                32 => 'TAMIL NADU',
                33 => 'UTTAR PRADESH',
                34 => 'WEST BENGAL',
                35 => 'Department',
                36 => 'Business Model'
            ];
            $validValues = ['Yes', 'No'];
            $i = 1;
            $insert_data_array = [];           
            foreach ($xlsx->rows() as $key => $row) {
                if ($i == 1) {
                   
                    if (count($row) >= count($expectedColumns)) {
                       
                        foreach ($expectedColumns as $index => $columnName) {
                           
                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'lineNumber' => $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'lineNumber' => $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
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
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Manufacturer / Suppler Name is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Code is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['3'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Product Description is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                // if ($row['4'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'MRP is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Start Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'End Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Offer Details(100% Vendor Funded) is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '' && $row['10'] == 'Yes') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Selections of Cities is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['10'], $validValues) || !in_array($row['11'], $validValues) ||
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Pan India or one of the City is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['35'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Department is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['36'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Business Model is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Redemption Limit - Qty Per Campaign greater than 20 for Pan India Yes  */
                if ($row['8'] >= 20 && $row['10'] == 'Yes' && $row['8'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Redemption Limit - Qty Per Campaign Should be Less than 20 for Pan India'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Other Cities has No if Pan India Yes */
                $cityValues = array_slice($row, 11, 24); // Assuming these keys represent city values 11 to 34.
                if ($row['10'] == 'Yes' && in_array('No', $cityValues, true)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'One of the Cities Mentioned as No IF PanIndia is in Yes'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /** Format Check */
                if (!preg_match('/^[A-Za-z0-9\s]+$/', $row['1'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Manufacturer / Suppler Name Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_int($row['2'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Code Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$-]+$/', $row['3']) && $row['3'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Product Description Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_int($row['4']) && $row['4'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'MRP Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!Carbon::hasFormat($row['5'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Start Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['5'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'lineNumber' => $i,
                            'error' => 'Start Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }


                    /*if ($dateComponents[1] != '25' || $dateComponents[0] != $currentMonth || $currentYear != $dateComponents[2]) {
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'error' => 'Start Date Should be 25th of this month'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }*/
                }
               
                if (!Carbon::hasFormat($row['6'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'End Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['6'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'lineNumber' => $i,
                            'error' => 'End Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }


                    /*$startDate = Carbon::createFromFormat('m-d-Y H:i', $row['5']);
                    $endDate = Carbon::createFromFormat('m-d-Y H:i', $row['6']);
                    $expectedEndDate = $startDate->copy()->addMonthNoOverflow()->day(24);

                    if ($endDate->day != $expectedEndDate->day || $endDate->month != $expectedEndDate->month || $endDate->year != $expectedEndDate->year) {

                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'End Date Should be 24th of Next Month'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }*/
                    // if($startDate->diffInDays($expectedEndDate) === 30)
                }
               
                if (!preg_match('/^[A-Za-z0-9\s!@#$%.]+$/', $row['7'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Offer Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_numeric($row['8']) && $row['8'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Redemption Limit Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if (!preg_match('/^[A-Za-z0-9\s!@#$]+$/', $row['9'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Selection of Cities Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['10'], $validValues) || !in_array($row['11'], $validValues) ||
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'City Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%-]+$/', $row['35'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Department Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%]+$/', $row['36'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Business Model Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */
                preg_match("/([0-9.]+)/", $row['7'], $offerValue);
                $offerValue = $offerValue[1];

                $discountType = '';

                if (str_contains($row['7'], '%')) {
                    $discountType = 'Percent';
                } else if (str_contains($row['7'], 'Rs')) {
                    $discountType = 'Flat';
                } else if (str_contains($row['7'], 'sell')) {
                    $discountType = 'Fixed';
                } else {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Type should be % or Rs or sell'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if ($offerValue < 0) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Value Should not be Negative'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_numeric($offerValue)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Value Should be a Number'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^\d+(\.\d{2})?$/', $offerValue)) {
                    // Does not have 2 decimal places
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Value Should have 2 Decimal Places'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */

                /* Split Yes and No Cities lists */
                $yesKeys = [];
                $noKeys = [];
                $considerRange = false;

                $yesZone = [];
                $noZone = [];
                $considerZone = false;

                foreach ($row as $keys => $value) {
                    // dd($row[$keys]);
                    if ($considerRange) {
                        if ($value === "Yes") {
                            $yesKeys[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noKeys[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'West Bengal') {
                            $considerRange = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Central') {
                        $considerRange = true;
                    }

                    if ($considerZone) {
                        if ($value === "Yes") {
                            $yesZone[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noZone[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'Central') {
                            $considerZone = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Pan India') {
                        $considerZone = true;
                    }
                }
                $yesString = implode(',', $yesKeys);
                $noString = implode(',', $noKeys);

                $yesZoneString = implode(', ', $yesZone);
                $noZoneString = implode(', ', $noZone);
                /* Split Yes and No Cities lists */

                $startDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[5]);
                $formattedStartDate = $startDateTime->format('d-m-Y H:i:s'); // Format for display
                $endDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[6]);
                $formattedEndDate = $endDateTime->format('d-m-Y H:i:s'); // Format for display

                $fundingCategory = Auth::user()->fundingCategory;
                $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                $redemptionPerMember = Auth::user()->redemptionPerMember;
                $insert_data_array[] = array(
                    'manufacturer_name' => $row[1],
                    'combo_code' => $row[2],
                    'combo_code_name' => $row[2],
                    'code' => $row[2],
                    'description' => $row[3],
                    'mrp' => $row[4],
                    'start_date' => DBdatetimeformat($formattedStartDate),
                    'endDate' => DBdatetimeformat($formattedEndDate),
                    'offerDetails' => $row[7],
                    'redemptionLimit' => $row[8],
                    'redemptionLimitPerCampaign' => $row[8],
                    'citiesSelection' => $row[9],
                    'isPanIndia' => ($row[10] === 'Yes') ? 1 : 0,
                    'zone_yes_lists' => $yesZoneString,
                    'zone_no_lists' => $noZoneString,
                    'cities_yes_lists' => $yesString,
                    'cities_no_lists' => $noString,
                    'department' => $row[35],
                    'businessModel' => $row[36],
                    'uploadedId' => $uploadedId,
                    'uploadedBy' => $user_id,
                    'process_type' => 1,
                    'sr_no' => ($row[0] == '' ? 0 : $row[0]),
                    'discountType' => $discountType,
                    'discountValue' => $offerValue,
                    'fundingCategory' => $fundingCategory,
                    'redemptionLimitPerOrder' => $redemptionPerOrder,
                    'redemptionLimitPerMember' => $redemptionPerMember,
                );
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                try {
                    if ($promoType == 1) {
                        DB::table('admin_preview')->insert($insert_data_array);
                    } else {
                        DB::table('admin_preview_combo')->insert($insert_data_array);
                    }
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

    public function uploadpromoCombo($insert_data, $log_id, $uploadedId, $promoType)
    {
        $user_id = auth()->user()->id;
        $error_data = [];
        $update_array = array(
            'extract_status' => 1,

        );
        DB::table('admin_upload_log')
            ->where('id', $log_id)
            ->update($update_array);

        $previous_combo_code = null;
        $previous_code = null;
        $xlsx = SimpleXLSX::parseFile($insert_data['file_path']);
        // dd($insert_data, $log_id, $uploadedId,$xlsx,$insert_data['file_orgname'] );
        // dd($promoType== 2,$insert_data['file_path'] == 'single_promotype',strpos($insert_data['file_path'], 'single_promotype'));
        try {
            $expectedColumns = [
                0 => 'Sr. No.',
                1 => 'Manufacturer / Suppler Name',
                2 => 'Combo Code',
                3 => 'Combo Description',
                4 => 'Code',
                5 => 'Product Description',
                6 => 'MRP',
                7 => 'Start Date (DD-MMM-YY)',
                8 => 'End Date (DD-MMM-YY)',
                9 => 'Offer Details(100% Vendor Funded)',
                10 => 'Redemption Limit - Qty Per Campaign',
                11 => 'Selections of Cities',
                12 => 'Pan India',
                13 => 'South',
                14 => 'North',
                15 => 'East',
                16 => 'West',
                17 => 'Central',
                18 => 'ANDHRA PRADESH',
                19 => 'TELANGANA',
                20 => 'ASSAM',
                21 => 'BIHAR',
                22 => 'CHHATTISGARH',
                23 => 'GUJARAT',
                24 => 'DELHI-NCR',
                25 => 'JHARKHAND',
                26 => 'KARNATAKA',
                27 => 'KERALA',
                28 => 'MADHYA PRADESH',
                29 => 'MAHARASHTRA - Mumbai',
                30 => 'MAHARASHTRA - Pune',
                31 => 'ORISSA',
                32 => 'PUNJAB',
                33 => 'RAJASTHAN',
                34 => 'TAMIL NADU',
                35 => 'UTTAR PRADESH',
                36 => 'WEST BENGAL',
                37 => 'Department',
                38 => 'Business Model'
            ];
            $validValues = ['Yes', 'No'];
            $i = 1;
            $insert_data_array = [];
            foreach ($xlsx->rows() as $key => $row) {

                if ($i == 1) {
                    if (count($row) >= count($expectedColumns)) {
                        foreach ($expectedColumns as $index => $columnName) {
                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'lineNumber' => $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'lineNumber' => $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
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
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Manufacturer / Suppler Name is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Combo Code is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['3'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Combo Descrition is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Code is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['5'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Product Description is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                // if ($row['6'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'MRP is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Start Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'End Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Offer Details(100% Vendor Funded) is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['10'] == '' && $row['12'] == 'Yes') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['11'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Selections of Cities is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues) || !in_array($row['35'], $validValues) ||
                    !in_array($row['36'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Pan India or City is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['37'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Department is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['38'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Business Model is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Redemption Limit - Qty Per Campaign greater than 20 for Pan India Yes  */
                if ($row['10'] >= 20 && $row['12'] == 'Yes' && $row['10'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Redemption Limit - Qty Per Campaign Should be Less than 20 for Pan India'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Other Cities has No if Pan India Yes */
                $cityValues = array_slice($row, 13, 24); // Assuming these keys represent city values 11 to 34.
                if ($row['12'] == 'Yes' && in_array('No', $cityValues, true)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'One of the Cities Mentioned as No IF PanIndia is in Yes'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Format Check */
                if (!preg_match('/^[A-Za-z0-9\s]+$/', $row['1'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Manufacturer / Suppler Name Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[0-9\s,]+$/', $row['2'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Combo Code Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$-]+$/', $row['3']) && $row['3'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Combo Description Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[0-9\s,]+$/', $row['4'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Code Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$-]+$/', $row['5']) && $row['5'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Product Description Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_int($row['6']) && $row['6'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'MRP Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!Carbon::hasFormat($row['7'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Start Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['7'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'lineNumber' => $i,
                            'error' => 'Start Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }
                
                if (!Carbon::hasFormat($row['8'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'End Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['8'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'lineNumber' => $i,
                            'error' => 'Start Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }
                
                if (!preg_match('/^[A-Za-z0-9\s!@#$%.]+$/', $row['9'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Offer Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_numeric($row['10']) && $row['10'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Redemption Limit Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if (!preg_match('/^[A-Za-z0-9\s!@#$]+$/', $row['11'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Selection of Cities Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues) || !in_array($row['35'], $validValues) ||
                    !in_array($row['36'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'City Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%-]+$/', $row['37'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Department Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%]+$/', $row['38'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Business Model Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */
                preg_match("/([0-9.]+)/", $row['9'], $offerValue);
                $offerValue = $offerValue[1];
                $discountType = '';

                if (str_contains($row['9'], '%')) {
                    $discountType = 'Percent';
                } else if (str_contains($row['9'], 'Rs')) {
                    $discountType = 'Flat';
                } else if (str_contains($row['9'], 'sell')) {
                    $discountType = 'Fixed';
                } else {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Type should be % or Rs or sell'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if ($offerValue < 0) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Value Should not be Negative'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if (!is_numeric($offerValue)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Value Should be a Number'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^\d+(\.\d{2})?$/', $offerValue)) {
                    // Does not have 2 decimal places
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'lineNumber' => $i,
                        'error' => 'Discount Value Should have 2 Decimal Places'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */

                /* Split Yes and No Cities lists */
                $yesKeys = [];
                $noKeys = [];
                $considerRange = false;

                $yesZone = [];
                $noZone = [];
                $considerZone = false;

                foreach ($row as $keys => $value) {
                    // dd($row[$keys]);
                    if ($considerRange) {
                        if ($value === "Yes") {
                            $yesKeys[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noKeys[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'West Bengal') {
                            $considerRange = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Central') {
                        $considerRange = true;
                    }

                    if ($considerZone) {
                        if ($value === "Yes") {
                            $yesZone[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noZone[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'Central') {
                            $considerZone = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Pan India') {
                        $considerZone = true;
                    }
                }
                $yesString = implode(',', $yesKeys);
                $noString = implode(',', $noKeys);

                $yesZoneString = implode(',', $yesZone);
                $noZoneString = implode(',', $noZone);

                /* Split Yes and No Cities lists */

                $startDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[7]);
                $formattedStartDate = $startDateTime->format('d-m-Y H:i:s'); // Format for display
                $endDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[8]);
                $formattedEndDate = $endDateTime->format('d-m-Y H:i:s'); // Format for display
                // $combo_codes = (strpos($row[2], ',') !== false) ? explode(',', $row[2]) : [$row[2]];
                // $codes = (strpos($row[4], ',') !== false) ? explode(',', $row[4]) : [$row[4]];

                $combo_codes = string_to_array($row[2]);
                $codes = string_to_array($row[4]);
                if (count($combo_codes) > 1 || count($codes) > 1) {
                    $max_count = max(count($combo_codes), count($codes));
                } else {
                    $max_count = 1;
                }
                $fundingCategory = Auth::user()->fundingCategory;
                $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                $redemptionPerMember = Auth::user()->redemptionPerMember;

                for ($index = 0; $index < $max_count; $index++) {
                    $combo_code = isset($combo_codes[$index]) ? trim($combo_codes[$index]) : $previous_combo_code;
                    $code = isset($codes[$index]) ? trim($codes[$index]) : $previous_code;
                    $insert_data_array[] = array(
                        'manufacturer_name' => $row[1],
                        'combo_code' => $combo_code,
                        'combo_code_name' => $row[3],
                        'code' => $code,
                        'description' => $row[5],
                        'mrp' => $row[6],
                        'start_date' => DBdatetimeformat($formattedStartDate),
                        'endDate' => DBdatetimeformat($formattedEndDate),
                        'offerDetails' => $row[9],
                        'redemptionLimit' => $row[10],
                        'redemptionLimitPerCampaign' => $row[10],
                        'citiesSelection' => $row[11],
                        'isPanIndia' => ($row[12] === 'Yes') ? 1 : 0,
                        'zone_yes_lists' => $yesZoneString,
                        'zone_no_lists' => $noZoneString,
                        'cities_yes_lists' => $yesString,
                        'cities_no_lists' => $noString,
                        'department' => $row[37],
                        'businessModel' => $row[38],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                        'process_type' => 1,
                        'sr_no' => ($row[0] == '' ? 0 : $row[0]),
                        'discountType' => $discountType,
                        'discountValue' => $offerValue,
                        'fundingCategory' => $fundingCategory,
                        'redemptionLimitPerOrder' => $redemptionPerOrder,
                        'redemptionLimitPerMember' => $redemptionPerMember,
                    );
                    $previous_combo_code = $combo_code;
                    $previous_code = $code;
                }

                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                try {
                    if ($promoType == 1) {
                        DB::table('admin_preview')->insert($insert_data_array);
                    } else {
                        DB::table('admin_preview_combo')->insert($insert_data_array);
                    }
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

    public function uploadpromoCartLevel($insert_data, $log_id, $uploadedId, $promoType)
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
                0 => 'SKU ID',
                1 => 'Discount Type',
                2 => 'Discount Value',
                3 => 'Funding - Category',
                4 => 'Funding - Marketing',
                5 => 'Funding - Vendor',
                6 => 'Invoiced ?',
                7 => 'Redemption Limit - Qty Per Order',
                8 => 'Redemption Limit - Qty Per Member',
                9 => 'Redemption Limit - Qty Per Campaign',
                // 10 => 'Default Invoice',
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
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                }
                if ($key === 0) {
                    continue;
                }
                $cond_error_data = [];
                /**Mandatory Field Check */
                if ($row['0'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'SKU ID is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['1'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['3'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Category Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Marketing is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Vendor is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Invoiced ? is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Order is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Member is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['10'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Default Invoice is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                // }
                // $fundingCategory = Auth::user()->fundingCategory;
                // $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                // $redemptionPerMember = Auth::user()->redemptionPerMember;

                if($row[6] == 'TRUE'){
                    $invoice = 1;
                }else{
                    $invoice = 0;
                }
                    $insert_data_array[] = array(
                        'skuId' => $row[0],
                        'discountType' => $row[1],
                        'discountValue' =>$row[2],
                        'fundingCategory' => $row[3],
                        'fundingMarket' => $row[4],
                        'fundtionVendor' => $row[5],
                        'isInvoiced' => $invoice,
                        'redemptionLimitPerOrder' => $row[7],
                        'redemptionLimitPerMember' => $row[8],
                        'redemptionLimitPerCampaign' => $row[9],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                    );
                    

                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                try {
                    DB::table('admin_preview_cart_level')->insert($insert_data_array);
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


    public function uploadpromoCartFree($insert_data, $log_id, $uploadedId, $promoType)
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
                0 => 'SKU ID',
                1 => 'Discount Type',
                2 => 'Discount Value',
                3 => 'Funding - Category',
                4 => 'Funding - Marketing',
                5 => 'Funding - Vendor',
                6 => 'Invoiced ?',
                7 => 'Redemption Limit - Qty Per Order',
                8 => 'Redemption Limit - Qty Per Member',
                9 => 'Redemption Limit - Qty Per Campaign',
                // 10 => 'Default Invoice',
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
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                }
                if ($key === 0) {
                    continue;
                }
                $cond_error_data = [];
                /**Mandatory Field Check */
                if ($row['0'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'SKU ID is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['1'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['3'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Category Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Marketing is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Vendor is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Invoiced ? is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Order is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Member is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['10'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Default Invoice is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                // }
                // $fundingCategory = Auth::user()->fundingCategory;
                // $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                // $redemptionPerMember = Auth::user()->redemptionPerMember;

                if($row[6] == 'TRUE'){
                    $invoice = 1;
                }else{
                    $invoice = 0;
                }
                    $insert_data_array[] = array(
                        'skuId' => $row[0],
                        'discountType' => $row[1],
                        'discountValue' =>$row[2],
                        'fundingCategory' => $row[3],
                        'fundingMarket' => $row[4],
                        'fundtionVendor' => $row[5],
                        'isInvoiced' => $invoice,
                        'redemptionLimitPerOrder' => $row[7],
                        'redemptionLimitPerMember' => $row[8],
                        'redemptionLimitPerCampaign' => $row[9],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                    );
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                try {
                    DB::table('admin_preview_cart_free')->insert($insert_data_array);
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

    public function uploadpromoCartGroup($insert_data, $log_id, $uploadedId, $promoType)
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
                0 => 'Reward SKU ID',
                1 => 'Reward Qty',
                2 => 'Discount Type',
                3 => 'Discount Value',
                4 => 'Funding - Category',
                5 => 'Funding - Marketing',
                6 => 'Funding - Vendor',
                7 => 'Invoiced ?',
                8 => 'Redemption Limit - Qty Per Order',
                9 => 'Redemption Limit - Qty Per Member',
                10 => 'Redemption Limit - Qty Per Campaign',
                // 10 => 'Default Invoice',
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
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                }
                if ($key === 0) {
                    continue;
                }
                $cond_error_data = [];
                /**Mandatory Field Check */
                if ($row['0'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Reward SKU ID is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['1'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Qty is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['3'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Category Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Marketing is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Vendor is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Invoiced ? is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Order is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Member is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['10'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if($row[6] == 'TRUE'){
                    $invoice = 1;
                }else{
                    $invoice = 0;
                }
                    $insert_data_array[] = array(
                        'skuId' => $row[0],
                        'rewardQty' => $row[1],
                        'discountType' => $row[2],
                        'discountValue' =>$row[3],
                        'fundingCategory' => $row[4],
                        'fundingMarket' => $row[5],
                        'fundtionVendor' => $row[6],
                        'isInvoiced' => $invoice,
                        'redemptionLimitPerOrder' => $row[8],
                        'redemptionLimitPerMember' => $row[9],
                        'redemptionLimitPerCampaign' => $row[10],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                    );
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                try {
                    DB::table('admin_preview_cart_group')->insert($insert_data_array);
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

    /* Single and Combo Promo for Upload */


    /* Single and Combo Promo for Edit upload */

    public function PromotypeFileEdit(Request $request)
    {
        try {
            $file = $request->file('promotype_file');
            $id = decryptId($request->promotionid);
            if ($file != null) {
                $uploadpath = 'public/uploads/promotion';

                $filenewname = time() . Str::random('10') . '.' . $file->getClientOriginalExtension();
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileMimetype = $file->getMimeType();
                $fileExt = $file->getClientOriginalExtension();
                $file->move($uploadpath, $filenewname);
                $path = $uploadpath . "/" . $filenewname;

                // if (!is_dir($uploadpath)) {
                //     mkdir($uploadpath, 0755, true);
                // }
                // chmod($uploadpath,0777);
                // chmod($path, 0777);

                $user_id = auth()->user()->id;
                $xlsx = SimpleXLSX::parseFile($path);
                if (count($xlsx->rows()) == 1) {
                    // No data rows, only a heading row, so don't proceed with insertion
                    unlink($path); // Delete the uploaded file
                    Session::flash('error', 'The uploaded file contains only a heading row and no data. Please upload a valid file.');
                    return redirect(admin_url('upload_promotion/preview/' . encryptId($id)));
                }

                $promotion_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'manufacturer_id' => $user_id,
                    'updated_by' => $user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                );
                $data = $this->promotion->UpdatePromotion($id, $promotion_data);
                $insert_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'currentStatus' => 2,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                );
                $update = $this->uploadLog->Updatefun($insert_data, $id);
                $getId = $this->uploadLog->Getidfun($id);
                // dd($insert_data);
                $this->uploadPromoUpdate($insert_data, $getId['id'], $id);
            }

            Session::flash('success', 'Successfully Promotype uploaded !');
            return redirect(admin_url('upload_promotion/preview/' . encryptId($id)));
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('upload_promotion'));
        }
    }

    public function PromotypeFileEditCombo(Request $request)
    {
        try {
            $file = $request->file('promotype_file');
            $id = decryptId($request->promotionid);
            if ($file != null) {
                $uploadpath = 'public/uploads/promotion';
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
                    return redirect(admin_url('upload_promotion/preview_combo/' . encryptId($id)));
                }

                $promotion_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'manufacturer_id' => $user_id,
                    'updated_by' => $user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'file_orgname' => $fileName,
                );
                $data = $this->promotion->UpdatePromotion($id, $promotion_data);

                $insert_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'currentStatus' => 2,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                    'file_orgname' => $fileName,

                );
                $update = $this->uploadLog->Updatefun($insert_data, $id);
                $getId = $this->uploadLog->Getidfun($id);
                // dd($getId['id']);
                $this->uploadPromoComboUpdate($insert_data, $getId['id'], $id);
            }

            Session::flash('success', 'Successfully Promotype uploaded !');
            return redirect(admin_url('upload_promotion/preview_combo/' . encryptId($id)));
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('upload_promotion'));
        }
    }

    public function PromotypeFileEditCartLevel(Request $request)
    {
        try {
            $file = $request->file('promotype_file');
            $id = decryptId($request->promotionid);
            $minimum_value = $request->minimum_value;
            if ($file != null) {
                $uploadpath = 'public/uploads/promotion';
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
                    return redirect(admin_url('upload_promotion/preview_cart_level/' . encryptId($id)));
                }


                $promotion_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'manufacturer_id' => $user_id,
                    'updated_by' => $user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'file_orgname' => $fileName,
                    'minimum_value' => $minimum_value,
                );
                $data = $this->promotion->UpdatePromotion($id, $promotion_data);
                $insert_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'currentStatus' => 2,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                );
                $update = $this->uploadLog->Updatefun($insert_data, $id);
                $getId = $this->uploadLog->Getidfun($id);
                $this->uploadPromoCartLevelUpdate($insert_data, $getId['id'], $id);
            }

            Session::flash('success', 'Successfully Promotype uploaded !');
            return redirect(admin_url('upload_promotion/preview_cart_level/' . encryptId($id)));
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('upload_promotion'));
        }
    }
   
    public function PromotypeFileEditCartFree(Request $request)
    {

        try {
            $file = $request->file('promotype_file');
            $id = decryptId($request->promotionid);
            if ($file != null) {
                $uploadpath = 'public/uploads/promotion';
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
                    return redirect(admin_url('upload_promotion/preview_cart_free/' . encryptId($id)));
                }


                $promotion_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'manufacturer_id' => $user_id,
                    'updated_by' => $user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'file_orgname' => $fileName,
                );
                $data = $this->promotion->UpdatePromotion($id, $promotion_data);
                $insert_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'currentStatus' => 2,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                );
                $update = $this->uploadLog->Updatefun($insert_data, $id);
                $getId = $this->uploadLog->Getidfun($id);
                $this->uploadPromoCartFreeUpdate($insert_data, $getId['id'], $id);
            }

            Session::flash('success', 'Successfully Promotype uploaded !');
            return redirect(admin_url('upload_promotion/preview_cart_free/' . encryptId($id)));
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('upload_promotion'));
        }
    }
    
    public function PromotypeFileEditCartGroup(Request $request)
    {

        try {
            $file = $request->file('promotype_file');
            $id = decryptId($request->promotionid);
            if ($file != null) {
                $uploadpath = 'public/uploads/promotion';
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
                    return redirect(admin_url('upload_promotion/preview_cart_free/' . encryptId($id)));
                }


                $promotion_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'manufacturer_id' => $user_id,
                    'updated_by' => $user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'file_orgname' => $fileName,
                );
                $data = $this->promotion->UpdatePromotion($id, $promotion_data);
                $insert_data = array(
                    'file_path' => $path,
                    'file_name' => $filenewname,
                    'file_orgname' => $fileName,
                    'currentStatus' => 2,
                    'upload_type' => '2',
                    'created_by' => $user_id,
                );
                $update = $this->uploadLog->Updatefun($insert_data, $id);
                $getId = $this->uploadLog->Getidfun($id);
                $this->uploadPromoCartGroupUpdate($insert_data, $getId['id'], $id);
            }

            Session::flash('success', 'Successfully Promotype uploaded !');
            return redirect(admin_url('upload_promotion/preview_cart_group/' . encryptId($id)));
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('upload_promotion'));
        }
    }

    public function uploadPromoUpdate($insert_data, $log_id, $uploadedId)
    {
        $user_id = auth()->user()->id;
        $error_data = [];
        $update_array = array(
            'extract_status' => 1,

        );
        DB::table('admin_upload_log')
            ->where('file_uploaded_id', $uploadedId)
            ->update($update_array);

        $xlsx = SimpleXLSX::parseFile($insert_data['file_path']);

        try {
            $expectedColumns = [
                0 => 'Sr. No.',
                1 => 'Manufacturer / Suppler Name',
                2 => 'Code',
                3 => 'Product Description',
                4 => 'MRP',
                5 => 'Start Date (DD-MMM-YY)',
                6 => 'End Date (DD-MMM-YY)',
                7 => 'Offer Details(100% Vendor Funded)',
                8 => 'Redemption Limit - Qty Per Campaign',
                9 => 'Selections of Cities',
                10 => 'Pan India',
                11 => 'South',
                12 => 'North',
                13 => 'East',
                14 => 'West',
                15 => 'Central',
                16 => 'ANDHRA PRADESH',
                17 => 'TELANGANA',
                18 => 'ASSAM',
                19 => 'BIHAR',
                20 => 'CHHATTISGARH',
                21 => 'GUJARAT',
                22 => 'DELHI-NCR',
                23 => 'JHARKHAND',
                24 => 'KARNATAKA',
                25 => 'KERALA',
                26 => 'MADHYA PRADESH',
                27 => 'MAHARASHTRA - Mumbai',
                28 => 'MAHARASHTRA - Pune',
                29 => 'ORISSA',
                30 => 'PUNJAB',
                31 => 'RAJASTHAN',
                32 => 'TAMIL NADU',
                33 => 'UTTAR PRADESH',
                34 => 'WEST BENGAL',
                35 => 'Department',
                36 => 'Business Model'
            ];
            $validValues = ['Yes', 'No'];
            $i = 1;
            $insert_data_array = [];
            $updateTrash = array(
                'trash' => 'YES',
                'status' => 0
            );
            DB::table('admin_upload_error_log')->where('uploadedId', $uploadedId)->update($updateTrash);
            DB::table('admin_preview')->Where('uploadedId', $uploadedId)->update($updateTrash);
            foreach ($xlsx->rows() as $key => $row) {

                if ($i == 1) {
                    if (count($row) >= count($expectedColumns)) {
                        foreach ($expectedColumns as $index => $columnName) {
                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
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
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Manufacturer / Suppler Name is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Code is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['3'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Product Description is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                // if ($row['4'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'MRP is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Start Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'End Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Offer Details(100% Vendor Funded) is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if ($row['8'] == '' && $row['10'] == 'Yes') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Selections of Cities is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['10'], $validValues) || !in_array($row['11'], $validValues) ||
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Pan India or one of the City is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['35'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Department is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['36'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Business Model is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Redemption Limit - Qty Per Campaign greater than 20 for Pan India Yes  */
                if ($row['8'] >= 20 && $row['10'] == 'Yes' && $row['8'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign Should be Less than 20 for Pan India'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Other Cities has No if Pan India Yes */
                $cityValues = array_slice($row, 11, 24); // Assuming these keys represent city values 11 to 34.
                if ($row['10'] == 'Yes' && in_array('No', $cityValues, true)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'One of the Cities Mentioned as No IF PanIndia is in Yes'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /** Format Check */
                if (!preg_match('/^[A-Za-z0-9\s]+$/', $row['1'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Manufacturer / Suppler Name Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_int($row['2'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Code Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$-]+$/', $row['3']) && $row['3'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Product Description Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_int($row['4']) && $row['4'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'MRP Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!Carbon::hasFormat($row['5'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Start Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['5'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'error' => 'Start Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }
               
                if (!Carbon::hasFormat($row['6'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'End Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['6'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'error' => 'Start Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }
                
                if (!preg_match('/^[A-Za-z0-9\s!@#$%.]+$/', $row['7'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Offer Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_numeric($row['8']) && $row['8'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if (!preg_match('/^[A-Za-z0-9\s!@#$]+$/', $row['9'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Selection of Cities Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['10'], $validValues) || !in_array($row['11'], $validValues) ||
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'City Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%-]+$/', $row['35'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Department Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%]+$/', $row['36'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Business Model Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */
                preg_match("/([0-9.]+)/", $row['7'], $offerValue);
                $offerValue = $offerValue[1];
                $discountType = '';

                if (str_contains($row['7'], '%')) {
                    $discountType = 'Percent';
                } else if (str_contains($row['7'], 'Rs')) {
                    $discountType = 'Flat';
                } else if (str_contains($row['7'], 'sell')) {
                    $discountType = 'Fixed';
                } else {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type should be % or Rs or sell'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if ($offerValue < 0) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value Should not be Negative'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if (!is_numeric($offerValue)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value Should be a Number'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^\d+(\.\d{2})?$/', $offerValue)) {
                    // Does not have 2 decimal places
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value Should have 2 Decimal Places'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */


                /* Split Yes and No Cities lists */
                $yesKeys = [];
                $noKeys = [];
                $considerRange = false;

                $yesZone = [];
                $noZone = [];
                $considerZone = false;

                foreach ($row as $keys => $value) {
                    // dd($row[$keys]);
                    if ($considerRange) {
                        if ($value === "Yes") {
                            $yesKeys[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noKeys[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'West Bengal') {
                            $considerRange = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Central') {
                        $considerRange = true;
                    }

                    if ($considerZone) {
                        if ($value === "Yes") {
                            $yesZone[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noZone[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'Central') {
                            $considerZone = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Pan India') {
                        $considerZone = true;
                    }
                }
                $yesString = implode(',', $yesKeys);
                $noString = implode(',', $noKeys);

                $yesZoneString = implode(',', $yesZone);
                $noZoneString = implode(',', $noZone);

                /* Split Yes and No Cities lists */

                $startDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[5]);
                $formattedStartDate = $startDateTime->format('d-m-Y H:i:s'); // Format for display
                $endDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[6]);
                $formattedEndDate = $endDateTime->format('d-m-Y H:i:s'); // Format for display
                $fundingCategory = Auth::user()->fundingCategory;
                $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                $redemptionPerMember = Auth::user()->redemptionPerMember;
                $insert_data_array[] = array(
                    'manufacturer_name' => $row[1],
                    'combo_code' => $row[2],
                    'combo_code_name' => $row[2],
                    'code' => $row[2],
                    'description' => $row[3],
                    'mrp' => $row[4],
                    'start_date' => DBdatetimeformat($formattedStartDate),
                    'endDate' => DBdatetimeformat($formattedEndDate),
                    'offerDetails' => $row[7],
                    'redemptionLimit' => $row[8],
                    'citiesSelection' => $row[9],
                    'isPanIndia' => ($row[10] === 'Yes') ? 1 : 0,
                    'zone_yes_lists' => $yesZoneString,
                    'zone_no_lists' => $noZoneString,
                    'cities_yes_lists' => $yesString,
                    'cities_no_lists' => $noString,
                    'department' => $row[35],
                    'businessModel' => $row[36],
                    'uploadedId' => $uploadedId,
                    'uploadedBy' => $user_id,
                    'process_type' => 1,
                    'sr_no' => ($row[0] == '' ? 0 : $row[0]),
                    'discountValue' => $offerValue,
                    'discountType' => $discountType,
                    'fundingCategory' => $fundingCategory,
                    'redemptionLimitPerOrder' => $redemptionPerOrder,
                    'redemptionLimitPerMember' => $redemptionPerMember,
                );
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                DB::table('admin_preview')->insert($insert_data_array);
            }
        } catch (\Exception $ex) {
            dd($ex);
            report($ex);
        }
    }

    public function uploadPromoComboUpdate($insert_data, $log_id, $uploadedId)
    {
        $user_id = auth()->user()->id;
        $error_data = [];
        $update_array = array(
            'extract_status' => 1,

        );
        DB::table('admin_upload_log')
            ->where('file_uploaded_id', $uploadedId)
            ->update($update_array);
        $previous_combo_code = null;
        $previous_code = null;
        $xlsx = SimpleXLSX::parseFile($insert_data['file_path']);
        try {
            $expectedColumns = [
                0 => 'Sr. No.',
                1 => 'Manufacturer / Suppler Name',
                2 => 'Combo Code',
                3 => 'Combo Description',
                4 => 'Code',
                5 => 'Product Description',
                6 => 'MRP',
                7 => 'Start Date (DD-MMM-YY)',
                8 => 'End Date (DD-MMM-YY)',
                9 => 'Offer Details(100% Vendor Funded)',
                10 => 'Redemption Limit - Qty Per Campaign',
                11 => 'Selections of Cities',
                12 => 'Pan India',
                13 => 'South',
                14 => 'North',
                15 => 'East',
                16 => 'West',
                17 => 'Central',
                18 => 'ANDHRA PRADESH',
                19 => 'TELANGANA',
                20 => 'ASSAM',
                21 => 'BIHAR',
                22 => 'CHHATTISGARH',
                23 => 'GUJARAT',
                24 => 'DELHI-NCR',
                25 => 'JHARKHAND',
                26 => 'KARNATAKA',
                27 => 'KERALA',
                28 => 'MADHYA PRADESH',
                29 => 'MAHARASHTRA - Mumbai',
                30 => 'MAHARASHTRA - Pune',
                31 => 'ORISSA',
                32 => 'PUNJAB',
                33 => 'RAJASTHAN',
                34 => 'TAMIL NADU',
                35 => 'UTTAR PRADESH',
                36 => 'WEST BENGAL',
                37 => 'Department',
                38 => 'Business Model'
            ];
            $validValues = ['Yes', 'No'];
            $i = 1;
            $insert_data_array = [];
            $updateTrash = array(
                'trash' => 'YES',
                'status' => 0
            );
            DB::table('admin_upload_error_log')->where('uploadedId', $uploadedId)->update($updateTrash);
            DB::table('admin_preview_combo')->Where('uploadedId', $uploadedId)->update($updateTrash);
            foreach ($xlsx->rows() as $key => $row) {

                if ($i == 1) {
                    if (count($row) >= count($expectedColumns)) {
                        foreach ($expectedColumns as $index => $columnName) {
                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
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
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Manufacturer / Suppler Name is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Combo Code is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['3'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Combo Descrition is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Code is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['5'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Product Description is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                // if ($row['6'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'MRP is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Start Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'End Date is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Offer Details(100% Vendor Funded) is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['10'] == '' && $row['12'] == 'Yes') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['11'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Selections of Cities is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues) || !in_array($row['35'], $validValues) ||
                    !in_array($row['36'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Pan India or City is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['37'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Department is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['38'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Business Model is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Redemption Limit - Qty Per Campaign greater than 20 for Pan India Yes  */
                if ($row['10'] >= 20 && $row['12'] == 'Yes' && $row['10'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign Should be Less than 20 for Pan India'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Check Other Cities has No if Pan India Yes */
                $cityValues = array_slice($row, 13, 24);
                if ($row['12'] == 'Yes' && in_array('No', $cityValues, true)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'One of the Cities Mentioned as No IF PanIndia is in Yes'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                /** Format Check */
                if (!preg_match('/^[A-Za-z0-9\s]+$/', $row['1'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Manufacturer / Suppler Name Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[0-9\s,]+$/', $row['2'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Combo Code Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$-]+$/', $row['3']) && $row['3'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Combo Description Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[0-9\s,]+$/', $row['4']) && $row['4'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Code Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$-]+$/', $row['5']) && $row['5'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Product Description Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_int($row['6']) && $row['6'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'MRP Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!Carbon::hasFormat($row['7'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Start Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['7'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'error' => 'Start Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }
               
                if (!Carbon::hasFormat($row['8'], 'm-d-Y H:i')) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'End Date Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                } else {
                    $date = $row['8'];
                    $dateParts = explode(' ', $date);
                    $dateComponents = explode('-', $dateParts[0]);
                    $currentMonth = date('m');
                    $currentDay = date('d');
                    $currentYear = date('Y');

                    $currentMonthStartDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-25"));
                    if ($currentMonth == 12) {
                        $nextMonth = 1;
                        $nextYear = $currentYear + 1;
                    } else {
                        $nextMonth = $currentMonth + 1;
                        $nextYear = $currentYear;
                    }
                    $nextMonthEndDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-24"));

                    $inputDate = date('Y-m-d', strtotime($dateComponents[2] . '-' . $dateComponents[0] . '-' . $dateComponents[1]));

                    if ($inputDate < $currentMonthStartDate || $inputDate > $nextMonthEndDate) {
                        // Start Date is not within the specified range
                        // Handle the validation error here
                        $cond_error_data = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in Line Number " . $i,
                            'error' => 'Start Date should be between ' . date('d M Y', strtotime($currentMonthStartDate)) . ' and ' . date('d M Y', strtotime($nextMonthEndDate))
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                        $i++;
                        continue;
                    }
                }
                /* else {
                    $startDate = $row['5'];
                    $endDate = $row['6'];

                    $startDateParts = explode('-', $startDate);
                    $endDateParts = explode('-', $endDate);

                    if (count($startDateParts) === 3 && count($endDateParts) === 3) {
                        $startMonth = (int)$startDateParts[0];
                        $startDay = (int)$startDateParts[1];
                        $startYear = (int)$startDateParts[2];
                        $endMonth = (int)$endDateParts[0];
                        $endDay = (int)$endDateParts[1];
                        $endYear = (int)$endDateParts[2];
                        $expectedEndYear = $startYear;
                        $expectedEndMonth = $startMonth + 1;
                        if ($expectedEndMonth > 12) {
                            $expectedEndMonth = 1;
                            $expectedEndYear++;
                        }
                        if ($endYear !== $expectedEndYear || $endMonth !== $expectedEndMonth || $endDay !== 24) {
                            $cond_error_data = array(
                                'log_id' => $log_id,
                                'uploadedId' => $uploadedId,
                                'file_name' => "Error in Line Number " . $i,
                                'error' => 'End Date is not the 24th'
                            );
                            $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                            $i++;
                            continue;
                        }
                    }
                }*/
                if (!preg_match('/^[A-Za-z0-9\s!@#$%.]+$/', $row['9'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Offer Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!is_numeric($row['10']) && $row['10'] != '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if (!preg_match('/^[A-Za-z0-9\s!@#$]+$/', $row['11'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Selection of Cities Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (
                    !in_array($row['12'], $validValues) || !in_array($row['13'], $validValues) ||
                    !in_array($row['14'], $validValues) || !in_array($row['15'], $validValues) ||
                    !in_array($row['16'], $validValues) || !in_array($row['17'], $validValues) ||
                    !in_array($row['18'], $validValues) || !in_array($row['19'], $validValues) ||
                    !in_array($row['20'], $validValues) || !in_array($row['21'], $validValues) ||
                    !in_array($row['22'], $validValues) || !in_array($row['23'], $validValues) ||
                    !in_array($row['24'], $validValues) || !in_array($row['25'], $validValues) ||
                    !in_array($row['26'], $validValues) || !in_array($row['27'], $validValues) ||
                    !in_array($row['28'], $validValues) || !in_array($row['29'], $validValues) ||
                    !in_array($row['30'], $validValues) || !in_array($row['31'], $validValues) ||
                    !in_array($row['32'], $validValues) || !in_array($row['33'], $validValues) ||
                    !in_array($row['34'], $validValues) || !in_array($row['35'], $validValues) ||
                    !in_array($row['36'], $validValues)
                ) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'City Details Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%-]+$/', $row['37'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Department Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^[A-Za-z0-9\s!@#$%]+$/', $row['38'])) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Business Model Format mismatch'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */
                preg_match("/([0-9.]+)/", $row['9'], $offerValue);
                $offerValue = $offerValue[1];
                $discountType = '';

                if (str_contains($row['9'], '%')) {
                    $discountType = 'Percent';
                } else if (str_contains($row['9'], 'Rs')) {
                    $discountType = 'Flat';
                } else if (str_contains($row['9'], 'sell')) {
                    $discountType = 'Fixed';
                } else {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type should be % or Rs or sell'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if ($offerValue < 0) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value Should not be Negative'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                if (!is_numeric($offerValue)) {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value Should be a Number'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if (!preg_match('/^\d+(\.\d{2})?$/', $offerValue)) {
                    // Does not have 2 decimal places
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value Should have 2 Decimal Places'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }

                /*Split percentage, Flat, Fixed */

                /* Split Yes and No Cities lists */
                $yesKeys = [];
                $noKeys = [];
                $considerRange = false;

                $yesZone = [];
                $noZone = [];
                $considerZone = false;

                foreach ($row as $keys => $value) {
                    // dd($row[$keys]);
                    if ($considerRange) {
                        if ($value === "Yes") {
                            $yesKeys[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noKeys[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'West Bengal') {
                            $considerRange = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Central') {
                        $considerRange = true;
                    }

                    if ($considerZone) {
                        if ($value === "Yes") {
                            $yesZone[] = $expectedColumns[$keys];
                        } elseif ($value === "No") {
                            $noZone[] = $expectedColumns[$keys];
                        }

                        if ($expectedColumns[$keys] == 'Central') {
                            $considerZone = false;
                        }
                    } elseif ($expectedColumns[$keys] == 'Pan India') {
                        $considerZone = true;
                    }
                }
                $yesString = implode(',', $yesKeys);
                $noString = implode(',', $noKeys);

                $yesZoneString = implode(',', $yesZone);
                $noZoneString = implode(',', $noZone);
                /* Split Yes and No Cities lists */

                $startDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[7]);
                $formattedStartDate = $startDateTime->format('d-m-Y H:i:s'); // Format for display
                $endDateTime = Carbon::createFromFormat('m-d-Y H:i', $row[8]);
                $formattedEndDate = $endDateTime->format('d-m-Y H:i:s'); // Format for display
                $combo_codes = string_to_array($row[2]);
                $codes = string_to_array($row[4]);
                if (count($combo_codes) > 1 || count($codes) > 1) {
                    $max_count = max(count($combo_codes), count($codes));
                } else {
                    $max_count = 1;
                }
                $fundingCategory = Auth::user()->fundingCategory;
                $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                $redemptionPerMember = Auth::user()->redemptionPerMember;

                for ($index = 0; $index < $max_count; $index++) {
                    $combo_code = isset($combo_codes[$index]) ? trim($combo_codes[$index]) : $previous_combo_code;
                    $code = isset($codes[$index]) ? trim($codes[$index]) : $previous_code;
                    $insert_data_array[] = array(
                        'manufacturer_name' => $row[1],
                        'combo_code' => $combo_code,
                        'combo_code_name' => $row[3],
                        'code' => $code,
                        'description' => $row[5],
                        'mrp' => $row[6],
                        'start_date' => DBdatetimeformat($formattedStartDate),
                        'endDate' => DBdatetimeformat($formattedEndDate),
                        'offerDetails' => $row[9],
                        'redemptionLimit' => $row[10],
                        'redemptionLimitPerCampaign' => $row[10],
                        'citiesSelection' => $row[11],
                        'isPanIndia' => ($row[12] === 'Yes') ? 1 : 0,
                        'zone_yes_lists' => $yesZoneString,
                        'zone_no_lists' => $noZoneString,
                        'cities_yes_lists' => $yesString,
                        'cities_no_lists' => $noString,
                        'department' => $row[37],
                        'businessModel' => $row[38],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                        'process_type' => 1,
                        'sr_no' => ($row[0] == '' ? 0 : $row[0]),
                        'discountType' => $discountType,
                        'discountValue' => $offerValue,
                        'fundingCategory' => $fundingCategory,
                        'redemptionLimitPerOrder' => $redemptionPerOrder,
                        'redemptionLimitPerMember' => $redemptionPerMember,
                    );
                    $previous_combo_code = $combo_code;
                    $previous_code = $code;
                }
                /* $insert_data_array[] = array(
                    'manufacturer_name' => $row[1],
                    'combo_code' => $row[2],
                    'combo_code_name' => $row[3],
                    'code' => $row[4],
                    'description' => $row[5],
                    'mrp' => $row[6],
                    'start_date' => DBdatetimeformat($formattedStartDate),
                    'endDate' => DBdatetimeformat($formattedEndDate),
                    'offerDetails' => $row[9],
                    'redemptionLimit' => $row[10],
                    'citiesSelection' => $row[11],
                    'isPanIndia' => ($row[12] === 'Yes') ? 1 : 0,
                    'zone_yes_lists' => $yesZoneString,
                    'zone_no_lists' => $noZoneString,
                    'cities_yes_lists' => $yesString,
                    'cities_no_lists' => $noString,
                    'department' => $row[37],
                    'businessModel' => $row[38],
                    'uploadedId' => $uploadedId,
                    'uploadedBy' => $user_id,
                    'process_type' => 1,
                    'sr_no' => $row[0]
                );
                */
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                DB::table('admin_preview_combo')->insert($insert_data_array);
            }
        } catch (\Exception $ex) {
            dd($ex);
            report($ex);
        }
    }




    public function uploadPromoCartLevelUpdate($insert_data, $log_id, $uploadedId)
    {
        $user_id = auth()->user()->id;
        $error_data = [];
        $update_array = array(
            'extract_status' => 1,

        );
        DB::table('admin_upload_log')
            ->where('file_uploaded_id', $uploadedId)
            ->update($update_array);
        $previous_combo_code = null;
        $previous_code = null;
        $xlsx = SimpleXLSX::parseFile($insert_data['file_path']);
        try {
            $expectedColumns = [
                0 => 'SKU ID',
                1 => 'Discount Type',
                2 => 'Discount Value',
                3 => 'Funding - Category',
                4 => 'Funding - Marketing',
                5 => 'Funding - Vendor',
                6 => 'Invoiced ?',
                7 => 'Redemption Limit - Qty Per Order',
                8 => 'Redemption Limit - Qty Per Member',
                9 => 'Redemption Limit - Qty Per Campaign',
                // 10 => 'Default Invoice',
            ];
            $validValues = ['Yes', 'No'];
            $i = 1;
            $insert_data_array = [];
            $updateTrash = array(
                'trash' => 'YES',
                'status' => 0
            );
            DB::table('admin_upload_error_log')->where('uploadedId', $uploadedId)->update($updateTrash);
            DB::table('admin_preview_cart_level')->Where('uploadedId', $uploadedId)->update($updateTrash);
            foreach ($xlsx->rows() as $key => $row) {

                if ($i == 1) {
                    if (count($row) >= count($expectedColumns)) {
                        foreach ($expectedColumns as $index => $columnName) {
                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                }
                if ($key === 0) {
                    continue;
                }
                $cond_error_data = [];
                /**Mandatory Field Check */
                if ($row['0'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'SKU ID is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['1'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['3'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Category Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Marketing is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Vendor is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Invoiced ? is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Order is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Member is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['10'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Default Invoice is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                /** Format Check */
                /*Split percentage, Flat, Fixed */
                /*Split percentage, Flat, Fixed */
                /* Split Yes and No Cities lists */
                /* Split Yes and No Cities lists */
                    if($row[6] == 'TRUE'){
                        $invoice = 1;
                    }else{
                        $invoice = 0;
                    }
                // $fundingCategory = Auth::user()->fundingCategory;
                // $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                // $redemptionPerMember = Auth::user()->redemptionPerMember;
                    $insert_data_array[] = array(
                        'skuId' => $row[0],
                        'discountType' => $row[1],
                        'discountValue' =>$row[2],
                        'fundingCategory' => $row[3],
                        'fundingMarket' => $row[4],
                        'fundtionVendor' => $row[5],
                        'isInvoiced' => $invoice,
                        'redemptionLimitPerOrder' => $row[7],
                        'redemptionLimitPerMember' => $row[8],
                        'redemptionLimitPerCampaign' => $row[9],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                    );
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                DB::table('admin_preview_cart_level')->insert($insert_data_array);
            }
        } catch (\Exception $ex) {
            dd($ex);
            report($ex);
        }
    }
    public function uploadPromoCartFreeUpdate($insert_data, $log_id, $uploadedId)
    {
        $user_id = auth()->user()->id;
        $error_data = [];
        $update_array = array(
            'extract_status' => 1,

        );
        DB::table('admin_upload_log')
            ->where('file_uploaded_id', $uploadedId)
            ->update($update_array);
        $previous_combo_code = null;
        $previous_code = null;
        $xlsx = SimpleXLSX::parseFile($insert_data['file_path']);
        try {
            $expectedColumns = [
                0 => 'SKU ID',
                1 => 'Discount Type',
                2 => 'Discount Value',
                3 => 'Funding - Category',
                4 => 'Funding - Marketing',
                5 => 'Funding - Vendor',
                6 => 'Invoiced ?',
                7 => 'Redemption Limit - Qty Per Order',
                8 => 'Redemption Limit - Qty Per Member',
                9 => 'Redemption Limit - Qty Per Campaign',
                // 10 => 'Default Invoice',
            ];
            $validValues = ['Yes', 'No'];
            $i = 1;
            $insert_data_array = [];
            $updateTrash = array(
                'trash' => 'YES',
                'status' => 0
            );
            DB::table('admin_upload_error_log')->where('uploadedId', $uploadedId)->update($updateTrash);
            DB::table('admin_preview_cart_free')->Where('uploadedId', $uploadedId)->update($updateTrash);
            foreach ($xlsx->rows() as $key => $row) {

                if ($i == 1) {
                    if (count($row) >= count($expectedColumns)) {
                        foreach ($expectedColumns as $index => $columnName) {
                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                }
                if ($key === 0) {
                    continue;
                }
                $cond_error_data = [];
                /**Mandatory Field Check */
                if ($row['0'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'SKU ID is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['1'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['3'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Category Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Marketing is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Vendor is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Invoiced ? is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Order is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Member is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                // if ($row['10'] == '') {
                //     $cond_error_data = array(
                //         'log_id' => $log_id,
                //         'uploadedId' => $uploadedId,
                //         'file_name' => "Error in the Line Number " . $i,
                //         'error' => 'Default Invoice is Missing'
                //     );
                //     $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                //     $i++;
                //     continue;
                // }
                /** Format Check */
                /*Split percentage, Flat, Fixed */
                /*Split percentage, Flat, Fixed */
                /* Split Yes and No Cities lists */
                /* Split Yes and No Cities lists */

                    if($row[6] == 'TRUE'){
                        $invoice = 1;
                    }else{
                        $invoice = 0;
                    }
                // $fundingCategory = Auth::user()->fundingCategory;
                // $redemptionPerOrder = Auth::user()->redemptionPerOrder;
                // $redemptionPerMember = Auth::user()->redemptionPerMember;

                    $insert_data_array[] = array(
                        'skuId' => $row[0],
                        'discountType' => $row[1],
                        'discountValue' =>$row[2],
                        'fundingCategory' => $row[3],
                        'fundingMarket' => $row[4],
                        'fundtionVendor' => $row[5],
                        'isInvoiced' => $invoice,
                        'redemptionLimitPerOrder' => $row[7],
                        'redemptionLimitPerMember' => $row[8],
                        'redemptionLimitPerCampaign' => $row[9],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                    );
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                DB::table('admin_preview_cart_free')->insert($insert_data_array);
            }
        } catch (\Exception $ex) {
            dd($ex);
            report($ex);
        }
    }


    public function uploadPromoCartGroupUpdate($insert_data, $log_id, $uploadedId)
    {
        $user_id = auth()->user()->id;
        $error_data = [];
        $update_array = array(
            'extract_status' => 1,

        );
        DB::table('admin_upload_log')
            ->where('file_uploaded_id', $uploadedId)
            ->update($update_array);
        $previous_combo_code = null;
        $previous_code = null;
        $xlsx = SimpleXLSX::parseFile($insert_data['file_path']);
        try {
            $expectedColumns = [
                0 => 'Reward SKU ID',
                1 => 'Reward Qty',
                2 => 'Discount Type',
                3 => 'Discount Value',
                4 => 'Funding - Category',
                5 => 'Funding - Marketing',
                6 => 'Funding - Vendor',
                7 => 'Invoiced ?',
                8 => 'Redemption Limit - Qty Per Order',
                9 => 'Redemption Limit - Qty Per Member',
                10 =>'Redemption Limit - Qty Per Campaign',
                // 10 => 'Default Invoice',
            ];
            $validValues = ['Yes', 'No'];
            $i = 1;
            $insert_data_array = [];
            $updateTrash = array(
                'trash' => 'YES',
                'status' => 0
            );
            DB::table('admin_upload_error_log')->where('uploadedId', $uploadedId)->update($updateTrash);
            DB::table('admin_preview_cart_group')->Where('uploadedId', $uploadedId)->update($updateTrash);
            foreach ($xlsx->rows() as $key => $row) {

                if ($i == 1) {
                    if (count($row) >= count($expectedColumns)) {
                        foreach ($expectedColumns as $index => $columnName) {
                            if ($row[$index] != $columnName) {
                                $error_data = [
                                    'log_id' => $log_id,
                                    'uploadedId' => $uploadedId,
                                    'file_name' => "Error in Line Number " . $i,
                                    'error' => 'Header Column Not Match2'
                                ];
                                DB::table('admin_upload_error_log')->insert($error_data);
                                $i++;
                                break;
                            }
                        }
                        $i++;
                        continue;
                    } else {
                        $error_data_1 = array(
                            'log_id' => $log_id,
                            'uploadedId' => $uploadedId,
                            'file_name' => "Error in the Line Number " . $i,
                            'error' => 'Header Column Not Matchs'
                        );
                        $insert_id = DB::table('admin_upload_error_log')->insert($error_data_1);
                        $i++;
                        break;
                    }
                }
                if ($key === 0) {
                    continue;
                }
                $cond_error_data = [];
                /**Mandatory Field Check */
                if ($row['0'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Reward SKU ID is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['1'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Reward Qty is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['2'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Type is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['3'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Discount Value Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['4'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Category Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['5'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Marketing is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['6'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Funding Vendor is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['7'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Invoiced ? is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['8'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Order is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['9'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Member is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                if ($row['10'] == '') {
                    $cond_error_data = array(
                        'log_id' => $log_id,
                        'uploadedId' => $uploadedId,
                        'file_name' => "Error in the Line Number " . $i,
                        'error' => 'Redemption Limit - Qty Per Campaign is Missing'
                    );
                    $insert_id = DB::table('admin_upload_error_log')->insert($cond_error_data);
                    $i++;
                    continue;
                }
                    if($row[6] == 'TRUE'){
                        $invoice = 1;
                    }else{
                        $invoice = 0;
                    }
                    $insert_data_array[] = array(
                        'skuId' => $row[0],
                        'rewardQty' => $row[1],
                        'discountType' => $row[2],
                        'discountValue' =>$row[3],
                        'fundingCategory' => $row[4],
                        'fundingMarket' => $row[5],
                        'fundtionVendor' => $row[6],
                        'isInvoiced' => $invoice,
                        'redemptionLimitPerOrder' => $row[8],
                        'redemptionLimitPerMember' => $row[9],
                        'redemptionLimitPerCampaign' => $row[10],
                        'uploadedId' => $uploadedId,
                        'uploadedBy' => $user_id,
                    );
                $i++;
            }
            $updateCount = array(
                'totalSku' => count($insert_data_array)
            );
            DB::table('admin_upload_promotion')->where('id', $uploadedId)->update($updateCount);
            if (count($insert_data_array) > 0) {
                DB::table('admin_preview_cart_group')->insert($insert_data_array);
            }
        } catch (\Exception $ex) {
            dd($ex);
            report($ex);
        }
    }

    /* Single and Combo Promo for Edit upload */

    public function PromotionEdit(Request $request)
    {

        try {
            $id = decryptId($request->promotionid);
            $promotion = $this->promotion->GetPromotion($id);
            $business_type = $this->business_type->BusinessTypeList();
            $region = $this->region->RegionList();
            $selected_business = explode(',', $promotion->business_type_id);
            $selected_theme = explode(',', $promotion->theme_id);
            $selected_region = explode(',', $promotion->region_id);

            $data = array(
                'promotion_details' => $promotion,
                'business_type_details' =>  $business_type,
                'region_details' => $region,
                'selected_business' => $selected_business,
                'selected_theme' => $selected_theme,
                'selected_region' => $selected_region,


            );

            return view('admin.Upload_promotion_details_edit', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function PromotionUpdate(Request $request)
    {

        try {
            $id = decryptId($request->promotionid);

            $region = arrayDecrypt($request->region);
            $business_type = arrayDecrypt($request->business_type);
            $theme = arrayDecrypt($request->theme);


            $update_data = array(
                'region_id' => implode(',',   $region),
                'business_type_id' => implode(',', $business_type),
                'theme_id' => implode(',',  $theme),
                'promo_details_status' => 1,

            );
            $promotion_details =   $this->promotion->UpdatePromotion($id, $update_data);

            Session::flash('success', 'Promotion updated successfully!');
            return redirect(admin_url('upload_promotion'));
        } catch (Exception $ex) {
            dd($ex);
            return "Error";
        }
    }
    /* Single Promo and Combo for Promotion view*/
    public function PromotionPreview(Request $request)
    {
        try {
            $id = decryptId($request->promotionid);
            $promotion = $this->promotion->GetPromotion($id);
            // dd( $id ,$promotion->created_at);
            $xlsxFilePath = $promotion->file_path;
            if (!file_exists($xlsxFilePath)) {
                dd('File Not Found');
            } else {
                $data = $this->adminPreview->GetPreviewDetails($id);
                return view('admin.Upload_promotion_preview', [
                    'data' => $data, 'promotion' => $promotion
                ]);
            }
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function PromotionPreviewCombo(Request $request)
    {
        try {
            $id = decryptId($request->promotionid);
            $promotion = $this->promotion->GetPromotion($id);
            // dd( $id ,$promotion->created_at);
            $xlsxFilePath = $promotion->file_path;
            if (!file_exists($xlsxFilePath)) {
                dd('File Not Found');
            } else {
                $data = $this->adminPreviewCombo->GetPreviewDetails($id);
                return view('admin.Upload_promotion_preview_combo', [
                    'data' => $data, 'promotion' => $promotion
                ]);
            }
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }



    public function PromotionPreviewCartLevel(Request $request)
    {
        try {
            $id = decryptId($request->promotionid);
            $promotion = $this->promotion->GetPromotion($id);

            // dd( $id ,$promotion->created_at);
            $xlsxFilePath = $promotion->file_path;
            if (!file_exists($xlsxFilePath)) {
                dd('File Not Found');
            } else {
                $data = $this->adminPreviewCartlevel->GetPreviewDetails($id);
                return view('admin.Upload_promotion_preview_cart_level', [
                    'data' => $data, 'promotion' => $promotion
                ]);
            }
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function PromotionPreviewCartFree(Request $request)
    {
        try {
            $id = decryptId($request->promotionid);
            $promotion = $this->promotion->GetPromotion($id);
            $xlsxFilePath = $promotion->file_path;
            if (!file_exists($xlsxFilePath)) {
                dd('File Not Found');
            } else {
                $data = $this->adminPreviewCartfree->GetPreviewDetails($id);
                return view('admin.Upload_promotion_preview_cart_free', [
                    'data' => $data, 'promotion' => $promotion
                ]);
            }
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function PromotionPreviewCartGroup(Request $request)
    {
        try {
            $id = decryptId($request->promotionid);
            $promotion = $this->promotion->GetPromotion($id);
            $xlsxFilePath = $promotion->file_path;
            if (!file_exists($xlsxFilePath)) {
                dd('File Not Found');
            } else {
                $data = $this->adminPreviewCartgroup->GetPreviewDetails($id);
                return view('admin.Upload_promotion_preview_cart_group', [
                    'data' => $data, 'promotion' => $promotion
                ]);
            }
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    /* Single Promo and Combo for Promotion view*/

    /* Single Promo and Combo for Download */

    public function Download_dispatch(Request $request)
    {
        $id = decryptId($request->promotionid);
        $promotion = $this->promotion->GetPromotion($id);
        $xlsxFilePath = $promotion->file_path;
        $xlsxFileName = $promotion->file_orgname;
        $original = $promotion->file_name;
        if($request->getHost() == 'localhost'){
            $project = '/promosite/';
        }else{
            $project = '/';
        }
        $path = ('http://' . $request->getHost() . $project .'public/uploads/promotion/' . $original);
        return response()->json(['fileContent' => $path, 'filename' => $xlsxFileName]);
    }
    public function Download_dispatch_old(Request $request)
    {
        $id = decryptId($request->promotionid);
        $categoryValue = json_decode($request->input('category_values'), true);
        $promotion = $this->promotion->GetPromotion($id);
        $xlsxFilePath = $promotion->file_path;
        $promoType = 1;
        if (!file_exists($xlsxFilePath)) {
            dd('File Not Found');
        } else {
            // $data = Excel::toArray(new ExcelImport(), $xlsxFilePath);
            $data =  $this->adminPreview->GetPreviewDetails($id);
            $downloadArray = [];
            foreach ($data as $index => $listData) {
                $yesListArray = explode(", ", $listData['cities_yes_lists']);
                $downloadArray[] = [
                    'sr_no' => $listData['sr_no'],
                    'manufacturer_suppler_name' => $listData['manufacturer_name'],
                    'code' => $listData['code'],
                    'product_description' => $listData['description'],
                    'mrp' => $listData['mrp'],
                    'start_date_dd_mmm_yy' => $listData['start_date'],
                    'end_date_dd_mmm_yy' => $listData['endDate'],
                    'offer_details100_vendor_funded' => $listData['offerDetails'],
                    'redemption_limit_qty_per_campaign' => $listData['redemptionLimit'],
                    'selections_of_cities' => $listData['citiesSelection'],
                    'pan_india' => ($listData['isPanIndia'] == 1 ? 'Yes' : 'No'),
                    'south' => (in_array('South', $yesListArray) ? 'Yes' : 'No'),
                    'north' => (in_array('North', $yesListArray) ? 'Yes' : 'No'),
                    'east' => (in_array('East', $yesListArray) ? 'Yes' : 'No'),
                    'west' => (in_array('West', $yesListArray) ? 'Yes' : 'No'),
                    'central' => (in_array('Central', $yesListArray) ? 'Yes' : 'No'),
                    'andhra_pradesh' => (in_array('ANDHRA PRADESH', $yesListArray) ? 'Yes' : 'No'),
                    'telangana' => (in_array('TELANGANA', $yesListArray) ? 'Yes' : 'No'),
                    'assam' => (in_array('ASSAM', $yesListArray) ? 'Yes' : 'No'),
                    'bihar' => (in_array('BIHAR', $yesListArray) ? 'Yes' : 'No'),
                    'chhattisgarh' => (in_array('CHHATTISGARH', $yesListArray) ? 'Yes' : 'No'),
                    'gujarat' => (in_array('GUJARAT', $yesListArray) ? 'Yes' : 'No'),
                    'delhi_ncr' => (in_array('DELHI-NCR', $yesListArray) ? 'Yes' : 'No'),
                    'jharkhand' => (in_array('JHARKHAND', $yesListArray) ? 'Yes' : 'No'),
                    'karnataka' => (in_array('KARNATAKA', $yesListArray) ? 'Yes' : 'No'),
                    'kerala' => (in_array('KERALA', $yesListArray) ? 'Yes' : 'No'),
                    'madhya_pradesh' => (in_array('MADHYA PRADESH', $yesListArray) ? 'Yes' : 'No'),
                    'maharashtra_mumbai' => (in_array('MAHARASHTRA - Mumbai', $yesListArray) ? 'Yes' : 'No'),
                    'maharashtra_pune' => (in_array('MAHARASHTRA - Pune', $yesListArray) ? 'Yes' : 'No'),
                    'orissa' => (in_array('ORISSA', $yesListArray) ? 'Yes' : 'No'),
                    'punjab' => (in_array('PUNJAB', $yesListArray) ? 'Yes' : 'No'),
                    'rajasthan' => (in_array('RAJASTHAN', $yesListArray) ? 'Yes' : 'No'),
                    'tamil_nadu' => (in_array('TAMIL NADU', $yesListArray) ? 'Yes' : 'No'),
                    'uttar_pradesh' => (in_array('UTTAR PRADESH', $yesListArray) ? 'Yes' : 'No'),
                    'west_bengal' => (in_array('WEST BENGAL', $yesListArray) ? 'Yes' : 'No'),
                    'department' => $listData['department'],
                    'business_model' => $listData['businessModel'],
                    'category_value' => $categoryValue[$index],
                ];
            }
            // $data[0][0]['categoryValue'] = $categoryValue[0];
        }
        return \Excel::download(new PreviewExport($downloadArray, $promoType), 'Preview_Report.xlsx');
    }

    public function Download_dispatch_combo_old(Request $request)
    {
        $id = decryptId($request->promotionid);
        $categoryValue = json_decode($request->input('category_values'), true);
        $promotion = $this->promotion->GetPromotion($id);
        $xlsxFilePath = $promotion->file_path;
        $promoType = 2;
        if (!file_exists($xlsxFilePath)) {
            dd('File Not Found');
        } else {
            // $data = Excel::toArray(new ExcelImport(), $xlsxFilePath);
            $data =  $this->adminPreviewCombo->GetPreviewDetails($id);
            $downloadArray = [];
            foreach ($data as $index => $listData) {
                $yesListArray = explode(", ", $listData['cities_yes_lists']);
                $downloadArray[] = [
                    'sr_no' => $listData['sr_no'],
                    'manufacturer_suppler_name' => $listData['manufacturer_name'],
                    'combo_code' => $listData['combo_code'],
                    'combo_code_name' => $listData['combo_code_name'],
                    'code' => $listData['code'],
                    'product_description' => $listData['description'],
                    'mrp' => $listData['mrp'],
                    'start_date_dd_mmm_yy' => $listData['start_date'],
                    'end_date_dd_mmm_yy' => $listData['endDate'],
                    'offer_details100_vendor_funded' => $listData['offerDetails'],
                    'redemption_limit_qty_per_campaign' => $listData['redemptionLimit'],
                    'selections_of_cities' => $listData['citiesSelection'],
                    'pan_india' => ($listData['isPanIndia'] == 1 ? 'Yes' : 'No'),
                    'south' => (in_array('South', $yesListArray) ? 'Yes' : 'No'),
                    'north' => (in_array('North', $yesListArray) ? 'Yes' : 'No'),
                    'east' => (in_array('East', $yesListArray) ? 'Yes' : 'No'),
                    'west' => (in_array('West', $yesListArray) ? 'Yes' : 'No'),
                    'central' => (in_array('Central', $yesListArray) ? 'Yes' : 'No'),
                    'andhra_pradesh' => (in_array('ANDHRA PRADESH', $yesListArray) ? 'Yes' : 'No'),
                    'telangana' => (in_array('TELANGANA', $yesListArray) ? 'Yes' : 'No'),
                    'assam' => (in_array('ASSAM', $yesListArray) ? 'Yes' : 'No'),
                    'bihar' => (in_array('BIHAR', $yesListArray) ? 'Yes' : 'No'),
                    'chhattisgarh' => (in_array('CHHATTISGARH', $yesListArray) ? 'Yes' : 'No'),
                    'gujarat' => (in_array('GUJARAT', $yesListArray) ? 'Yes' : 'No'),
                    'delhi_ncr' => (in_array('DELHI-NCR', $yesListArray) ? 'Yes' : 'No'),
                    'jharkhand' => (in_array('JHARKHAND', $yesListArray) ? 'Yes' : 'No'),
                    'karnataka' => (in_array('KARNATAKA', $yesListArray) ? 'Yes' : 'No'),
                    'kerala' => (in_array('KERALA', $yesListArray) ? 'Yes' : 'No'),
                    'madhya_pradesh' => (in_array('MADHYA PRADESH', $yesListArray) ? 'Yes' : 'No'),
                    'maharashtra_mumbai' => (in_array('MAHARASHTRA - Mumbai', $yesListArray) ? 'Yes' : 'No'),
                    'maharashtra_pune' => (in_array('MAHARASHTRA - Pune', $yesListArray) ? 'Yes' : 'No'),
                    'orissa' => (in_array('ORISSA', $yesListArray) ? 'Yes' : 'No'),
                    'punjab' => (in_array('PUNJAB', $yesListArray) ? 'Yes' : 'No'),
                    'rajasthan' => (in_array('RAJASTHAN', $yesListArray) ? 'Yes' : 'No'),
                    'tamil_nadu' => (in_array('TAMIL NADU', $yesListArray) ? 'Yes' : 'No'),
                    'uttar_pradesh' => (in_array('UTTAR PRADESH', $yesListArray) ? 'Yes' : 'No'),
                    'west_bengal' => (in_array('WEST BENGAL', $yesListArray) ? 'Yes' : 'No'),
                    'department' => $listData['department'],
                    'business_model' => $listData['businessModel'],
                    'category_value' => $categoryValue[$index],
                ];
            }
            // $data[0][0]['categoryValue'] = $categoryValue[0];
        }
        return \Excel::download(new PreviewExport($downloadArray, $promoType), 'Preview_Report_combo.xlsx');
    }
    /* Single Promo and Combo for Download */

    /** Single Promo and Combo for Submit */

    public function create(Request $request)
    {

        $id = decryptId($request->promotionid);
        $categoryValue = $request->input('category_values');
        $indexValue = $request->input('indexId');
        // dd($categoryValue,$indexValue);
        $promotion = $this->promotion->GetPromotion($id);
        $data = $this->adminPreview->GetPreviewDetails($id);
        $insert_data = [];
        $i = 0;
        $discountType = '';
        // dd($data[0]);
        foreach ($data as $listData) {

            $user = $this->user->GetUser($listData['uploadedBy']);
            /**Second level validation */

            if ($listData['discountType'] == 'Percent' || $listData['discountType'] == 'Percentage') {
                $vendorFund = (100 - $user['fundingCategory']);
            } else if ($listData['discountType'] == 'Flat') {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            } else {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            }
            // $vendorFund = ($totalPercentage - );
            /**Second level validation */
            $approveRejectStatus = 0;
            $rejectComments = '';
            if ($vendorFund < 0) {
                $approveRejectStatus = 2;
                $rejectComments = 'Funding Value Should not be Negative';
            }
            if (strpos($vendorFund, '.') != false) {
                // Does not have 2 decimal places
                $vendorFund = round($vendorFund, 2);
            } else {
                $vendorFund - $vendorFund;
            }
            if ($listData['approveRejectStatus'] == 2) {
                $approveRejectStatus = 2;
                $rejectComments = $listData['rejectComments'];
            }
            $insert_data = [
                'category_value' => $categoryValue[$i],
                'fundtionVendor' => $vendorFund,
                'process_type' => 2,
                'fundingMarket' => 0,
                // 'fundingCategory' => $user['fundingCategory'],
                // 'redemptionLimitPerOrder' => $user['redemptionPerOrder'],
                // 'redemptionLimitPerMember' => $user['redemptionPerMember'],
                'approveRejectStatus' => $approveRejectStatus,
                'rejectComments' => $rejectComments

            ];
            $updateArray = $this->adminPreview->UpdatePromotionPreview($indexValue[$i], $id, $insert_data);
            $i++;
        }
        $updateProcessType = array(
            'process_type' => 2
        );
        $this->promotion->UpdatePromotion($id, $updateProcessType);

        Session::flash('success', 'Successfully Promotype uploaded !');
        // return redirect(admin_url('upload_promotion/preview/' . encryptId($id)));
        return redirect(admin_url('preview/' . encryptId($id)));
    }

    public function create_combo(Request $request)
    {

        $id = decryptId($request->promotionid);
        $categoryValue = $request->input('category_values');
        $indexValue = $request->input('indexId');
        // dd($categoryValue,$indexValue);
        $promotion = $this->promotion->GetPromotion($id);
        $data = $this->adminPreviewCombo->GetPreviewDetails($id);
        $insert_data = [];
        $i = 0;
        $discountType = '';
        // dd($data[0]);
        foreach ($data as $listData) {

            $user = $this->user->GetUser($listData['uploadedBy']);
            /**Second level validation */
            if ($listData['discountType'] == 'Percent' || $listData['discountType'] == 'Percentage') {
                $vendorFund = (100 - $user['fundingCategory']);
            } else if ($listData['discountType'] == 'Flat') {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            } else {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            }

            /**Second level validation */
            $approveRejectStatus = 0;
            $rejectComments = '';
            if ($vendorFund < 0) {
                $approveRejectStatus = 2;
                $rejectComments = 'Funding Value Should not be Negative';
            }
            if (strpos($vendorFund, '.') != false) {
                // Does not have 2 decimal places
                $vendorFund = round($vendorFund, 2);
            } else {
                $vendorFund - $vendorFund;
            }
            if ($listData['approveRejectStatus'] == 2) {
                $approveRejectStatus = 2;
                $rejectComments = $listData['rejectComments'];
            }
            $insert_data = [
                'category_value' => $categoryValue[$i],
                'fundtionVendor' => $vendorFund,
                'process_type' => 2,
                'fundingMarket' => 0,
                // 'fundingCategory' => $user['fundingCategory'],
                // 'redemptionLimitPerOrder' => $user['redemptionPerOrder'],
                // 'redemptionLimitPerMember' => $user['redemptionPerMember'],
                'approveRejectStatus' => $approveRejectStatus,
                'rejectComments' => $rejectComments

            ];
            $updateArray = $this->adminPreviewCombo->UpdatePromotionPreview($indexValue[$i], $id, $insert_data);
            $i++;
        }
        $updateProcessType = array(
            'process_type' => 2
        );
        $this->promotion->UpdatePromotion($id, $updateProcessType);

        Session::flash('success', 'Successfully Promotype uploaded !');
        // return redirect(admin_url('upload_promotion/preview_combo/' . encryptId($id)));
        return redirect(admin_url('preview_combo/' . encryptId($id)));
    }

    public function create_level(Request $request)
    {
        $id = decryptId($request->promotionid);
        $categoryValue = $request->input('category_values');
        $indexValue = $request->input('indexId');
        // dd($categoryValue,$indexValue);
        $promotion = $this->promotion->GetPromotion($id);
        $data = $this->adminPreviewCartlevel->GetPreviewDetails($id);
        $insert_data = [];
        $i = 0;
        $discountType = '';
        // dd($data[0]);
        // dd($id,$categoryValue,$indexValue,$promotion,$data);

        foreach ($data as $listData) {
            $user = $this->user->GetUser($listData['uploadedBy']);
            /**Second level validation */
            if ($listData['discountType'] == 'Percent' || $listData['discountType'] == 'Percentage') {
                $vendorFund = (100 - $user['fundingCategory']);
            } else if ($listData['discountType'] == 'Flat') {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            } else {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            }

            /**Second level validation */
            $approveRejectStatus = 0;
            $rejectComments = '';
            if ($vendorFund < 0) {
                $approveRejectStatus = 2;
                $rejectComments = 'Funding Value Should not be Negative';
            }
            if (strpos($vendorFund, '.') != false) {
                // Does not have 2 decimal places
                $vendorFund = round($vendorFund, 2);
            } else {
                $vendorFund - $vendorFund;
            }
            if ($listData['approveRejectStatus'] == 2) {
                $approveRejectStatus = 2;
                $rejectComments = $listData['rejectComments'];
            }
            $insert_data = [
                'category_value' => $categoryValue[$i],
                'fundtionVendor' => $vendorFund,
                'process_type' => 2,
                'fundingMarket' => 0,
                // 'fundingCategory' => $user['fundingCategory'],
                // 'redemptionLimitPerOrder' => $user['redemptionPerOrder'],
                // 'redemptionLimitPerMember' => $user['redemptionPerMember'],
                'approveRejectStatus' => $approveRejectStatus,
                'rejectComments' => $rejectComments

            ];
            $updateArray = $this->adminPreviewCartlevel->UpdatePromotionPreview($indexValue[$i], $id, $insert_data);
            $i++;
        }
        $updateProcessType = array(
            'process_type' => 2
        );
        $this->promotion->UpdatePromotion($id, $updateProcessType);

        Session::flash('success', 'Successfully Promotype uploaded !');
        // return redirect(admin_url('upload_promotion/preview_combo/' . encryptId($id)));
        return redirect(admin_url('preview_level/' . encryptId($id)));
    }


    public function create_free(Request $request)
    {
        $id = decryptId($request->promotionid);
        $categoryValue = $request->input('category_values');
        $indexValue = $request->input('indexId');
        // dd($categoryValue,$indexValue);
        $promotion = $this->promotion->GetPromotion($id);
        $data = $this->adminPreviewCartfree->GetPreviewDetails($id);
        $insert_data = [];
        $i = 0;
        $discountType = '';
        // dd($data[0]);
        foreach ($data as $listData) {

            $user = $this->user->GetUser($listData['uploadedBy']);
            /**Second level validation */
            if ($listData['discountType'] == 'Percent' || $listData['discountType'] == 'Percentage') {
                $vendorFund = (100 - $user['fundingCategory']);
            } else if ($listData['discountType'] == 'Flat') {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            } else {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            }

            /**Second level validation */
            $approveRejectStatus = 0;
            $rejectComments = '';
            if ($vendorFund < 0) {
                $approveRejectStatus = 2;
                $rejectComments = 'Funding Value Should not be Negative';
            }
            if (strpos($vendorFund, '.') != false) {
                // Does not have 2 decimal places
                $vendorFund = round($vendorFund, 2);
            } else {
                $vendorFund - $vendorFund;
            }
            if ($listData['approveRejectStatus'] == 2) {
                $approveRejectStatus = 2;
                $rejectComments = $listData['rejectComments'];
            }
            $insert_data = [
                'category_value' => $categoryValue[$i],
                'fundtionVendor' => $vendorFund,
                'process_type' => 2,
                'fundingMarket' => 0,
                // 'fundingCategory' => $user['fundingCategory'],
                // 'redemptionLimitPerOrder' => $user['redemptionPerOrder'],
                // 'redemptionLimitPerMember' => $user['redemptionPerMember'],
                'approveRejectStatus' => $approveRejectStatus,
                'rejectComments' => $rejectComments

            ];
            $updateArray = $this->adminPreviewCartfree->UpdatePromotionPreview($indexValue[$i], $id, $insert_data);
            $i++;
        }
        $updateProcessType = array(
            'process_type' => 2
        );
        $this->promotion->UpdatePromotion($id, $updateProcessType);

        Session::flash('success', 'Successfully Promotype uploaded !');
        // return redirect(admin_url('upload_promotion/preview_combo/' . encryptId($id)));
        return redirect(admin_url('preview_free/' . encryptId($id)));
    }
    public function create_group(Request $request)
    {
        $id = decryptId($request->promotionid);
        $categoryValue = $request->input('category_values');
        $indexValue = $request->input('indexId');
        // dd($categoryValue,$indexValue);
        $promotion = $this->promotion->GetPromotion($id);
        $data = $this->adminPreviewCartfree->GetPreviewDetails($id);
        $insert_data = [];
        $i = 0;
        $discountType = '';
        // dd($data[0]);
        foreach ($data as $listData) {

            $user = $this->user->GetUser($listData['uploadedBy']);
            /**Second level validation */
            if ($listData['discountType'] == 'Percent' || $listData['discountType'] == 'Percentage') {
                $vendorFund = (100 - $user['fundingCategory']);
            } else if ($listData['discountType'] == 'Flat') {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            } else {
                $vendorFund = ($listData['discountValue'] - $user['fundingCategory']);
            }

            /**Second level validation */
            $approveRejectStatus = 0;
            $rejectComments = '';
            if ($vendorFund < 0) {
                $approveRejectStatus = 2;
                $rejectComments = 'Funding Value Should not be Negative';
            }
            if (strpos($vendorFund, '.') != false) {
                // Does not have 2 decimal places
                $vendorFund = round($vendorFund, 2);
            } else {
                $vendorFund - $vendorFund;
            }
            if ($listData['approveRejectStatus'] == 2) {
                $approveRejectStatus = 2;
                $rejectComments = $listData['rejectComments'];
            }
            $insert_data = [
                'category_value' => $categoryValue[$i],
                'fundtionVendor' => $vendorFund,
                'process_type' => 2,
                'fundingMarket' => 0,
                // 'fundingCategory' => $user['fundingCategory'],
                // 'redemptionLimitPerOrder' => $user['redemptionPerOrder'],
                // 'redemptionLimitPerMember' => $user['redemptionPerMember'],
                'approveRejectStatus' => $approveRejectStatus,
                'rejectComments' => $rejectComments

            ];
            $updateArray = $this->adminPreviewCartgroup->UpdatePromotionPreview($indexValue[$i], $id, $insert_data);
            $i++;
        }
        $updateProcessType = array(
            'process_type' => 2
        );
        $this->promotion->UpdatePromotion($id, $updateProcessType);

        Session::flash('success', 'Successfully Promotype uploaded !');
        // return redirect(admin_url('upload_promotion/preview_combo/' . encryptId($id)));
        return redirect(admin_url('preview_group/' . encryptId($id)));
    }
    /** Single Promo and Combo for Submit */

    public function PromotionEditPreview(Request $request)
    {
        try {
            $business_type = $this->business_type->BusinessTypeList();
            $region = $this->region->RegionList();
            $data = array(
                'business_type_details' =>  $business_type,
                'region_details' => $region,
                'promoType' => $request->promo_type
            );
            return view('admin.Upload_promotion_details_edit_preview', $data);
        } catch (Exception $ex) {
            dd($ex);
            return redirect(admin_url('upload_promotion'));
        }
    }

    public function create_old(Request $request)
    {

        $rules = [
            'category_values' => 'required|array',
            'category_values.*' => 'required',
        ];

        $messages = [
            'category_values.required' => 'Please enter category value',
            'category_values.*.required' => 'Please enter category value',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $id = decryptId($request->promotionid);
        $categoryValue = $request->input('category_values');
        $promotion = $this->promotion->GetPromotion($id);
        $xlsxFilePath = $promotion->file_path;
        $data = Excel::toArray(new ExcelImport(), $xlsxFilePath);
        $insert_data = [];
        $i = 0;

        // dd($data[0]);
        foreach ($data[0] as $listData) {
            /**Second level validation */
            preg_match("/([0-9]+)/", $listData['offer_details100_vendor_funded'], $totalPercentage);
            $totalPercentage = $totalPercentage[1];

            if (str_contains($listData['offer_details100_vendor_funded'], '%')) {
                // $vendorFund = ($totalPercentage / 100) * $listData['mrp'];
                $vendorFund = 100;
            } else if (str_contains($listData['offer_details100_vendor_funded'], 'Rs')) {
                // $vendorFund = ($listData['mrp'] - $totalPercentage);
                $vendorFund = $totalPercentage;
            } else {
                // $vendorFund = $totalPercentage;
                $vendorFund = 100;
            }

            /**Second level validation */

            /* Split Yes and No Cities lists */
            $yesKeys = [];
            $noKeys = [];
            $considerRange = false;

            $yesZone = [];
            $noZone = [];
            $considerZone = false;

            foreach ($listData as $key => $value) {
                // dd($listData[$key]);
                if ($considerRange) {
                    if ($value === "Yes") {
                        $yesKeys[] = $key;
                    } elseif ($value === "No") {
                        $noKeys[] = $key;
                    }

                    if ($key === "west_bengal") {
                        $considerRange = false;
                    }
                } elseif ($key === "andhra_pradesh") {
                    $considerRange = true;
                }

                if ($considerZone) {
                    if ($value === "Yes") {
                        $yesZone[] = $key;
                    } elseif ($value === "No") {
                        $noZone[] = $key;
                    }

                    if ($key === "central") {
                        $considerZone = false;
                    }
                } elseif ($key === "south") {
                    $considerZone = true;
                }
            }
            $yesString = implode(',', $yesKeys);
            $noString = implode(',', $noKeys);

            $yesZoneString = implode(',', $yesZone);
            $noZoneString = implode(',', $noZone);


            /* Split Yes and No Cities lists */

            $startDateTime = Carbon::createFromFormat('m-d-Y H:i', $listData['start_date_dd_mmm_yy']);
            $formattedStartDate = $startDateTime->format('d-m-Y'); // Format for display
            $endDateTime = Carbon::createFromFormat('m-d-Y H:i', $listData['end_date_dd_mmm_yy']);
            $formattedEndDate = $endDateTime->format('d-m-Y'); // Format for display
            $insert_data[] = array(
                'manufacturer_name' => $listData['manufacturer_suppler_name'],
                'combo_code' => $listData['code'],
                'combo_code_name' => $listData['code'],
                'code' => $listData['code'],
                'description' => $listData['product_description'],
                'mrp' => $listData['mrp'],
                'start_date' => DBdateformat($formattedStartDate),
                'endDate' => DBdateformat($formattedEndDate),
                'offerDetails' => $listData['offer_details100_vendor_funded'],
                'redemptionLimit' => $listData['redemption_limit_qty_per_campaign'],
                'citiesSelection' => $listData['selections_of_cities'],
                'isPanIndia' => ($listData['pan_india'] === 'Yes') ? 1 : 0,
                'zone_yes_lists' => $yesZoneString,
                'zone_no_lists' => $noZoneString,
                'cities_yes_lists' => $yesString,
                'cities_no_lists' => $noString,
                'department' => $listData['department'],
                'businessModel' => $listData['business_model'],
                'category_value' => $categoryValue[$i],
                'fundtionVendor' => $vendorFund,
                'discountValue' => $totalPercentage
            );
            // DB::table('admin_preview')->insertGetId($insert_data);
            $i++;
        }
        // dd( $insert_data ,$start_date);
        if (count($insert_data) > 0) {
            try {
                if ($promotion->promo_type == 1) {
                    DB::table('admin_preview')->insert($insert_data);
                } else {
                    DB::table('admin_preview_combo')->insert($insert_data);
                }
            } catch (\Exception $e) {
                dd($e);
            }
        } else {
            dd($insert_data);
        }
        Session::flash('success', 'Successfully Promotype uploaded !');
        return redirect(admin_url('upload_promotion/preview/' . encryptId($id)));

        // Perform any other actions needed after saving the data
    }

    public function create_recent(Request $request)
    {

        /* $rules = [
            'category_values' => 'required|array',
            'category_values.*' => 'required',
        ];

        $messages = [
            'category_values.required' => 'Please enter category value',
            'category_values.*.required' => 'Please enter category value',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }*/

        $id = decryptId($request->promotionid);
        $categoryValue = $request->input('category_values');
        $indexValue = $request->input('indexId');
        // dd($categoryValue,$indexValue);
        $promotion = $this->promotion->GetPromotion($id);
        $data = $this->adminPreview->GetPreviewDetails($id);
        $insert_data = [];
        $i = 0;
        $discountType = '';
        // dd($data[0]);
        foreach ($data as $listData) {

            $user = $this->user->GetUser($listData['uploadedBy']);
            /**Second level validation */
            preg_match("/([0-9]+)/", $listData['offerDetails'], $totalPercentage);
            $totalPercentage = $totalPercentage[1];

            if (str_contains($listData['offerDetails'], '%')) {
                // $vendorFund = ($totalPercentage / 100) * $listData['mrp'];
                $vendorFund = (100 - $user['fundingCategory']);
                $discountType = 'Percent';
            } else if (str_contains($listData['offerDetails'], 'Rs')) {
                // $vendorFund = ($listData['mrp'] - $totalPercentage);
                $vendorFund = ($totalPercentage - $user['fundingCategory']);
                $discountType = 'Flat';
            } else {
                // $vendorFund = $totalPercentage;
                $vendorFund = ($totalPercentage - $user['fundingCategory']);
                $discountType = 'Fixed';
            }
            // $vendorFund = ($totalPercentage - );
            /**Second level validation */
            $approveRejectStatus = 0;
            $rejectComments = '';
            if ($vendorFund < 0) {
                $approveRejectStatus = 2;
                $rejectComments = 'Funding Value Should not be Negative';
            }
            if ($listData['approveRejectStatus'] == 2) {
                $approveRejectStatus = 2;
                $rejectComments = $listData['rejectComments'];
            }
            $insert_data = [
                'discountType' => $discountType,
                'category_value' => $categoryValue[$i],
                'fundtionVendor' => $vendorFund,
                'discountValue' => $totalPercentage,
                'process_type' => 2,
                'fundingMarket' => 0,
                'fundingCategory' => $user['fundingCategory'],
                'redemptionLimitPerOrder' => $user['redemptionPerOrder'],
                'redemptionLimitPerMember' => $user['redemptionPerMember'],
                'approveRejectStatus' => $approveRejectStatus,
                'rejectComments' => $rejectComments

            ];
            $updateArray = $this->adminPreview->UpdatePromotionPreview($indexValue[$i], $id, $insert_data);
            $i++;
        }
        $updateProcessType = array(
            'process_type' => 2
        );
        $this->promotion->UpdatePromotion($id, $updateProcessType);

        Session::flash('success', 'Successfully Promotype uploaded !');
        // return redirect(admin_url('upload_promotion/preview/' . encryptId($id)));
        return redirect(admin_url('preview/' . encryptId($id)));
    }
}
