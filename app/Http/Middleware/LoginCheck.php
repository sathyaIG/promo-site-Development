<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin\Common;
use Illuminate\Support\Facades\DB;

class LoginCheck {

    public function handle($request, Closure $next) {

     
        if ($request->session()->has('admin_id')) {

            return redirect(config('constants.ADMIN_URL') . "dashboard");
            
        } 
        return $next($request);
    }

}
