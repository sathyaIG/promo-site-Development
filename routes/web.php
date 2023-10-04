<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('test', 'Admin\TestController@index');
Route::get('cache', function () {

    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('route:clear');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('queue:restart');

    return 'Routes cache cleared';
});


Route::get('/', function () {

    return redirect('login');
});
// Route::get('/', 'Admin\LoginController@logout');

Route::get('Account_Activate/{token}', 'Admin\LoginController@Account_Activate');
Route::post('SubmitAccountActivate', 'Admin\LoginController@SubmitAccountActivate');

Route::get('otp', 'Admin\LoginController@Otp');
Route::post('OtpSubmit', 'Admin\LoginController@OtpSubmit');
Route::get('forcepassword_change', 'Admin\LoginController@forcepassword_change');
Route::get('update_password_date', 'Admin\UserController@update_password_date');

Route::get('login', 'Auth\LoginController@login')->name('login');
Route::post('logintry', 'Admin\LoginController@authenticate');
Route::get('logout', 'Admin\LoginController@logout');
Route::Post('resetpasswordSend', 'Admin\LoginController@resetPassword')->name('resetpasswordSend');
Route::Post('passwordreset_submit', 'Admin\LoginController@Passwordreset')->name('passwordreset_submit');


Route::middleware(['is_login'])->group(function () {

    // Route::get('index', 'Admin\AdminController@index');
    Route::get('dashboard', 'Admin\AdminController@index');

    Route::get('profile', 'Admin\AdminController@profileView');
    Route::post('profile', 'Admin\AdminController@profileUpdate');

    Route::post('change_profile_password', 'Admin\AdminController@change_profile_password');

    /*
     * User Management
     */

    Route::get('user_management', 'Admin\UserController@index');
    Route::get('UserAdd', 'Admin\UserController@UserAdd');
    Route::post('UserAddSubmit', 'Admin\UserController@UserAddSubmit');
    Route::get('user_management/{userid}', 'Admin\UserController@UserView');
    Route::get('user_management/edit/{userid}', 'Admin\UserController@UserEdit');
    Route::post('user_management/edit/{userid}', 'Admin\UserController@UserUpdate');
    Route::post('UserDelete', 'Admin\UserController@UserDelete');
    Route::post('UserStatus', 'Admin\UserController@UserStatus');
    Route::post('Useremailcheck', 'Admin\UserController@Useremailcheck');

    Route::get('UserImport', 'Admin\UserController@UserImport');
    Route::post('UserImportSubmit', 'Admin\UserController@UserImportSubmit');
    Route::get('UserProfileDownload/{reporttype}', 'Admin\UserController@UserProfileDownload');

    Route::post('UserFileUpload', 'Admin\UserController@UserFileUpload');

    Route::post('UserDownload', 'Admin\UserController@UserDownload');
    Route::get('Upload_view/{uploadid}', 'Admin\ErrorLogController@UploadView');

    /*
     * Region Management
     */

    Route::get('region_management', 'Admin\RegionController@index');
    Route::get('RegionAdd', 'Admin\RegionController@RegionAdd');
    Route::post('RegionAddSubmit', 'Admin\RegionController@RegionAddSubmit');
    Route::get('region_management/{regionid}', 'Admin\RegionController@RegionView');
    Route::get('region_management/edit/{regionid}', 'Admin\RegionController@RegionEdit');
    Route::post('region_management/edit/{regionid}', 'Admin\RegionController@RegionUpdate');
    Route::post('RegionDelete', 'Admin\RegionController@RegionDelete');
    Route::post('RegionStatus', 'Admin\RegionController@RegionStatus');
    Route::post('state_get_city', 'Admin\RegionController@state_get_city');

    /** Upload Log */

    Route::get('upload_logs', 'Admin\ErrorLogController@index');
    Route::get('Upload_view/{uploadid}', 'Admin\ErrorLogController@UploadView');
    Route::get('UploadFileDownload/{upload_id}', 'Admin\ErrorLogController@DownloadFile');


        /** Upload User Log */

        Route::get('upload_user_logs', 'Admin\ErrorLogController@upload_user_logs');
        Route::get('Upload_user_view/{uploadid}', 'Admin\ErrorLogController@UploadUserView');
        Route::get('UploadUserFileDownload/{upload_id}', 'Admin\ErrorLogController@UserDownloadFile');


    /** Upload promotion **/

    Route::get('upload_promotion', 'Admin\UploadPromotionController@index');
    Route::post('PromotypeDownload', 'Admin\UploadPromotionController@PromotypeDownload');

    /** Product List */
    Route::get('product_list', 'Admin\ProductController@index');

    Route::post('PromotypeFileUpload', 'Admin\UploadPromotionController@PromotypeFileUpload');
    Route::get('upload_promotion/edit/{promotionid}', 'Admin\UploadPromotionController@PromotionEdit');
    Route::post('upload_promotion/edit/{promotionid}', 'Admin\UploadPromotionController@PromotionUpdate');
    Route::get('upload_promotion/preview/{promotionid}','Admin\UploadPromotionController@PromotionPreview');
    Route::get('upload_promotion/preview_combo/{promotionid}','Admin\UploadPromotionController@PromotionPreviewCombo');
    Route::get('upload_promotion/preview_cart_level/{promotionid}','Admin\UploadPromotionController@PromotionPreviewCartLevel');
    Route::get('upload_promotion/preview_cart_free/{promotionid}','Admin\UploadPromotionController@PromotionPreviewCartFree');
    Route::get('upload_promotion/preview_cart_group/{promotionid}','Admin\UploadPromotionController@PromotionPreviewCartGroup');
    Route::post('upload_promotion/edit_preview/','Admin\UploadPromotionController@PromotionEditPreview');
    Route::get('upload_promotion/edit_preview/','Admin\UploadPromotionController@PromotionEditPreview');

    Route::post('create/{promotionid}', 'Admin\UploadPromotionController@create');
    Route::post('create_combo/{promotionid}', 'Admin\UploadPromotionController@create_combo');
    Route::post('create_level/{promotionid}', 'Admin\UploadPromotionController@create_level');
    Route::post('create_free/{promotionid}', 'Admin\UploadPromotionController@create_free');
    Route::post('create_group/{promotionid}', 'Admin\UploadPromotionController@create_group');
    Route::post('Download_dispatch/{promotionid}', 'Admin\UploadPromotionController@Download_dispatch');
    Route::post('Download_dispatch_combo/{promotionid}', 'Admin\UploadPromotionController@Download_dispatch_combo');
    Route::post('Download_dispatch_cart_level/{promotionid}', 'Admin\UploadPromotionController@Download_dispatch_cart_level');
    // Route::post('Download_dispatch_cart_free/{promotionid}', 'Admin\UploadPromotionController@Download_dispatch_cart_free');
    Route::post('PromotypeFileEdit/{promotionid}','Admin\UploadPromotionController@PromotypeFileEdit');
    Route::post('PromotypeFileEditCombo/{promotionid}','Admin\UploadPromotionController@PromotypeFileEditCombo');
    Route::post('PromotypeFileEditCartLevel/{promotionid}','Admin\UploadPromotionController@PromotypeFileEditCartLevel');
    Route::post('PromotypeFileEditCartFree/{promotionid}','Admin\UploadPromotionController@PromotypeFileEditCartFree');
    Route::post('PromotypeFileEditCartGroup/{promotionid}','Admin\UploadPromotionController@PromotypeFileEditCartGroup');


    /**Report */
    Route::get('report_dup','Admin\AdminController@report_view');
    Route::get('report','Admin\AdminController@report_view_dup');
    Route::get('preview/{uploadid}','Admin\AdminController@preview');
    Route::get('preview_combo/{uploadid}','Admin\AdminController@preview_combo');
    Route::get('preview_level/{uploadid}','Admin\AdminController@preview_level');
    Route::get('preview_free/{uploadid}','Admin\AdminController@preview_free');
    Route::get('preview_group/{uploadid}','Admin\AdminController@preview_group');
    Route::post('ManufacturerStatus', 'Admin\AdminController@ManufacturerStatus');
    Route::get('download_output_file/{promotionid}','Admin\AdminController@download_output_file');
    Route::post('Reject_sku/','Admin\AdminController@Reject_sku');
    Route::post('Reject_sku_combo/','Admin\AdminController@Reject_sku_combo');
    Route::post('Reject_sku_level/','Admin\AdminController@Reject_sku_level');
    Route::post('Reject_sku_free/','Admin\AdminController@Reject_sku_free');
    Route::post('Reject_sku_group/','Admin\AdminController@Reject_sku_group');
    Route::post('export_output','Admin\AdminController@export_output');
    Route::get('export_output','Admin\AdminController@export_output');
    Route::get('download_reject_file/{promotionid}/{promotype}/{manufacturer_id}','Admin\AdminController@download_reject_file');


    Route::get('General_Settings_View', 'Admin\SettingsController@General_Settings_View');
    Route::get('General_Settings_Edit', 'Admin\SettingsController@General_Settings_Edit');
    Route::post('General_Settings_EditSubmit', 'Admin\SettingsController@General_Settings_EditSubmit');
    Route::post('General_Settings_EditSubmit', 'Admin\SettingsController@General_Settings_EditSubmit');

    Route::get('/get-excel', 'Admin\AdminController@getAndProcessExcel');

    


});


Auth::routes();
