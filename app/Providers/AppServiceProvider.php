<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
        Schema::defaultStringLength(255);
        Paginator::useBootstrap();

        Validator::extend('recaptcha', 'App\\Validators\\CustomValidation@validate');

        defined('MENU') or define('MENU', 'template_left_menu');

        View::composer('*', function ($view) {
            $mymenu = [];
                if (Auth::check()) {
                    if (Auth::user()->role == '1') {

                    $mymenu = [
                        '1','3','4','5','6','7','8'
                    ];
                }
                if(Auth::user()->role == '5'){
                    $mymenu = [
                        '1','2','3','7','4'
                    ];
                }
                if(Auth::user()->role == '4'){
                    $mymenu = [
                        '1','3','7','4'
                    ];
                }
                if(Auth::user()->role == '3'){
                    $mymenu = [
                        '1','3','7','4'
                    ];
                }
                if(Auth::user()->role == '2'){
                    $mymenu = [
                        '1','3','7','4'
                    ];
                }
               
            }
            $menu = DB::table(MENU)
                ->select('id', 'name', 'namekey', 'link', 'icon', 'parent_id', 'is_parent', 'is_module', 'sort_order')
                ->where('status', 1)
                ->where('trash', 'NO')
                ->whereIn('id', $mymenu)
                ->orderBy('parent_id', 'asc')
                ->orderBy('sort_order', 'asc')
                ->get();

            $menu_lsit = get_admin_menu($menu);
            View::share('left_menu', $menu_lsit);
        });
    }
}
