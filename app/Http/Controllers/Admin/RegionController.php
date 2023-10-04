<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;


use App\Exports\UsersProfileExport;
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
use App\Models\AdminRegionMaster;
use App\Models\AdminCityMaster;
use App\Models\AdminStateMaster;




class RegionController extends Controller
{
    protected $region;
    protected $state;
    protected $city;


    public function __construct(AdminRegionMaster $region, AdminStateMaster $state, AdminCityMaster $city)
    {
        $this->region = $region;
        $this->state = $state;
        $this->city = $city;
    }

    public function index(Request $request)
    {

        if (Auth::check()) {

            if ($request->ajax()) {

                try {

                    $data = $this->region->RegionList();

                    $datatables = Datatables::of($data)
                        ->addIndexColumn()

                        ->addColumn('state', function ($row) {

                            $state = $this->state->StateName($row->state);

                            return $state->state;
                        })
                        // ->addColumn('city', function ($row) {
                        //     $city = $this->city->CityName($row->city);

                        //     return $city->city;
                        // })
                        ->addColumn('profile_status', function ($row) {

                            $text = "<span style='color:red'>In-Active<span>";
                            if ($row->status == 1) {

                                $text = "<span style='color:green;cursor:pointer' class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' >Active<span>";
                            } else if ($row->status == 0) {
                                $text = "<span style='color:red;cursor:pointer' class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '0' >In-Active<span>";
                            }

                            return $text;
                        })

                        ->addColumn('city', function ($row) {

                           $city_name = getCustomValue('admin_city_master','city',$row->city);

                            return $city_name;
                        })

                        ->addColumn('action', function ($row) {
                            $btn = '';
                            // $btn = '<a href="' . admin_url('region_management/' . encryptId($row->id)) . '"   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                            $btn .= '<a href="' . admin_url('region_management/edit/' . encryptId($row->id)) . '" class=" " title="Edit"><i class="fa fa-edit" style="color:#43a047;"></i></a> ';
                            $btn .= '<a href="javascript:void(0);"  data-id="' . encryptId($row->id) . '"  class="RegionDelete" title="Delete"><i class="fa fa-trash-alt" style="color:#d81821;"></i></a> ';

                            return $btn;
                        })
                        ->rawColumns(['action', 'state', 'profile_status'])
                        ->make(true);

                    return $datatables;
                } catch (Exception $ex) {
                    dd($ex);
                    report($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }


        $region = $this->region->RegionList();

        $data = array(
            'region_details' => $region,

        );

        return view('admin.Region_details_list', $data);
    }

    public function RegionAdd(Request $request)
    {
        $region = $this->region->RegionList();
        $city = $this->city->CityList();
        $state = $this->state->StateList();

        $data = array(
            'region_details' => $region,
            'state_details' => $state,
            'city_details' => $city


        );

        return view('admin.Region_details_add', $data);
    }

    public function RegionAddSubmit(Request $request)
    {

        try {
            $rules = [
                'region' => 'required',
                'state' => 'required',
                'city' => 'required',
            ];
            $messages = [
                'region.required' => 'Please enter Region',
                'state.required' => 'Please select State',
                'city.required' => 'Please select City',
            ];
            $validator = \Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $insert_data = array(
                'region' => $request->region,
                'status' => "1",
                'state' => decryptId($request->state),
                'city' =>  implode(',', $request->city),
                'created_by' => Auth::user()->id,
                'region_code' =>  $request->region_code,
                'region_id' =>  $request->region_id,

            );
            $region_details = $this->region->InsertRegion($insert_data);

            Session::flash('success', 'Region added successfully!');
            return redirect(admin_url('region_management'));
        } catch (Exception $ex) {
            return redirect(admin_url('region_management'));
        }
    }

    public function state_get_city(Request $request)
    {

        try {
            $id = decryptId($request->state_id);

            $get_city = AdminCityMaster::where('state_id', $id)->select('city', 'id')->get();

            return ['msg' => 'success', 'data' => $get_city];
        } catch (Exception $error) {

            return ['msg' => 'failed', 'data' => $get_city];
        }
    }



    public function RegionView(Request $request, $regionid = '')
    {
        $id = decryptId($request->regionid);
        if (Auth::check()) {

            $region = $this->region->GetRegion($id);
            $city = $this->city->StateName($region->city);
            $state = $this->state->CityName($region->state);

            $data = array(
                'region_details' => $region,
                'state_details' => $state,
                'city_details' => $city
            );
        }
        try {
            return view('admin.Region_details_view', $data);
        } catch (Exception $ex) {
            report($ex);
        }
    }
    public function RegionEdit(Request $request, $regionid = '')
    {

        try {
            $id = decryptId($request->regionid);
            $region = $this->region->GetRegion($id);
            $city = $this->city->StateCityList($region->state);
            $state = $this->state->StateList();
            $selected_city = explode(',', $region->city);


            $data = array(
                'region_details' => $region,
                'state_details' => $state,
                'city_details' => $city,
                'selected_city' => $selected_city
            );

            return view('admin.Region_details_edit', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }
    public function RegionUpdate(Request $request)
    {
        try {
            $id = decryptId($request->regionid);
            $update_data = array(
                'region' => $request->region,
                'state' => decryptId($request->state),
                'city' => implode(',', $request->city),
                'region_code' => $request->region_code,
                'region_id' => $request->region_id,
            );

            $region_details =   $this->region->UpdateRegion($id, $update_data);

            Session::flash('success', 'Region updated successfully!');
            return redirect(admin_url('region_management'));
        } catch (Exception $ex) {
            dd($ex);
            return "Error";
        }
    }
    public function RegionStatus(Request $request)
    {
        try {
            $id = decryptId($request->region_id);
            $type = $request->types;
            if ($type == 1) {
                $update_data = array(
                    'status' => "0",
                );
                $successMsg = 'Region successfully De-activated';
                $this->region->UpdateRegion($id, $update_data);
            } else {
                $update_data = array(
                    'status' => "1",
                );

                $successMsg = 'Region successfully Activated';
                $this->region->UpdateRegion($id, $update_data);
            }
            return response()->json(['status' => 'success', 'msg' => $successMsg], 200);
        } catch (Exception $ex) {
            return response()->json(['error' => '2', 'status' => 'error', 'msg' => 'Please try after some time'], 406);
        }
    }
    public function RegionDelete(Request $request)
    {
        try {
            $region_id = decryptId($request->region_id);
            $update_data = array(
                'status' => "0",
                'trash' => "YES",
            );

            $this->region->UpdateRegion($region_id, $update_data);


            Session::flash('success', 'Region deleted successfully!');
            return response()->json(['status' => 'success', 'msg' => 'Region deleted successfully'], 200);
        } catch (Exception $ex) {
            return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
        }
    }
}
