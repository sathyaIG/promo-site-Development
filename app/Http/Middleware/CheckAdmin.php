<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdmin
{    
    public function handle($request, Closure $next)
    {    	
        if($request->session()->has('admin_id'))
        {
            return redirect()->back();
        }

        return $next($request);
    }
}
