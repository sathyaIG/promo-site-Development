<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DB;
use Str;
use Mail;
use Log;
use Session;
use App\Mail\Otpmail;
use App\Jobs\ResetpasswordJob;
use App\Jobs\ChangepasswordJob;
use App\Models\User;


class LoginController extends Controller
{



    public function showLoginForm()
    {
        session(['link' => url()->previous()]);
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {

        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        $messages = [
            'email.required' => 'Please enter your email address!',
            'email.email' => 'Please enter a valid email address!',
            'password.required' => 'Please enter your password',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember') ? true : false;

        $email = $request->email;
        $userCheck = User::where('email', $email)->where('trash', 'NO');

        $userDelete = $userCheck->first();
        $userCheck_status = $userCheck->where('status', 1)->first();

        if ($userDelete) {
            if ($userCheck_status) {
                if (Auth::attempt($credentials, $remember)) {

                    $request->session()->regenerate();

                    return redirect()->intended(admin_url('dashboard'));
                }
            } else {
                Session::flash('error', 'User is In-Active');
                return back()->withErrors([
                    'email' => 'User is In-active',
                ]);
            }
        } else {
            Session::flash('error', 'Invalid User');
            return back()->withErrors([
                'email' => 'Invalid User',
            ]);
        }
        Session::flash('error', 'Invalid Email and Password');
        return back()->withErrors([
            'email' => 'Username or Password is incorrect',
        ]);
    }

    public function Login_index(Request $request)
    {

        $data = [];

        return view('admin.index_login', $data);
    }

    public function logout(Request $request)
    {

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        return redirect(url('login'));
    }

    public function resetPassword(Request $request)
    {

        $rules = [
            'email' => 'required|email',
        ];
        $messages = [
            'email.required' => 'Enter official Email',
            'email.email' => 'Enter valid Email',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();
        }


        $user = DB::table('users')->where('trash', 'NO')->where('email', '=', $request->email)
            ->first();

        if ($user == null) {

            $userdetails = userDetails();

            $logmessage = 'Invalid user try to login ' . $request->email . ', in user-agent : ' . $userdetails['useragent'] . ', User IP : ' . $userdetails['ip'];
            Log::channel('password-reset')->warning($logmessage);

            return redirect()->back()->with('error', trans('Please enter registered Email ID.'));
        }

        DB::table('password_resets')->where('email', $user->email)
            ->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Str::random(60),
            'created_at' => Carbon::now()
        ]);

        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)
            ->orderBy('created_at', 'Desc')
            ->first();
        $logmessage = 'User ' . $tokenData->email . ' request for Password Reset on ' . $tokenData->created_at . " User reset token is " . $tokenData->token;

        Log::channel('password-reset')->info($logmessage);

        //return redirect()->back()->with('status', trans('A reset password link has been sent to your Register email address.'));

        if ($this->sendResetEmail($request->email, $tokenData->token)) {
            return redirect()->back()->with('status', trans('A reset password link has been sent to your registered email address.'));
        } else {
            return redirect()->back()->with('status', trans('A Network Error occurred. Please try again.'));
        }
    }

    private function sendResetEmail($email, $token)
    {

        $user = DB::table('users')->where('trash', 'NO')->where('email', $email)->select('name', 'email')->first();

        $link = getHost() . 'password/reset/' . $token . '?email=' . urlencode($user->email);


        try {

            $details = [
                "email" => $user->email,
                "name" => $user->name,
                "link" => $link,
                "expire" => ""
            ];

            dispatch((new ResetpasswordJob($details))->onQueue('high'));

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function Passwordreset(Request $request)
    {

        //Validate input
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required|same:password-confirm',
                'token' => 'required'
            ]
        );

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator);
        }

        $password = $request->password;

        $newTime = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " -" . get_constant('RESET_PASSWORD_EXPIRE') . " minutes"));

        $tokenData = DB::table('password_resets')
            ->where('token', $request->token)
            ->where('created_at', '>=', $newTime)
            ->first();

        if ($tokenData == null) {
            return redirect()->back()->withErrors(['email' => 'Forgot password link Expired']);
        }

        $user = User::where('email', $tokenData->email)->first();

        if (!$user) {

            return redirect()->back()->withErrors(['email' => 'Email not found']);
        }


        $user->password = Hash::make($password);
        $user->update();

        //Auth::login($user);

        DB::table('password_resets')
            ->where('email', $user->email)
            ->delete();

        if ($this->sendSuccessEmail($tokenData->email)) {
            return redirect(url('login'));
        } else {


            return redirect()->back()->withErrors(['email' => trans('A Network Error occurred. Please try again.')]);
        }
    }

    private function sendSuccessEmail($email, $token = '')
    {

        $user = DB::table('users')->where('trash', 'NO')->where('email', $email)->select('name', 'email')->first();

        try {

            $details = [
                "email" => $user->email,
                "name" => $user->name,
                "link" => ''
            ];

            dispatch((new ChangepasswordJob($details))->onQueue('high'));

            return true;
        } catch (\Exception $e) {

            return false;
        }
    }

    protected function credentials(Request $request)
    {

        if (is_numeric($request->get('email'))) {
            return ['phone' => $request->get('email'), 'password' => $request->get('password')];
        } elseif (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {

            return ['email' => $request->get('email'), 'password' => $request->get('password')];
        }
        return ['loginid' => $request->get('email'), 'password' => $request->get('password')];
    }

    protected function loginTry($credentials, $request)
    {

        $userDetails = USER::withoutGlobalScopes()
            ->select('*')
            ->where($credentials)
            ->first();

        return $userDetails;
    }

    protected function setSession($userDetails)
    {

        $userDetails = USER::withoutGlobalScopes()
            ->select('*')
            ->where($credentials)
            ->first();

        return $userDetails;
    }

    public function validate_email(Request $request)
    {

        if ($request->input('email') !== '') {
            if ($request->input('email')) {
                $rule = array('email' => 'Required|email|unique:users');
                $validator = Validator::make($request->all(), $rule);
            }
            if (!$validator->fails()) {
                die('true');
            }
        }
        die('false');
    }

    public function Account_Activate(Request $request)
    {


        if (Auth::check()) {
            if (Auth::user()->email == $request->email) {
                return redirect('login');
            }
        }


        $data = array(
            'token' => $request->token,
            'email' => $request->email,
            'status' => 'not_activate'
        );
        $whrere_array = array(
            'active_tokan' => $request->token,
            'email' => $request->email,
            'status' => '0'
        );

        $userdetails = User::where($whrere_array)->first();



        if ($userdetails == null) {
            $data['status'] = 'already_active';
        }


        return view('admin.account_activate', $data);
    }

    public function SubmitAccountActivate(Request $request)
    {


        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required|min:12|same:password-confirm',
                'token' => 'required'
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $password = $request->password;

        $whrere_array = array(
            'active_tokan' => $request->token,
            'email' => $request->email,
            'is_active' => '0'
        );

        $user = User::where($whrere_array)->first();

        if ($user == null) {
            return redirect()->back()->withErrors(['email' => 'Your account has already been activated.']);
        }

        $user = User::where('email', $user->email)->first();

        if (!$user)
            return redirect()->back()->withErrors(['email' => 'Email not found']);

        $user->password = Hash::make($password);
        $user->active_tokan = '';
        $user->is_active = '1';
        $user->status = '1';
        $user->update();

        //Auth::login($user);

        return redirect(admin_url('dashboard'));
    }
}
