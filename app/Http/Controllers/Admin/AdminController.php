<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;
use Auth;
use Session;
use DB;
use DataTables;

use Str;
use App\Models\User;
use App\Models\Admin\Product;
use App\Models\Admin\Purity;
use App\Models\AdminUploadPromotion;
use App\Models\Admin\CategoryTLC;
use App\Models\Admin\CategoryDept;
use App\Exports\OutputExport;
use App\Models\AdminPreview;
use App\Models\AdminPreviewCombo;
use App\Models\AdminPreviewCartLevel;
use App\Models\AdminPreviewCartFree;
use Maatwebsite\Excel\Facades\Excel;
use Svg\Tag\Rect;
use Carbon\Carbon;
use App\Exports\RejectedExport;
use App\Jobs\AttachmentmailJob;
use App\Mail\AttachmentsMail;
use App\Models\AdminPreviewCartGroup;
use Illuminate\Support\Arr;
use Mail;
use SimpleXLSX;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Http;
use App\Jobs\ProcessCsvFile;



class AdminController extends Controller
{


    protected $upload_promotion;
    protected $adminPreview;
    protected $promotion;
    protected $adminPreviewCombo;
    protected $user;
    protected $adminPreviewCartlevel;
    protected $adminPreviewCartfree;
    protected $adminPreviewCartgroup;




    public function __construct(User $user, AdminUploadPromotion $upload_promotion, AdminPreview $adminPreview, AdminUploadPromotion $promotion, AdminPreviewCombo $adminPreviewCombo, AdminPreviewCartLevel $adminPreviewCartlevel, AdminPreviewCartLevel $adminPreviewCartfree, AdminPreviewCartGroup $adminPreviewCartgroup)
    {
        $this->upload_promotion = $upload_promotion;
        $this->adminPreview = $adminPreview;
        $this->promotion = $promotion;
        $this->adminPreviewCombo = $adminPreviewCombo;
        $this->user = $user;
        $this->adminPreviewCartlevel = $adminPreviewCartlevel;
        $this->adminPreviewCartfree = $adminPreviewCartfree;
        $this->adminPreviewCartgroup = $adminPreviewCartgroup;
    }

    public function index()
    {
        if (Auth::user()->role == 5) {
            $totalReject = DB::table('admin_preview')
                ->where('uploadedBy', Auth::user()->id)
                ->where('approveRejectStatus', '2')
                ->where('trash', 'NO')
                ->orWhere(function ($query) {
                    $query->from('admin_preview_combo')
                        ->where('uploadedBy', Auth::user()->id)
                        ->where('trash', 'NO')
                        ->where('approveRejectStatus', '2');
                })
                ->count();

            $totalPending = DB::table('admin_preview')
                ->where('uploadedBy', Auth::user()->id)
                ->where('approveRejectStatus', '0')
                ->where('trash', 'NO')
                ->orWhere(function ($query) {
                    $query->from('admin_preview_combo')
                        ->where('uploadedBy', Auth::user()->id)
                        ->where('trash', 'NO')
                        ->where('approveRejectStatus', '0');
                })
                ->count();

            $totalApproved = DB::table('admin_preview')
                ->where('uploadedBy', Auth::user()->id)
                ->where('approveRejectStatus', '1')
                ->where('trash', 'NO')
                ->orWhere(function ($query) {
                    $query->from('admin_preview_combo')
                        ->where('uploadedBy', Auth::user()->id)
                        ->where('approveRejectStatus', '1')
                        ->where('trash', 'NO');
                })
                ->count();

            $count = 0;
        } else if (Auth::user()->role == 1) {
            $manufacturerLists = DB::table('users')->where('role', '5')->where('trash', 'No')->count();
            $totalReject = DB::table('admin_preview')
                ->where('approveRejectStatus', '2')
                ->where('trash', 'NO')
                ->selectRaw('COUNT(*) as count')
                ->union(
                    DB::table('admin_preview_combo')
                        ->where('approveRejectStatus', '2')
                        ->where('trash', 'NO')
                        ->selectRaw('COUNT(*)')
                )
                ->sum('count');

            $totalPending = DB::table('admin_preview')
                ->where('approveRejectStatus', '0')
                ->where('trash', 'NO')
                ->selectRaw('COUNT(*) as count')
                ->union(
                    DB::table('admin_preview_combo')
                        ->where('approveRejectStatus', '0')
                        ->where('trash', 'NO')
                        ->selectRaw('COUNT(*)')
                )
                ->sum('count');

            $totalApproved = DB::table('admin_preview')
                ->where('approveRejectStatus', '1')
                ->where('trash', 'NO')
                ->selectRaw('COUNT(*) as count')
                ->union(
                    DB::table('admin_preview_combo')
                        ->where('approveRejectStatus', '1')
                        ->where('trash', 'NO')
                        ->selectRaw('COUNT(*)')
                )
                ->sum('count');
            $count = $manufacturerLists;
        } else {
            $manufacturerLists = Auth::user()->manufacturerLists;
            $userArray = explode(',', $manufacturerLists);
            $totalReject = DB::table('admin_preview')
                ->whereIn('uploadedBy', $userArray)
                ->where('approveRejectStatus', '2')
                ->where('trash', 'NO')
                ->selectRaw('COUNT(*) as count')
                ->union(
                    DB::table('admin_preview_combo')
                        ->whereIn('uploadedBy', $userArray)
                        ->where('approveRejectStatus', '2')
                        ->where('trash', 'NO')
                        ->selectRaw('COUNT(*)')
                )
                ->sum('count');

            $totalPending = DB::table('admin_preview')
                ->whereIn('uploadedBy', $userArray)
                ->where('approveRejectStatus', '0')
                ->where('trash', 'NO')
                ->selectRaw('COUNT(*) as count')
                ->union(
                    DB::table('admin_preview_combo')
                        ->whereIn('uploadedBy', $userArray)
                        ->where('approveRejectStatus', '0')
                        ->where('trash', 'NO')
                        ->selectRaw('COUNT(*)')
                )
                ->sum('count');

            $totalApproved = DB::table('admin_preview')
                ->whereIn('uploadedBy', $userArray)
                ->where('approveRejectStatus', '1')
                ->where('trash', 'NO')
                ->selectRaw('COUNT(*) as count')
                ->union(
                    DB::table('admin_preview_combo')
                        ->whereIn('uploadedBy', $userArray)
                        ->where('approveRejectStatus', '1')
                        ->where('trash', 'NO')
                        ->selectRaw('COUNT(*)')
                )
                ->sum('count');
            $count = count($userArray);
        }

        $data = array(
            'manufacturerLists' => $count,
            'totalReject' => $totalReject,
            'totalPending' => $totalPending,
            'totalApproved' => $totalApproved
        );
        return view('admin.index', $data);
    }

    public function profileView()
    {
        //return Auth::user();
        $id = Auth::user()->id;
        $page_data['category_details'] = CategoryTLC::get();
        $page_data['department_details'] = CategoryDept::get();
        $page_data['user_list'] = User::where('id', '!=', $id)->get();
        $page_data['user_details'] = Auth::user();
        $page_data['company_name'] = '';


        return view('admin.user_profile', $page_data);
    }

    public function change_profile_password(Request $request)
    {

        $user = Auth::user();

        if (Auth::attempt(array('email' => $user->email, 'password' => $request->old_password))) {
            if ($request->password != null && $request->confirm_password != null) {
                if ($request->password == $request->confirm_password) {
                    $password = $request->password;
                    $user->password = Hash::make($password);

                    $user->save();

                    Session::flash('message', 'Password updated successfully!');
                }
            }
        } else {
            Session::flash('message', 'Wrong old password');
        }

        return redirect(admin_url('profile'));
    }

    public function profileUpdate(Request $request)
    {

        $user = Auth::user();

        $department = '';
        $category = '';
        $reportManager = '';

        if ($request->department) {
            $department = array_to_string(array_map("decryptId", $request->department));
        }
        if ($request->category) {
            $category = array_to_string(array_map("decryptId", $request->category));
        }
        if ($request->report_manager_l1) {
            $reportManager = array_to_string(array_map("decryptId", $request->report_manager_l1));
        }

        $user->first_name = $request->fname;
        $user->last_name = $request->lname;
        $user->name = $request->fname . " " . $request->lname;
        $user->phone = $request->phone;
        $user->ext = $request->ext;
        $user->mobile = $request->mobile;
        $user->department =  $department;
        $user->category = $category;
        $user->report_manager = $reportManager;
        $user->status =  decryptId($request->profile_status);
        /* $user->profile_image = $request->profile_image; */

        $file = $request->file('profile_image');
        if ($file != null) {
            $uploadpath = 'public/uploads/profile';

            $filenewname = time() . Str::random('10') . '.' . $file->getClientOriginalExtension();
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileMimetype = $file->getMimeType();
            $fileExt = $file->getClientOriginalExtension();
            $file->move($uploadpath, $filenewname);
            $user->profile_image = $filenewname;
        }

        /* if ($request->password != null && $request->confirm_password != null) {
            if ($request->password == $request->confirm_password ) {
                $password = $request->password;
                $user->password = Hash::make($password);
            }
        } */

        $user->save();
        // }

        Session::flash('message', 'Profile updated successfully!');

        return redirect(admin_url('profile'));
    }

    public function report_view(Request $request)
    {
        if (Auth::check()) {

            if ($request->ajax()) {
                try {

                    $user_id = auth()->user()->id;
                    // $data = $this->upload_promotion->AllPromotionList();
                    if (Auth::user()->role == '5') {
                        $data = $this->upload_promotion->AllUserPromotionList($user_id);
                    } elseif (Auth::user()->role == '1') {
                        $data = $this->upload_promotion->AllPromotionList();
                    } else {
                        $getManufacturerIds = string_to_array(Auth::user()->manufacturerLists);
                        $data = $this->upload_promotion->getPromotionBasedOnCondition($getManufacturerIds);
                    }
                    $datatables = Datatables::of($data)
                        ->addIndexColumn()
                        ->addColumn('status', function ($row) {
                            if (Auth::user()->role == '5') {
                                if ($row->upload_status == 1) {
                                    $text = "Yet to Approve";
                                } else if ($row->upload_status == 2) {
                                    $text = "Approved";
                                } else {
                                    $text = "Rejected";
                                }
                            } else {
                                if ($row->promo_type == 1) {
                                    $getRejectedCount = DB::table('admin_preview')->where('trash', 'No')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->count();
                                } else if ($row->promo_type == 2) {
                                    $getRejectedCount = DB::table('admin_preview_combo')->where('trash', 'No')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->count();
                                } else {
                                    $getRejectedCount = 0;
                                }
                                if ($row->upload_status == 1) {
                                    $text = "<span style='color:blue;cursor:pointer' class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' data-rejectcount='" . $getRejectedCount . "' data-processtype='" . $row->process_type . "' >Yet to Approve<span>";
                                } else if ($row->upload_status == 2) {
                                    $text = "<span style='color:green; class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' >Approved<span>";
                                } else {
                                    $text = "<span style='color:red; class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' >Rejected<span>";
                                }
                            }

                            return $text;
                        })
                        ->addColumn('manufacturer_name', function ($row) {
                            $getUser =  $this->user->GetUser($row->manufacturer_id);
                            return $getUser->name;
                        })
                        ->addColumn('check_box', function ($row) {
                            $btn = '';
                            if ($row->upload_status == 2) {
                                $btn = '<input type="checkbox" name="promotionId[]" value="' . $row->id . '" class="promotionId" data-id="' . $row->promo_type . '" />';
                                return $btn;
                            } else {
                                return $btn;
                            }
                        })
                        ->addColumn('created_at', function ($row) {
                            $d = strtotime($row->created_at);
                            $btn = date("d/m/Y", $d);
                            return $btn;
                        })
                        ->addColumn('report_name', function ($row) {
                            $btn = $row->file_orgname;
                            return $btn;
                        })
                        ->addColumn('promo_type', function ($row) {
                            if ($row->promo_type == 1) {
                                return 'Single Promo Type';
                            } else if ($row->promo_type == 2) {
                                return 'Combo Promo Type';
                            } else if ($row->promo_type == 3) {
                                return 'Cart Level Promo Type';
                            } else {
                                return 'Cart Free Promo Type';
                            }
                        })
                        ->addColumn('rejected_file', function ($row) {
                            $btnn = '';
                            if ($row->promo_type == 2) {
                                $getCount = DB::table('admin_preview_combo')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->where('trash', 'NO')->get();
                                if (count($getCount) > 0) {
                                    $btnn = '<a class="btn btn-xs btn-danger" href="' . admin_url('download_reject_file/' . encryptId($row->id)) . '/' . encryptId($row->promo_type) . '/' . encryptId($row->manufacturer_id) . '">Generate Rejected File</a>';
                                } else {
                                    $btnn = 'No Rejected Files';
                                }
                            } else {
                                $getCount = DB::table('admin_preview')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->where('trash', 'NO')->get();
                                if (count($getCount) > 0) {
                                    $btnn = '<a class="btn btn-xs btn-danger" href="' . admin_url('download_reject_file/' . encryptId($row->id)) . '/' . encryptId($row->promo_type) . '/' . encryptId($row->manufacturer_id) . '">Generate Rejected File</a>';
                                } else {
                                    $btnn = 'No Rejected Files';
                                }
                            }

                            // if ($row->process_type == 2) {
                            //     $btnn = '';
                            //     $btnn = '<a href="' . admin_url('download_output_file/' . encryptId($row->id)) . '">Click here to Download Output File</a>';
                            // } else {
                            //     $btnn = 'Output File Not Generated';
                            // }

                            return $btnn;
                        })
                        ->addColumn('action', function ($row) {
                            $btn = '';
                            if (Auth::user()->role == '5') {
                                if ($row['promo_type'] == 1) {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row['promo_type'] == 2) {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview_combo/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row['promo_type'] == 3) {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview_level/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview_free/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                }
                            } else {
                                if ($row['promo_type'] == 1) {
                                    $btn = '<a href="' . admin_url('preview/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row['promo_type'] == 2) {
                                    $btn = '<a href="' . admin_url('preview_combo/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row['promo_type'] == 3) {
                                    $btn = '<a href="' . admin_url('preview_level/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else {
                                    $btn = '<a href="' . admin_url('preview_free/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                }
                            }

                            return $btn;
                        })
                        ->rawColumns(['action', 'created_at', 'created_by', 'status', 'check_box', 'rejected_file', 'manufacturer_name'])
                        ->make(true);

                    return $datatables;
                } catch (Exception $ex) {
                    report($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }


        $upload_promotion = $this->upload_promotion->AllPromotionList();

        $data = array(
            'upload_promotion' => $upload_promotion,

        );

        return view('admin.report_view', $data);
    }

    public function ManufacturerStatus(Request $request)
    {
        try {
            $uploadid = decryptId($request->upload_id);
            $type = $request->selected_option;
            $textarea_value = $request->textarea_value;
            if ($type == 'Approve') {
                $status = 2;
            } else {
                $status = 3;
            }
            $getPromotionType =  $this->upload_promotion->GetPromotion($uploadid);

            if ($status == 2) {
                $update_data = array(
                    'upload_status' => $status,
                    'review' => $textarea_value,
                );
                $successMsg = 'Successfully Approved';
                $where_data = array(
                    'id' => $uploadid,
                );
                AdminUploadPromotion::where($where_data)->update($update_data);
            } else {
                $where_data = array(
                    'id' => $uploadid,
                );
                $update_data = array(
                    'upload_status' => $status,
                    'review' => $textarea_value,
                );
                $successMsg = 'Rejected';
                AdminUploadPromotion::where($where_data)->update($update_data);
            }
            if ($status == 2) {
                $updatedStatus = 1;
            } else {
                $updatedStatus = 2;
            }
            $update_all_array  = array(
                'approveRejectStatus' => $updatedStatus,
                'rejectComments' => $textarea_value
            );
            if ($getPromotionType->promo_type == 1) {
                $updateStatus = $this->adminPreview->updateExceptRejected($uploadid, $update_all_array);
            } else {
                $updateStatus = $this->adminPreviewCombo->updateExceptRejected($uploadid, $update_all_array);
            }
            return response()->json(['status' => 'success', 'msg' => $successMsg], 200);
        } catch (Exception $ex) {
            dd($ex);
            return response()->json(['error' => '2', 'status' => 'error', 'msg' => 'Please try after some time'], 406);
        }
    }


    public function preview(Request $request)
    {
        try {
            $id = decryptId($request->uploadid);
            $promotion = $this->promotion->GetPromotion($id);
            $preview = DB::table('admin_preview')->where('uploadedId', $id)->where('trash', 'NO')->get();
            $data = array(
                'preview' => $preview,
                'promotion' => $promotion
            );
            return view('admin.Upload_preview', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function preview_combo(Request $request)
    {
        try {
            $id = decryptId($request->uploadid);
            $promotion = $this->promotion->GetPromotion($id);
            $preview = DB::table('admin_preview_combo')->where('uploadedId', $id)->where('trash', 'NO')->get();
            $data = array(
                'preview' => $preview,
                'promotion' => $promotion
            );
            return view('admin.Upload_preview_combo', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function preview_level(Request $request)
    {
        try {
            $id = decryptId($request->uploadid);
            $promotion = $this->promotion->GetPromotion($id);
            $preview = DB::table('admin_preview_cart_level')->where('uploadedId', $id)->where('trash', 'NO')->get();
            $data = array(
                'preview' => $preview,
                'promotion' => $promotion
            );
            return view('admin.Upload_preview_level', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function preview_free(Request $request)
    {
        try {
            $id = decryptId($request->uploadid);
            $promotion = $this->promotion->GetPromotion($id);
            $preview = DB::table('admin_preview_cart_free')->where('uploadedId', $id)->where('trash', 'NO')->get();
            $data = array(
                'preview' => $preview,
                'promotion' => $promotion
            );
            return view('admin.Upload_preview_free', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }

    public function preview_group(Request $request)
    {
        try {
            $id = decryptId($request->uploadid);
            $promotion = $this->promotion->GetPromotion($id);
            $preview = DB::table('admin_preview_cart_group')->where('uploadedId', $id)->where('trash', 'NO')->get();
            $data = array(
                'preview' => $preview,
                'promotion' => $promotion
            );
            return view('admin.Upload_preview_group', $data);
        } catch (Exception $error) {
            report($error->getMessage());
        }
    }
    public function download_output_file(Request $request)
    {
        $id = decryptId($request->promotionid);
        $data =  $this->adminPreview->GetPreviewDetails($id);
        $downloadArray = [];
        foreach ($data as $index => $listData) {
            $downloadArray[] = [
                'skuId' => $listData['code'],
                'discountType' => $listData['discountType'],
                'discountValue' => $listData['discountValue'],
                'fundingCategory' => $listData['fundingCategory'],
                'fundingMarketing' => $listData['fundingMarket'],
                'fundingVendor' => $listData['fundtionVendor'],
                'isInvoiced' => ($listData['isInvoiced'] == 1 ? 'True' : 'False'),
                'redemptionLimitPerOrder' => $listData['redemptionLimitPerOrder'],
                'redemptionLimitPerMember' => $listData['redemptionLimitPerMember'],
                'redemptionLimitPerCampaign' => $listData['redemptionLimitPerCampaign']
            ];
        }
        return \Excel::download(new OutputExport($downloadArray), 'Output_file.xlsx');
    }

    public function Reject_sku(Request $request)
    {
        try {
            $id = decryptId($request->currentIndex);
            $reason = $request->reason;
            $promotionId = decryptId($request->promotion);

            $update_data = array(
                'rejectComments' => $reason,
                'approveRejectStatus' => 2
            );
            $this->adminPreview->rejectSku($id, $update_data);
            $getRejectedSkuDetails = $this->adminPreview->getRejectedDetails($promotionId);
            $getPromotionDetails = $this->upload_promotion->GetPromotion($promotionId);
            if ($getPromotionDetails->totalSku == count($getRejectedSkuDetails)) {
                $updateArr = array(
                    'upload_status' => 3,
                    'review' => 'All SKU rejected'
                );
                $updateAll = $this->upload_promotion->UpdatePromotion($promotionId, $updateArr);
            }
            Session::flash('success', 'Rejected Successfully !');
            return response()->json(['message' => 'success']);
        } catch (Exception $error) {
            return response()->json(['message' => 'failed']);
            report($error->getMessage());
        }
    }

    public function Reject_sku_combo(Request $request)
    {
        try {
            $id = decryptId($request->currentIndex);
            $reason = $request->reason;
            $promotionId = decryptId($request->promotion);
            $update_data = array(
                'rejectComments' => $reason,
                'approveRejectStatus' => 2
            );
            $this->adminPreviewCombo->rejectSku($id, $update_data);
            $getRejectedSkuDetails = $this->adminPreviewCombo->getRejectedDetails($promotionId);
            $getPromotionDetails = $this->upload_promotion->GetPromotion($promotionId);
            if ($getPromotionDetails->totalSku == count($getRejectedSkuDetails)) {
                $updateArr = array(
                    'upload_status' => 3,
                    'review' => 'All SKU rejected'
                );
                $updateAll = $this->upload_promotion->UpdatePromotion($promotionId, $updateArr);
            }
            Session::flash('success', 'Rejected Successfully !');
            return response()->json(['message' => 'success']);
        } catch (Exception $error) {
            return response()->json(['message' => 'failed']);
            report($error->getMessage());
        }
    }

    public function Reject_sku_level(Request $request)
    {
        try {
            $id = decryptId($request->currentIndex);
            $reason = $request->reason;
            $promotionId = decryptId($request->promotion);
            $update_data = array(
                'rejectComments' => $reason,
                'approveRejectStatus' => 2
            );
            $this->adminPreviewCartlevel->rejectSku($id, $update_data);
            $getRejectedSkuDetails = $this->adminPreviewCartlevel->getRejectedDetails($promotionId);
            $getPromotionDetails = $this->upload_promotion->GetPromotion($promotionId);
            if ($getPromotionDetails->totalSku == count($getRejectedSkuDetails)) {
                $updateArr = array(
                    'upload_status' => 3,
                    'review' => 'All SKU rejected'
                );
                $updateAll = $this->upload_promotion->UpdatePromotion($promotionId, $updateArr);
            }
            Session::flash('success', 'Rejected Successfully !');
            return response()->json(['message' => 'success']);
        } catch (Exception $error) {
            return response()->json(['message' => 'failed']);
            report($error->getMessage());
        }
    }


    public function Reject_sku_free(Request $request)
    {
        try {
            $id = decryptId($request->currentIndex);
            $reason = $request->reason;
            $promotionId = decryptId($request->promotion);
            $update_data = array(
                'rejectComments' => $reason,
                'approveRejectStatus' => 2
            );
            $this->adminPreviewCartfree->rejectSku($id, $update_data);
            $getRejectedSkuDetails = $this->adminPreviewCartfree->getRejectedDetails($promotionId);
            $getPromotionDetails = $this->upload_promotion->GetPromotion($promotionId);
            if ($getPromotionDetails->totalSku == count($getRejectedSkuDetails)) {
                $updateArr = array(
                    'upload_status' => 3,
                    'review' => 'All SKU rejected'
                );
                $updateAll = $this->upload_promotion->UpdatePromotion($promotionId, $updateArr);
            }
            Session::flash('success', 'Rejected Successfully !');
            return response()->json(['message' => 'success']);
        } catch (Exception $error) {
            return response()->json(['message' => 'failed']);
            report($error->getMessage());
        }
    }


    public function Reject_sku_group(Request $request)
    {
        try {
            $id = decryptId($request->currentIndex);
            $reason = $request->reason;
            $promotionId = decryptId($request->promotion);
            $update_data = array(
                'rejectComments' => $reason,
                'approveRejectStatus' => 2
            );
            $this->adminPreviewCartgroup->rejectSku($id, $update_data);
            $getRejectedSkuDetails = $this->adminPreviewCartgroup->getRejectedDetails($promotionId);
            $getPromotionDetails = $this->upload_promotion->GetPromotion($promotionId);
            if ($getPromotionDetails->totalSku == count($getRejectedSkuDetails)) {
                $updateArr = array(
                    'upload_status' => 3,
                    'review' => 'All SKU rejected'
                );
                $updateAll = $this->upload_promotion->UpdatePromotion($promotionId, $updateArr);
            }
            Session::flash('success', 'Rejected Successfully !');
            return response()->json(['message' => 'success']);
        } catch (Exception $error) {
            return response()->json(['message' => 'failed']);
            report($error->getMessage());
        }
    }

    public function export_output(Request $request)
    {
        try {
            $selectedIndexId = string_to_array($request->selectedIndexId);
            $promo_type = $request->promo_type;

            if ($promo_type == 1) {
                $department = $request->selected_department;
                $getDepartmentArray = DB::table('admin_department')->where('parentId', $department)->pluck('department')->toArray();
                if ($department == 11) {
                    $outputFileName = 'Javelin_FMCG_Combined_Departments';
                } else {
                    $convert = array_to_string($getDepartmentArray);
                    $outputFileName = 'Javelin_' . $convert;
                }

                $data =  $this->adminPreview->getApprovedList($selectedIndexId, $getDepartmentArray);
                $downloadArray = [];
                foreach ($data as $index => $listData) {
                    // $updateArray = array(
                    //     'approveRejectStatus' => 1,
                    // );
                    // $this->adminPreview->UpdatePromotion($listData['uploadedId'], $updateArray);

                    $selectionCitites =  $listData['citiesSelection'];
                    $saCityId = '';
                    if ($selectionCitites == 'Pan India') {
                        $getIds = DB::table('admin_sa_id')->where('title', 'panindia')->first();
                        $saCityId = $getIds->description;
                    } else if ($selectionCitites == 'Selected Cities') {
                        $cityArray = string_to_array($listData['cities_yes_lists']);
                        $cityYesList = [];
                        foreach ($cityArray as $listCity) {
                            $getIds = DB::table('admin_sa_id')->where('title', strtolower($listCity))->first();
                            $cityYesList[] = $getIds->description;
                            $saCityId = array_to_string($cityYesList);
                        }
                    } else if ($selectionCitites == 'Selected Zone') {
                        $zoneArray = string_to_array($listData['zone_yes_lists']);
                        $zoneYesList = [];
                        foreach ($zoneArray as $listZone) {
                            $getIds = DB::table('admin_sa_id')->where('title', strtolower($listZone))->first();
                            $zoneYesList[] = $getIds->description;
                            $saCityId = array_to_string($zoneYesList);
                        }
                    }
                    // dd($saCityId);
                    $businessModel = strtolower($listData['businessModel']);
                    $getEcId =  DB::table('admin_ec_id')->where('businessType', $businessModel)->first();

                    $downloadArray[] = [
                        'indexId' => $listData['id'],
                        'skuId' => $listData['code'],
                        'startDate' => Carbon::createFromFormat('Y-m-d H:i:s', $listData['start_date'])->format('m-d-Y H:i'),
                        'endDate' => Carbon::createFromFormat('Y-m-d H:i:s', $listData['endDate'])->format('m-d-Y H:i'),
                        'ecId' => (isset($getEcId->ecId) ? implode(',', string_to_array($getEcId->ecId)) : ''),
                        'businessType' => 'b2c',
                        'saCityId' => $saCityId,
                        'sa' => 'All',
                        'discountType' => $listData['discountType'],
                        'offerDetails' => $listData['discountValue'],
                        'discountCategory' => 'Regular',
                        'discountValue' => $listData['discountValue'],
                        'fundingCategory' => $listData['fundingCategory'],
                        'fundingMarketing' => $listData['fundingMarket'],
                        'fundingVendor' => $listData['fundtionVendor'],
                        'isInvoiced' => ($listData['isInvoiced'] == 1 ? 'True' : 'False'),
                        'redemptionLimitPerOrder' => $listData['redemptionLimitPerOrder'],
                        'redemptionLimitPerMember' => $listData['redemptionLimitPerMember'],
                        'redemptionLimitPerCampaign' => $listData['redemptionLimitPerCampaign'],
                        'manufacturer_name' => $listData['manufacturer_name'],
                        'code' => $listData['code'],
                        'description' => $listData['description'],
                        'mrp' => $listData['mrp'],
                        'category_value' => $listData['category_value'],

                    ];
                }
                $filteredArray = [];
                $removedArray = [];
                $groupedData = [];
                $uniqueKeys = [];

                foreach ($downloadArray as $index => $data) {
                    // Generate a unique key based on the specified fields
                    $key = implode('-', [
                        $data['skuId'],
                        $data['startDate'],
                        $data['endDate'],
                        $data['discountType'],
                        // $data['offerDetails'],
                        // $data['discountValue'],
                        $data['fundingCategory'],
                        $data['fundingMarketing'],
                        $data['fundingVendor'],
                        $data['redemptionLimitPerOrder'],
                        $data['redemptionLimitPerMember'],
                        // $data['redemptionLimitPerCampaign'],
                        $data['manufacturer_name'],
                        $data['category_value'],
                    ]);
                    if (!in_array($key, $uniqueKeys)) {
                        $uniqueKeys[] = $key;
                        $filteredArray[] = $data;
                    } else {
                        // If the key is already in the uniqueKeys array, find the existing data
                        $existingData = null;
                        foreach ($filteredArray as $filteredIndex => $filteredData) {
                            $filteredKey = implode('-', [
                                $filteredData['skuId'],
                                $filteredData['startDate'],
                                $filteredData['endDate'],
                                $filteredData['discountType'],
                                // $filteredData['offerDetails'],
                                // $filteredData['discountValue'],
                                $filteredData['fundingCategory'],
                                $filteredData['fundingMarketing'],
                                $filteredData['fundingVendor'],
                                $filteredData['redemptionLimitPerOrder'],
                                $filteredData['redemptionLimitPerMember'],
                                $filteredData['manufacturer_name'],
                                $filteredData['category_value'],
                            ]);

                            if ($key === $filteredKey) {
                                $existingData = $filteredData;
                                break;
                            }
                        }

                        // Compare 'redemptionLimitPerCampaign'
                        if ($data['redemptionLimitPerCampaign'] > $existingData['redemptionLimitPerCampaign']) {
                            $removedArray[] = $existingData;
                            $filteredArray[$filteredIndex] = $data;
                        } elseif (
                            $data['redemptionLimitPerCampaign'] === $existingData['redemptionLimitPerCampaign'] &&
                            $data['discountValue'] > $existingData['discountValue']
                        ) {
                            $removedArray[] = $existingData;
                            $filteredArray[$filteredIndex] = $data;
                        } elseif (
                            $data['redemptionLimitPerCampaign'] === $existingData['redemptionLimitPerCampaign'] &&
                            $data['discountValue'] === $existingData['discountValue'] &&
                            $data['description'] !== $existingData['description']
                        ) {

                            $removedArray[] = $existingData;
                            $filteredArray[$filteredIndex] = $data;
                        } else {
                            if ($data['redemptionLimitPerCampaign'] > $existingData['redemptionLimitPerCampaign']) {
                                $removedArray[] = $existingData;
                                $filteredArray[$filteredIndex] = $data;
                            } else {
                                $removedArray[] = $data;
                            }
                        }
                    }
                }
                if (!empty($removedArray)) {
                    foreach ($removedArray as $listRemovedArray) {
                        $updateArray = array(
                            'approveRejectStatus' => 2,
                            'rejectComments' => 'Duplication SKU'
                        );
                        $this->adminPreview->rejectSku($listRemovedArray['indexId'], $updateArray);
                    }
                }

                return \Excel::download(new OutputExport($filteredArray, 1), $outputFileName . '.xlsx');
            } else {
                $getStartDate = '25' . date('-M-y');
                $getEndDate = '24-' . date('M-y', strtotime('+1 month', strtotime($getStartDate)));
                $getStartEndDate = $getStartDate . '-' . $getEndDate;
                $selected_creteria = $request->selected_creteria;
                $selected_zone = $request->selected_zone;
                $selected_cities = $request->selected_cities;
                if ($selected_creteria == 'Panindia') {
                    $outputFileName = 'MultiCombo_' . $getStartEndDate . '_Pan India';
                } else if ($selected_creteria == 'Zone') {
                    $usezones = string_to_array($selected_zone);
                    if (count($usezones) == 1) {
                        $outputFileName = 'MultiCombo_' . $getStartEndDate . '_' . $selected_zone;
                    } else {
                        $outputFileName = 'MultiCombo_' . $getStartEndDate . '_Multiple_zones';
                    }
                } else if ($selected_creteria == 'Cities') {
                    $usecities = string_to_array($selected_cities);
                    if (count($usecities) == 1) {
                        $outputFileName = 'MultiCombo_' . $getStartEndDate . '_' . $selected_cities;
                    } else {
                        $outputFileName = 'MultiCombo_' . $getStartEndDate . '_Multiple_cities';
                    }
                } else {
                    $outputFileName = 'MultiCombo_' . $getStartEndDate;
                }
                $data =  $this->adminPreviewCombo->getApprovedList($selectedIndexId, $selected_creteria, $selected_zone, $selected_cities);
                $downloadArray = [];
                foreach ($data as $index => $listData) {
                    // $updateArray = array(
                    //     'approveRejectStatus' => 1,
                    // );
                    // $this->adminPreviewCombo->UpdatePromotion($listData['uploadedId'], $updateArray);
                    $selectionCitites =  $listData['citiesSelection'];
                    $saCityId = '';
                    if ($selectionCitites == 'Pan India') {
                        $getIds = DB::table('admin_sa_id')->where('title', 'panindia')->first();
                        $saCityId = $getIds->description;
                    } else if ($selectionCitites == 'Selected Cities') {
                        $cityArray = string_to_array($listData['cities_yes_lists']);
                        $cityYesList = [];
                        foreach ($cityArray as $listCity) {
                            $getIds = DB::table('admin_sa_id')->where('title', strtolower($listCity))->first();
                            $cityYesList[] = $getIds->description;
                            $saCityId = array_to_string($cityYesList);
                        }
                    } else if ($selectionCitites == 'Selected Zone') {
                        $zoneArray = string_to_array($listData['zone_yes_lists']);
                        $zoneYesList = [];
                        foreach ($zoneArray as $listZone) {
                            $getIds = DB::table('admin_sa_id')->where('title', strtolower($listZone))->first();
                            $zoneYesList[] = $getIds->description;
                            $saCityId = array_to_string($zoneYesList);
                        }
                    }
                    // dd($saCityId);
                    $businessModel = strtolower($listData['businessModel']);
                    $getEcId =  DB::table('admin_ec_id')->where('businessType', $businessModel)->first();
                    $downloadArray[] = [
                        'indexId' => $listData['id'],
                        'skuId' => $listData['code'],
                        'combo_code' => $listData['combo_code'],
                        'startDate' => Carbon::createFromFormat('Y-m-d H:i:s', $listData['start_date'])->format('m-d-Y H:i'),
                        'endDate' => Carbon::createFromFormat('Y-m-d H:i:s', $listData['endDate'])->format('m-d-Y H:i'),
                        'ecId' => (isset($getEcId->ecId) ? implode(',', string_to_array($getEcId->ecId)) : ''),
                        'businessType' => 'b2c',
                        'saCityId' => $saCityId,
                        'sa' => 'All',
                        'discountType' => $listData['discountType'],
                        'offerDetails' => $listData['discountValue'],
                        'discountCategory' => 'Regular',
                        'discountValue' => $listData['discountValue'],
                        'fundingCategory' => $listData['fundingCategory'],
                        'fundingMarketing' => $listData['fundingMarket'],
                        'fundingVendor' => $listData['fundtionVendor'],
                        'isInvoiced' => ($listData['isInvoiced'] == 1 ? 'True' : 'False'),
                        'redemptionLimitPerOrder' => $listData['redemptionLimitPerOrder'],
                        'redemptionLimitPerMember' => $listData['redemptionLimitPerMember'],
                        'redemptionLimitPerCampaign' => $listData['redemptionLimitPerCampaign'],
                        'manufacturer_name' => $listData['manufacturer_name'],
                        'code' => $listData['code'],
                        'description' => $listData['description'],
                        'mrp' => $listData['mrp'],
                        'category_value' => $listData['category_value'],

                    ];
                }
                $filteredArray = [];
                $removedArray = [];
                $groupedData = [];
                $uniqueKeys = [];

                foreach ($downloadArray as $index => $data) {
                    $key = implode('-', [
                        $data['skuId'],
                        $data['combo_code'],
                        $data['startDate'],
                        $data['endDate'],
                        $data['discountType'],
                        // $data['offerDetails'],
                        // $data['discountValue'],
                        $data['fundingCategory'],
                        $data['fundingMarketing'],
                        $data['fundingVendor'],
                        $data['redemptionLimitPerOrder'],
                        $data['redemptionLimitPerMember'],
                        // $data['redemptionLimitPerCampaign'],
                        $data['manufacturer_name'],
                        $data['category_value'],
                    ]);
                    if (!in_array($key, $uniqueKeys)) {
                        $uniqueKeys[] = $key;
                        $filteredArray[] = $data;
                    } else {

                        $existingData = null;
                        foreach ($filteredArray as $filteredIndex => $filteredData) {
                            $filteredKey = implode('-', [
                                $filteredData['skuId'],
                                $filteredArray['combo_code'],
                                $filteredData['startDate'],
                                $filteredData['endDate'],
                                $filteredData['discountType'],
                                // $filteredData['offerDetails'],
                                // $filteredData['discountValue'],
                                $filteredData['fundingCategory'],
                                $filteredData['fundingMarketing'],
                                $filteredData['fundingVendor'],
                                $filteredData['redemptionLimitPerOrder'],
                                $filteredData['redemptionLimitPerMember'],
                                $filteredData['manufacturer_name'],
                                $filteredData['category_value'],
                            ]);

                            if ($key === $filteredKey) {
                                $existingData = $filteredData;
                                break;
                            }
                        }

                        // Compare 'redemptionLimitPerCampaign'
                        if ($data['redemptionLimitPerCampaign'] > $existingData['redemptionLimitPerCampaign']) {
                            $removedArray[] = $existingData;
                            $filteredArray[$filteredIndex] = $data;
                        } elseif (
                            $data['redemptionLimitPerCampaign'] === $existingData['redemptionLimitPerCampaign'] &&
                            $data['discountValue'] > $existingData['discountValue']
                        ) {
                            $removedArray[] = $existingData;
                            $filteredArray[$filteredIndex] = $data;
                        } elseif (
                            $data['redemptionLimitPerCampaign'] === $existingData['redemptionLimitPerCampaign'] &&
                            $data['discountValue'] === $existingData['discountValue'] &&
                            $data['description'] !== $existingData['description']
                        ) {

                            $removedArray[] = $existingData;
                            $filteredArray[$filteredIndex] = $data;
                        } else {
                            if ($data['redemptionLimitPerCampaign'] > $existingData['redemptionLimitPerCampaign']) {
                                $removedArray[] = $existingData;
                                $filteredArray[$filteredIndex] = $data;
                            } else {
                                $removedArray[] = $data;
                            }
                        }
                    }
                }
                if (!empty($removedArray)) {
                    foreach ($removedArray as $listRemovedArray) {
                        $updateArray = array(
                            'approveRejectStatus' => 2,
                            'rejectComments' => 'Duplication SKU'
                        );
                        $this->adminPreview->rejectSku($listRemovedArray['indexId'], $updateArray);
                    }
                }

                return \Excel::download(new OutputExport($filteredArray, 2), $outputFileName . '.xlsx');
            }
        } catch (Exception $ex) {
            report($ex->getMessage());
        }
    }

    public function download_reject_file(Request $request)
    {
        try {
            $promoType = decryptId($request->promotype);
            $promotionId = decryptId($request->promotionid);
            $manufacturerId = decryptId($request->manufacturer_id);
            if ($promoType == 1) {

                $getRejectedDetails = $this->adminPreview->getRejectedDetails($promotionId);
                $exportedArray = [];
                foreach ($getRejectedDetails as $listRejectedDetails) {
                    $exportedArray[] = [
                        'sr_no' => $listRejectedDetails['sr_no'],
                        'manufacturer_name' => $listRejectedDetails['manufacturer_name'],
                        'code' => $listRejectedDetails['code'],
                        'description' => $listRejectedDetails['description'],
                        'mrp' => $listRejectedDetails['mrp'],
                        'start_date' => date('m-d-Y', strtotime($listRejectedDetails['start_date'])),
                        'end_date' => date('m-d-Y', strtotime($listRejectedDetails['endDate'])),
                        'offer_details' => $listRejectedDetails['offerDetails'],
                        'redemption_campaign' => $listRejectedDetails['redemptionLimit'],
                        'selection_cities' => $listRejectedDetails['citiesSelection'],
                        'pan_india' => ($listRejectedDetails['isPanIndia'] == 0 ? 'False' : 'True'),
                        'zone_yes_list' => $listRejectedDetails['zone_yes_lists'],
                        'zone_no_list' => $listRejectedDetails['zone_no_lists'],
                        'cities_yes_lists' => $listRejectedDetails['cities_yes_lists'],
                        'cities_no_lists' => $listRejectedDetails['cities_no_lists'],
                        'department' => $listRejectedDetails['department'],
                        'business_model' => $listRejectedDetails['businessModel'],
                        'rejected_comments' => $listRejectedDetails['rejectComments']
                    ];
                }

                $rand = mt_rand(1000, 9999);
                // dd($rand);
                $getManufacturerId = $this->user->GetUser($manufacturerId);
                $details = [
                    'email' => $getManufacturerId->email,
                    'filename' => $rand,
                    'name' => $getManufacturerId->name,
                    'attachment' => storage_path('framework/laravel-excel/' . $rand . '.xlsx')
                    // 'from_date' => date('m/d/Y', strtotime($request->from_date)),
                    // 'to_date' => date('m/d/Y', strtotime($request->to_date))
                ];
                try {
                    Excel::store(new RejectedExport($exportedArray, 1), $rand . '.xlsx', 'excel_uploads');
                    $mail = dispatch((new AttachmentmailJob($details))->onQueue('high'));
                    // dd($details);

                } catch (Exception $ex) {
                    dd($ex->getMessage());
                }

                Session::flash('success', 'Mail Send Successfully');
                return redirect(admin_url('report'));
            } else {
            }
        } catch (Exception $ex) {
            report($ex->getMessage());
        }
    }

    public function report_view_dup(Request $request)
    {
        $promo_type = $request->input('promo_type');
        $department = $request->input('department');
        $selection_creteria = $request->input('selection_creteria');
        $usezones = $request->input('selection_zone');
        $usecities = $request->input('selection_cities');
        if (Auth::check()) {

            if ($request->ajax()) {
                try {

                    $user_id = auth()->user()->id;

                    if (empty($promo_type) && empty($department)) {
                        if (Auth::user()->role == '5') {
                            $data = $this->upload_promotion->AllUserPromotionList($user_id);
                        } elseif (Auth::user()->role == '1') {
                            $data = $this->upload_promotion->AllPromotionList();
                        } else {
                            $getManufacturerIds = string_to_array(Auth::user()->manufacturerLists);
                            $data = $this->upload_promotion->getPromotionBasedOnCondition($getManufacturerIds);
                        }
                    } else {
                        $data = DB::table('admin_upload_promotion')
                            ->select(
                                'admin_upload_promotion.*',
                            );
                        if (Auth::user()->role == '5') {
                            $data = $data->where('manufacturer_id', $user_id)->where('totalSku', '>', 0);
                        } else if (Auth::user()->role == '1') {
                            $data = $data->where('admin_upload_promotion.totalSku', '>', 0);
                        } else {
                            $manufacturer_id = string_to_array(Auth::user()->manufacturerLists);
                            $data = $data->whereIn('admin_upload_promotion.manufacturer_id', $manufacturer_id)->where('admin_upload_promotion.totalSku', '>', 0);
                        }
                        $getDepartmentArray = DB::table('admin_department')->Where('isParent', $department)->pluck('department')->toArray();

                        if (isset($promo_type)) {

                            $data = $data->where('admin_upload_promotion.promo_type', $promo_type);
                        }

                        if (!empty($department) && $department != '') {
                            $getDepartmentArray = DB::table('admin_department')->where('parentId', $department)->pluck('department')->toArray();
                            $data = $data->whereIn('adminPreview.department', $getDepartmentArray);
                        }
                        if (!empty($selection_creteria) && $selection_creteria != '') {
                            if ($selection_creteria == 'Panindia') {
                                $data = $data->where('adminPreviewCombo.citiesSelection', 'Pan India');
                            } else if ($selection_creteria == 'Zone' && $usezones != '' && !empty($usezones)) {
                                $usezones = explode(',', $usezones);
                                $data = $data->where(function ($query) use ($usezones) {
                                    foreach ($usezones as $item) {
                                        $query->orwhereRaw("FIND_IN_SET('$item', adminPreviewCombo.zone_yes_lists) > 0");
                                    }
                                })->where('adminPreviewCombo.citiesSelection', 'Selected Zone');
                            } else if ($selection_creteria == 'Cities' && $usecities != '' && !empty($usecities)) {
                                $usecities = explode(',', $usecities);
                                $data = $data->where(function ($query) use ($usecities) {
                                    foreach ($usecities as $item) {
                                        $query->orwhereRaw("FIND_IN_SET('$item', adminPreviewCombo.cities_yes_lists) > 0");
                                    }
                                })->where('adminPreviewCombo.citiesSelection', 'Selected Cities');
                            }
                        }
                        if ($promo_type == 1) {
                            $data =  $data->join('admin_preview AS adminPreview', 'admin_upload_promotion.id', '=', 'adminPreview.uploadedId');
                        } else if ($promo_type == 2) {
                            $data =  $data->join('admin_preview_combo AS adminPreviewCombo', 'admin_upload_promotion.id', '=', 'adminPreviewCombo.uploadedId');
                        } else if ($promo_type == 3) {
                            $data =  $data->join('admin_preview_cart_level AS adminPreviewCartlevel', 'admin_upload_promotion.id', '=', 'adminPreviewCartlevel.uploadedId');
                        } else if ($promo_type == 4) {
                            $data =  $data->join('admin_preview_cart_free AS adminPreviewCartfree', 'admin_upload_promotion.id', '=', 'adminPreviewCartfree.uploadedId');
                        } else if ($promo_type == 5) {
                            $data =  $data->join('admin_preview_cart_group AS adminPreviewCartgroup', 'admin_upload_promotion.id', '=', 'adminPreviewCartgroup.uploadedId');
                        }

                        $data =  $data->groupBy('admin_upload_promotion.id')
                            ->orderBy('admin_upload_promotion.id', 'Desc')
                            ->get();
                    }
                    $datatables = Datatables::of($data)
                        ->addIndexColumn()
                        ->addColumn('status', function ($row) {
                            if (Auth::user()->role == '5') {
                                if ($row->upload_status == 1) {
                                    $text = "Yet to Approve";
                                } else if ($row->upload_status == 2) {
                                    $text = "Approved";
                                } else {
                                    $text = "Rejected";
                                }
                            } else {
                                if ($row->promo_type == 1) {
                                    $getRejectedCount = DB::table('admin_preview')->where('trash', 'No')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->count();
                                } else if ($row->promo_type == 2) {
                                    $getRejectedCount = DB::table('admin_preview_combo')->where('trash', 'No')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->count();
                                } else if ($row->promo_type == 3) {
                                    $getRejectedCount = DB::table('admin_preview_cart_level')->where('trash', 'No')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->count();
                                } else if ($row->promo_type == 4) {
                                    $getRejectedCount = DB::table('admin_preview_cart_free')->where('trash', 'No')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->count();
                                } else if ($row->promo_type == 5) {
                                    $getRejectedCount = DB::table('admin_preview_cart_group')->where('trash', 'No')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->count();
                                } else {
                                    $getRejectedCount = 0;
                                }
                                if ($row->upload_status == 1) {
                                    $text = "<span style='color:blue;cursor:pointer' class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' data-rejectcount='" . $getRejectedCount . "' data-processtype='" . $row->process_type . "' >Yet to Approve<span>";
                                } else if ($row->upload_status == 2) {
                                    $text = "<span style='color:green; class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' >Approved<span>";
                                } else {
                                    $text = "<span style='color:red; class= 'StatusChange' data-id='" . encryptId($row->id) . "' data-type = '1' >Rejected<span>";
                                }
                            }

                            return $text;
                        })
                        ->addColumn('manufacturer_name', function ($row) {
                            $getUser =  $this->user->GetUser($row->manufacturer_id);
                            return $getUser->name;
                        })
                        ->addColumn('check_box', function ($row) {
                            $btn = '';
                            if ($row->upload_status == 2) {
                                $btn = '<input type="checkbox"  name="promotionId[]" value="' . $row->id . '" class="promotionId" data-id="' . $row->promo_type . '" />';
                                return $btn;
                            } else {
                                return $btn;
                            }
                        })
                        ->addColumn('created_at', function ($row) {
                            $d = strtotime($row->created_at);
                            $btn = date("d/m/Y", $d);
                            return $btn;
                        })
                        ->addColumn('report_name', function ($row) {
                            $btn = $row->file_orgname;
                            return $btn;
                        })
                        ->addColumn('promo_type', function ($row) {
                            if ($row->promo_type == 1) {
                                return 'Single Promo Type';
                            } else if ($row->promo_type == 2) {
                                return 'Combo Promo Type';
                            } else if ($row->promo_type == 3) {
                                return 'Cart Level Promo Type';
                            } else if ($row->promo_type == 4) {
                                return 'Cart Free Promo Type';
                            } else {
                                return 'Group Promo Type';
                            }
                        })
                        ->addColumn('rejected_file', function ($row) {
                            $btnn = '';
                            if ($row->promo_type == 2) {
                                $getCount = DB::table('admin_preview_combo')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->where('trash', 'NO')->get();
                                if (count($getCount) > 0) {
                                    $btnn = '<a class="btn btn-xs btn-danger" href="' . admin_url('download_reject_file/' . encryptId($row->id)) . '/' . encryptId($row->promo_type) . '/' . encryptId($row->manufacturer_id) . '">Generate Rejected File</a>';
                                } else {
                                    $btnn = 'No Rejected Files';
                                }
                            } else if ($row->promo_type == 3) {
                                $getCount = DB::table('admin_preview_cart_level')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->where('trash', 'NO')->get();
                                if (count($getCount) > 0) {
                                    $btnn = '<a class="btn btn-xs btn-danger" href="' . admin_url('download_reject_file/' . encryptId($row->id)) . '/' . encryptId($row->promo_type) . '/' . encryptId($row->manufacturer_id) . '">Generate Rejected File</a>';
                                } else {
                                    $btnn = 'No Rejected Files';
                                }
                            }
                            if ($row->promo_type == 4) {
                                $getCount = DB::table('admin_preview_cart_free')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->where('trash', 'NO')->get();
                                if (count($getCount) > 0) {
                                    $btnn = '<a class="btn btn-xs btn-danger" href="' . admin_url('download_reject_file/' . encryptId($row->id)) . '/' . encryptId($row->promo_type) . '/' . encryptId($row->manufacturer_id) . '">Generate Rejected File</a>';
                                } else {
                                    $btnn = 'No Rejected Files';
                                }
                            }
                            if ($row->promo_type == 5) {
                                $getCount = DB::table('admin_preview_cart_group')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->where('trash', 'NO')->get();
                                if (count($getCount) > 0) {
                                    $btnn = '<a class="btn btn-xs btn-danger" href="' . admin_url('download_reject_file/' . encryptId($row->id)) . '/' . encryptId($row->promo_type) . '/' . encryptId($row->manufacturer_id) . '">Generate Rejected File</a>';
                                } else {
                                    $btnn = 'No Rejected Files';
                                }
                            } else {
                                $getCount = DB::table('admin_preview')->where('uploadedId', $row->id)->where('approveRejectStatus', '2')->where('trash', 'NO')->get();
                                if (count($getCount) > 0) {
                                    $btnn = '<a class="btn btn-xs btn-danger" href="' . admin_url('download_reject_file/' . encryptId($row->id)) . '/' . encryptId($row->promo_type) . '/' . encryptId($row->manufacturer_id) . '">Generate Rejected File</a>';
                                } else {
                                    $btnn = 'No Rejected Files';
                                }
                            }

                            return $btnn;
                        })
                        ->addColumn('action', function ($row) {
                            $btn = '';
                            if (Auth::user()->role == '5') {
                                if ($row->promo_type == 1) {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row->promo_type == 2) {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview_combo/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row->promo_type == 3) {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview_cart_level/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row->promo_type == 4) {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview_free/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else {
                                    $btn = '<a href="' . admin_url('upload_promotion/preview_group/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                }
                            } else {
                                if ($row->promo_type == 1) {
                                    $btn = '<a href="' . admin_url('preview/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row->promo_type == 2) {
                                    $btn = '<a href="' . admin_url('preview_combo/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row->promo_type == 3) {
                                    $btn = '<a href="' . admin_url('preview_level/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else if ($row->promo_type == 4) {
                                    $btn = '<a href="' . admin_url('preview_free/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                } else {
                                    $btn = '<a href="' . admin_url('preview_group/' . encryptId($row->id)) . '" data-id = ' . $row->id . '   class="" title="View"><i class="fa  fa-eye" style="color:#0277bd;"></i></a> ';
                                }
                            }

                            return $btn;
                        })
                        ->rawColumns(['action', 'created_at', 'created_by', 'status', 'check_box', 'rejected_file', 'manufacturer_name'])
                        ->make(true);

                    return $datatables;
                } catch (Exception $ex) {
                    dd($ex);
                    report($ex);
                    return response()->json(['status' => 'error', 'msg' => 'Please try after some time'], 406);
                }
            }
        }


        $upload_promotion = $this->upload_promotion->AllPromotionList();
        $getDepartment = DB::table('admin_department')->where('isParent', '1')->orderBy('id', 'Desc')->get();
        $citiesArray = [
            'ANDHRA PRADESH', 'TELANGANA', 'ASSAM', 'BIHAR', 'CHHATTISGARH', 'GUJARAT', 'DELHI-NCR', 'JHARKHAND', 'KARNATAKA', 'KERALA', 'MADHYA PRADESH',
            'MAHARASHTRA - Mumbai', 'MAHARASHTRA - Pune', 'ORISSA', 'PUNJAB', 'RAJASTHAN', 'TAMIL NADU', 'UTTAR PRADESH', 'WEST BENGAL'
        ];
        $data = array(
            'upload_promotion' => $upload_promotion,
            'department' => $getDepartment,
            'citiesArray' => $citiesArray

        );

        return view('admin.report_view_dup', $data);
    }


    public function getAndProcessExcel()
    {
        $s3FileUrl = 'https://bb-catalog-files.s3.ap-south-1.amazonaws.com/IOT_Analytics_product_master_sku_category_details.csv';
        $localDirectory = 'public/uploads/tmp/';
        $localFileName = 'IOT_Analytics_product_master_sku_category_details.csv';
        Storage::makeDirectory($localDirectory);
        $localFilePath = $localDirectory . $localFileName;

        $response = Http::get($s3FileUrl);
        if ($response->successful()) {
            // Save the response content to a local file
            file_put_contents($localFilePath, $response->body());
            dispatch((new ProcessCsvFile($localFilePath))->onQueue('high'));            
            // Now, you can process the downloaded file located at $localFilePath
        } else {
            // Handle the case where the HTTP request was not successful
            return response()->json(['error' => 'Failed to download the file'], 500);
        }
        // $s3FilePath = 'https://bb-catalog-files.s3.ap-south-1.amazonaws.com/IOT_Analytics_product_master_sku_category_details';
        // dd($s3FilePath);
        // $localFilePath = 'public/uploads/tmp/' . $s3FilePath;
        // Storage::disk('s3')->get($s3FilePath, $localFilePath);

        // $fileKey = 'https://bb-catalog-files.s3.ap-south-1.amazonaws.com/IOT_Analytics_product_master_sku_category_details';
        // $csv = Storage::disk('s3')->get($fileKey);
        // dd($csv);
        // // Specify the S3 bucket and file name
        // $bucket = 'bb-catalog-files';
        // $fileKey = 'https://bb-catalog-files.s3.ap-south-1.amazonaws.com/IOT_Analytics_product_master_sku_category_details';

        // // Download the Excel file from S3 to a temporary location
        // $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
        // Storage::disk('s3')->get($fileKey, fopen($tempFilePath, 'w'));

        // // Initialize the SimpleXLSX object
        // $xlsx = new SimpleXLSX($tempFilePath);

        // if ($xlsx->success()) {
        //     $rows = $xlsx->rows();
        //     // Process the rows as needed
        //     // $rows is now an array containing the Excel data
        //     // Each element in $rows represents a row in the Excel file

        //     // Example: Print the Excel data
        //     foreach ($rows as $row) {
        //         print_r($row);
        //     }
        // } else {
        //     // Handle the case where the file couldn't be read
        //     return redirect()->back()->with('error', 'Error reading Excel file from S3.');
        // }

        // // Delete the temporary file
        // unlink($tempFilePath);

        // return redirect()->back()->with('success', 'Excel file retrieved from S3 and processed successfully.');
    }
}
