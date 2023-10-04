<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin\Login;
use App\Models\Admin\Modules;
use Illuminate\Support\Facades\DB;

class PermisionCheck {

    public function handle($request, Closure $next) {

        if (!$request->session()->has('admin_id')) {

            return redirect('login');
        }

        if (!$request->session()->has('admin_id')) {

            return redirect('login');
        }

        $admin_details = Login::where('admin_id', $request->session()->get('admin_id'))->first();

        $request->route()->setParameter('admin_details', $admin_details);

        $admin_user_permission = $admin_details->admin_modules;
        $user_array = srting_to_array($admin_user_permission);
        
        $admin_role_id = $admin_details->admin_role;
        $role_permission = DB::table(ROLE)
                ->select('role_permission')
                ->where('role_id', $admin_role_id)
                ->where('status', '1')
                ->where('status', '1')
                ->where('trash', 'NO')
                ->get()
                ->first();

        $admin_role_permission = $role_permission->role_permission;
        $role_array = srting_to_array($admin_role_permission);
        
        $final_array = merge_two_array($user_array,$role_array);

        
        $menu = DB::table(MODULES)
                ->select('*')
                ->whereIn('menu_id', $final_array)
                ->where('status', '1')
                ->where('trash', 'NO')
                ->orderByRaw('parent_id - sort_order DESC')
                ->get();
        
        

        $html = get_admin_menu($menu, FALSE);



        $menu_types = array('1' => 'Internal link', '2' => 'External link', '3' => 'Pdf', '4' => 'Image', '5' => 'Internal page link');

        $list_types = array('1' => 'Announcements', '2' => 'Press release', '3' => 'Tender');
        
        $request->route()->setParameter('menu_list', $menu);
        $request->route()->setParameter('left_menu', $html);

        $request->route()->setParameter('menu_types', $menu_types);
        $request->route()->setParameter('list_types', $list_types);

        return $next($request);
    }

}
