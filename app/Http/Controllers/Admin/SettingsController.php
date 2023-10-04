<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Auth;
use Session;
use Str;
use DB;
use Response;
use DataTables;
use Log;

use App\Models\SettingGeneral;


class SettingsController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth');
    }

 


    /**
     * General Settings Master
     */



    public function General_Settings_View(Request $request)
    {


        try {

            if (Auth::check()) {

                $General_Settings = SettingGeneral::first();
                $data = array(
                    'General_Settings' => $General_Settings,

                );

                return view('admin.Settings_General_view', $data);
            } else {
                report('Invalid User');
                return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
            }
        } catch (Exception $ex) {

            report($ex);
            dd($ex);
            return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
        }
    }

    public function General_Settings_Edit(Request $request)
    {

        try {

            try {
                if (Auth::check()) {


                    $General_Settings = SettingGeneral::first();


                    $data = array(
                        'General_Settings' => $General_Settings,

                    );

                    return view('admin.Settings_General_edit', $data);
                } else {
                    report('Invalid User');
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            } catch (Exception $ex) {

                report($ex);
                return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
            }
        } catch (Exception $ex) {
        }
    }

    public function General_Settings_EditSubmit(Request $request)
    {

        try {
            if (Auth::check()) {
                $id = decryptId($request->id);
                $product_data = array(
                    'upload_allowed_days' => $request->upload_days,
                    'edit_document' => $request->edit_document,
                    'end_date' => $request->end_date,
                    'start_date' => $request->start_date,
                    'submitted_till' => $request->file_submit,
                    'generated_on' => $request->generate_days,
                    'upload_dates' => $request->manualupload_days,
                    'updated_by' => Auth::user()->id,
                );
                SettingGeneral::where('id', '1')->update($product_data);

            
                $product_data['updated_by'] = Auth::user()->toArray();
            
                Session::flash('success', 'General Settings Updated successfully!');
                return redirect(admin_url('General_Settings_View'));
            } else {
                report('Invalid User');
                return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
            }
        } catch (Exception $ex) {

            Log::channel('generalSettings-info')->alert($ex);
            report($ex);
            return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
        }
    }




  
}
