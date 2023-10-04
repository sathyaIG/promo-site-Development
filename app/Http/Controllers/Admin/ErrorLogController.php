<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

use Auth;
use DataTables;
use DB;
use Exception;
use Session;
use Str;
use Log;
use Storage;
use SimpleXLSX;

use App\Models\UploadLog;
use App\Models\UploadUserLog;
use App\Models\UploadUserLogError;
use App\Models\UploadLogError;
use App\Models\User;

use App\Exports\DownloadsingleExport;


use App\Libraries\odoo\OdooSales;



class ErrorLogController extends Controller
{
    protected $UploadLog;
    protected $UploadLogError;
    protected $UploadUserLog;
    protected $UploadUserLogError;
    protected $User;


    public function __construct(User $User, UploadLog $UploadLog, UploadLogError $UploadLogError, UploadUserLog $UploadUserLog, UploadUserLogError $UploadUserLogError)
    {
        $this->UploadLog = $UploadLog;
        $this->UploadLogError = $UploadLogError;
        $this->UploadUserLog = $UploadUserLog;
        $this->UploadUserLogError = $UploadUserLogError;
        $this->User = $User;
    }


    public function index(Request $request)
    {
        if (Auth::check()) {
            if ($request->ajax()) {
                try {
                    $user_id = auth()->user()->id;
                    if (Auth::user()->role == '5') {
                        $data = $this->UploadLog->getById($user_id);
                    } elseif (Auth::user()->role == '1') {
                        $data = $this->UploadLog->List();
                    } else {
                        $getManufacturerIds = string_to_array(Auth::user()->manufacturerLists);
                        $data = $this->UploadLog->getPromotionBasedOnCondition($getManufacturerIds);
                    }

                    $datatables = Datatables::of($data)
                        ->addIndexColumn()


                        ->addColumn('error_count', function ($row) {

                            $logs = $this->UploadLogError->UploadLogErrorList($row->id);

                            $log_count =  $logs->count();

                            return $log_count;
                        })

                        ->addColumn('upload_by', function ($row) {

                            $user = $this->User->GetUser($row->created_by);
                            $name = '';
                            if (isset($user)) {
                                $name = $user->name;
                            }

                            return $name;
                        })

                        ->addColumn('date_time', function ($row) {

                            return Displaydateformat($row->created_at);
                        })

                        ->addColumn('status', function ($row) {

                            if ($row->extract_status == 0) {
                                $status = "<span style='color:red;' >Yet to Start<span>";
                            } else if ($row->extract_status == 1) {
                                $status = "<span style='color:primary;' >In progress<span>";
                            } else if ($row->extract_status == 2) {
                                $status = "<span style='color:green;' >Completed<span>";
                            }

                            return $status;
                        })


                        ->addColumn('action', function ($row) {
                            $btn = '';
                            $btn = '<a href="' . admin_url('Upload_view/' . encryptId($row->id)) . '"   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                            return $btn;
                        })
                        ->rawColumns(['action', 'date_time', 'error_count', 'upload_by', 'status'])
                        ->make(true);
                    return $datatables;
                } catch (Exception $ex) {
                    report($ex);
                    dd($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }
        $upload = $this->UploadLog->List();
        $data = array(
            'upload_details' => $upload,
        );
        return view('admin.Uploadlog_details_list', $data);
    }

    public function UploadView(Request $request)
    {

        if (Auth::check()) {

            if ($request->ajax()) {
                try {
                    $uploadid = decryptId($request->uploadid);
                    $data = $this->UploadLogError->UploadLogErrorListPromotion($uploadid);


                    $datatables = Datatables::of($data)
                        ->addIndexColumn()

                        ->rawColumns([])
                        ->make(true);
                    return $datatables;
                } catch (Exception $ex) {
                    report($ex);
                    dd($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }

        $uploadid = decryptId($request->uploadid);
        $upload = $data = $this->UploadLogError->UploadLogErrorListPromotion($uploadid);
        $data = array(
            'upload_details' => $upload,
            'upload_id' => $uploadid,
        );
        return view('admin.Uploadlog_error_details_list', $data);
    }

    public function DownloadFile(Request $request)
    {

        $upload_id = decryptId($request->upload_id);
        $logDetails = DB::table('admin_upload_log')->select('*')->where('id', $upload_id)->first();
        $xlsx = SimpleXLSX::parseFile($logDetails->file_path);

        try {
            $i = 1;
            foreach ($xlsx->rows() as $key => $row) {
                if($i > 1){
                    $geterror =  DB::table('admin_upload_error_log')->where('log_id', $upload_id)->where('lineNumber', $i)->first();
                    $exportedArray[] = [
                        'sr_no' => $row['0'],
                        'manufacturer_name' => $row['1'],
                        'code' => $row['2'],
                        'description' => $row['3'],
                        'mrp' => $row['4'],
                        'start_date' => date('m-d-Y', strtotime($row['5'])),
                        'end_date' => date('m-d-Y', strtotime($row['6'])),
                        'offer_details' => $row['7'],
                        'redemption_campaign' => $row['8'],
                        'selection_cities' => $row['9'],
                        'pan_india' => ($row['10'] == 0 ? 'False' : 'True'),
                        'zone_yes_list' => $row['11'],
                        'zone_no_list' => $row['12'],
                        'cities_yes_lists' => $row['13'],
                        'cities_no_lists' => $row['14'],
                        'department' => $row['35'],
                        'business_model' => $row['36'],
                        'error_description' => isset($geterror->error) ? $geterror->error : '',
                    ];
                }
                $i++;
            }
            try {
                return \Excel::download(new DownloadsingleExport($exportedArray, 1), 'Download_Report.xlsx');
            } catch (Exception $ex) {
                dd($ex->getMessage());
            }
        } catch (Exception $ex) {
            dd($ex->getMessage());
            report($ex->getMessage());
        }

    }
    public function upload_user_logs(Request $request)
    {
        if (Auth::check()) {
            if ($request->ajax()) {
                try {
                    $user_id = auth()->user()->id;
                    if (Auth::user()->role == '1') {
                        $data = $this->UploadUserLog->List();
                    }

                    $datatables = Datatables::of($data)
                        ->addIndexColumn()


                        ->addColumn('error_count', function ($row) {

                            $logs = $this->UploadUserLogError->UploadLogErrorList($row->id);

                            $log_count =  $logs->count();

                            return $log_count;
                        })

                        ->addColumn('upload_by', function ($row) {

                            $user = $this->User->GetUser($row->created_by);
                            $name = '';
                            if (isset($user)) {
                                $name = $user->name;
                            }

                            return $name;
                        })

                        ->addColumn('date_time', function ($row) {

                            return Displaydateformat($row->created_at);
                        })

                        ->addColumn('status', function ($row) {

                            if ($row->extract_status == 0) {
                                $status = "<span style='color:red;' >Yet to Start<span>";
                            } else if ($row->extract_status == 1) {
                                $status = "<span style='color:primary;' >In progress<span>";
                            } else if ($row->extract_status == 2) {
                                $status = "<span style='color:green;' >Completed<span>";
                            }

                            return $status;
                        })

                        ->addColumn('action', function ($row) {
                            $btn = '';
                            $btn = '<a href="' . admin_url('Upload_user_view/' . encryptId($row->id)) . '"   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                            return $btn;
                        })
                        ->rawColumns(['action', 'date_time', 'error_count', 'upload_by', 'status'])
                        ->make(true);
                    return $datatables;
                } catch (Exception $ex) {
                    report($ex);
                    dd($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }
        $upload = $this->UploadLog->List();
        $data = array(
            'upload_details' => $upload,
        );
        return view('admin.Uploaduserlog_details_list', $data);
    }

    public function UploadUserView(Request $request)
    {

        if (Auth::check()) {

            if ($request->ajax()) {
                try {
                    $uploadid = decryptId($request->uploadid);
                    $data = $this->UploadUserLogError->UploadLogErrorListPromotion($uploadid);
                    $datatables = Datatables::of($data)
                        ->addIndexColumn()

                        ->rawColumns([])
                        ->make(true);
                    return $datatables;
                } catch (Exception $ex) {
                    report($ex);
                    dd($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }

        $uploadid = decryptId($request->uploadid);
        $upload = $data = $this->UploadUserLogError->UploadLogErrorListPromotion($uploadid);
        $data = array(
            'upload_details' => $upload,
            'upload_id' => $uploadid,
        );
        return view('admin.Uploaduserlog_error_details_list', $data);
    }
    public function UserDownloadFile(Request $request)
    {

        $upload_id = decryptId($request->upload_id);

        $logDetails = DB::table('admin_user_upload_log')
            ->select('*')
            ->where('id', $upload_id)
            ->first();

        return Response::download($logDetails->file_path, $logDetails->file_orgname);
    }
}
