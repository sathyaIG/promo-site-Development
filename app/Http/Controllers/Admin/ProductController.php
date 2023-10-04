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


class ProductController extends Controller
{
    public function index(Request $request){
        return view('admin.Product_list');
    }
}