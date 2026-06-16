<?php

namespace App\Http\Controllers;

use App\Http\Requests\g2fverifyRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUser;
use App\Http\Requests\ResetPasswordSaveRequest;
use App\Http\Services\AuthService;
use App\Http\Services\GeeTestService;
use App\Http\Services\User2FAService;
use App\Model\UserVerificationCode;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as UserAuth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;
use Laravel\PhpLaravel\Helpers\Auth;


class AuthController extends Controller
{
    public $geeTestService;
    public function __construct()
    {
        $this->geeTestService = new GeeTestService;
    }

    //login
    public function login()
    {
        if (UserAuth::user()) {
            if (UserAuth::user()->role == USER_ROLE_ADMIN) {
                return redirect()->route('adminDashboard');
            } else {
                UserAuth::logout();
                return view('auth.login');
            }
        }
        return view('auth.login');
    }

    // sign up
    public function signUp()
    {
        return view('auth.signup');
    }

    // forgot password
    public function forgotPassword()
    {
        return view('auth.forgot_password');
    }

    // forgot password
    public function resetPasswordPage()
    {
        return view('auth.reset_password');
    }

    // sign up process with referral sign up
    public function signUpProcess(RegisterUser $request)
    {
        try {
            $service = new AuthService();
            if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->withInput()->with('dismiss', __('Invalid email address'));
            }
            $response = $service->signUpProcess($request);
            if ($response['success'] == true) {
                return redirect()->route('login')->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            return ['status' => false, 'message' => __('Failed to signup! Try Again.')];
        }
    }


    // login process
    public function loginProcess(LoginRequest $request)
    {
        if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_GEETESTCAPTCHA)) {
            $geetest_response = $this->geeTestService->checkValidation($request);

            if (!$geetest_response['success']) {
                return back()->with('dismiss', $geetest_response['message']);
            }
        }

        $data['success'] = false;
        $data['message'] = '';
        $data['token'] = '';
        $user = User::where('email', $request->email)->first();
        $service = new AuthService();
        if (!empty($user)) {
            if (empty($user->email_verified_at))
                $user->email_verified_at = 0;

            if ($user->role == USER_ROLE_ADMIN) {
                $auth = Auth::attempt(['email' => $request->email, 'password' => $request->password]);
                // dd($auth);
                if (is_bool($auth)) {
                    if ($auth) {
                        //Check email verification
                        if ($user->status == STATUS_SUCCESS) {
                            if (!empty($user->is_verified)) {
                                $data['success'] = true;
                                $data['message'] = __('Login successful');
                                if (UserAuth::user()->role == USER_ROLE_ADMIN) {
                                    return redirect()->route('adminDashboard')->with('success', $data['message']);
                                } else {
                                    createUserActivity(UserAuth::user()->id, USER_ACTIVITY_LOGIN);

                                    return redirect()->route('exchangeDashboard')->with('success', $data['message']);
                                }
                            } else {
                                $existsToken = User::join('user_verification_codes', 'user_verification_codes.user_id', 'users.id')
                                    ->where('user_verification_codes.user_id', $user->id)
                                    ->whereDate('user_verification_codes.expired_at', '>=', Carbon::now()->format('Y-m-d'))
                                    ->first();
                                if (!empty($existsToken)) {
                                    $mail_key = $existsToken->code;
                                } else {
                                    $mail_key = randomNumber(6);
                                    UserVerificationCode::create(['user_id' => $user->id, 'code' => $mail_key, 'status' => STATUS_PENDING, 'expired_at' => date('Y-m-d', strtotime('+15 days'))]);
                                }
                                try {
                                    $service->sendEmail($user, $mail_key);
                                    $data['success'] = false;
                                    $data['message'] = __('Your email is not verified yet. Please verify your mail.');
                                    UserAuth::logout();

                                    return redirect()->back()->with('dismiss', $data['message']);
                                } catch (\Exception $e) {
                                    $data['success'] = false;
                                    $data['message'] = $e->getMessage();
                                    UserAuth::logout();

                                    return redirect()->back()->with('dismiss', $data['message']);
                                }
                            }
                        } elseif ($user->status == STATUS_SUSPENDED) {
                            $data['success'] = false;
                            $data['message'] = __("Your account has been suspended. please contact support team to active again");
                            UserAuth::logout();
                            return redirect()->back()->with('dismiss', $data['message']);
                        } elseif ($user->status == STATUS_DELETED) {
                            $data['success'] = false;
                            $data['message'] = __("Your account has been deleted. please contact support team to active again");
                            UserAuth::logout();
                            return redirect()->back()->with('dismiss', $data['message']);
                        } elseif ($user->status == STATUS_PENDING) {
                            $data['success'] = false;
                            $data['message'] = __("Your account has been pending for system approval. please contact support team to active again");
                            UserAuth::logout();
                            return redirect()->back()->with('dismiss', $data['message']);
                        }
                    } else {
                        $data['success'] = false;
                        $data['message'] = __("Email or Password doesn't match");
                        return redirect()->back()->with('dismiss', $data['message']);
                    }
                } else {
                    return redirect()->route($auth);
                }
            } else {
                $data['success'] = false;
                $data['message'] = __("You have no login access");
                UserAuth::logout();
                return redirect()->back()->with('dismiss', $data['message']);
            }
        } else {
            $data['success'] = false;
            $data['message'] = __("You have no account,please register new account");
            return redirect()->back()->with('dismiss', $data['message']);
        }
    }


    // send forgot mail
    public function sendForgotMail(Request $request)
    {
        $service = new AuthService();
        $rules = ['email' => 'required|email|exists:users,email'];
        $messages = [
            'email.required' => __('Email field can not be empty'),
            'email.email' => __('Email is invalid'),
            'email.exists' => __('Email is invalid'),
        ];
        $validatedData = $request->validate($rules, $messages);

        $response = $service->sendForgotMailProcess($request);
        if ($response['success'] == true) {
            return redirect()->route('resetPasswordPage')->with('success', $response['message']);
        } else {
            return redirect()->back()->with('dismiss', $response['message']);
        }
    }

    // logout
    public function logOut()
    {
        Session::forget('g2f_checked');
        Session::flush();
        Cookie::queue(Cookie::forget('accesstokenvalue'));
        UserAuth::logout();

        return redirect()->route('logOut')->with('success', __('Logout successful'));
    }

    // reset password save process

    public function resetPasswordSave(ResetPasswordSaveRequest $request)
    {
        $service = new AuthService();
        $response = $service->passwordResetProcess($request);
        if ($response['success'] == true) {
            return redirect()->route('login')->with('success', $response['message']);
        } else {
            return redirect()->back()->with('dismiss', $response['message']);
        }
    }
    public function getSettingTwoFactor()
    {
        $settings = settings();
        return json_decode($settings["two_factor_list"] ?? "{}", true);
    }
    // For Google Two Factor
    public function g2fChecked(Request $request)
    {
        $data['setting'] = settings();
        $data['two_factor'] = $this->getSettingTwoFactor();
        //        if(UserAuth::user()->g2f_enabled == DISABLE) {
        //            UserAuth::logout();
        //            return redirect()->route("login")->with("dismiss",__("Something went wrong"));
        //        }
        return view('auth.g2f', $data);
    }
    // For Email Two Factor
    public function verifyEmail(Request $request)
    {
        if ($request->ajax()) {
            if (UserAuth::user()->email_enabled == DISABLE) {
                return response()->json(["success" => false, "message" => __("Something went wrong")]);
            }
            $email = new User2FAService();
            $email->sendOtpCodeEmail();
            return response()->json(["success" => true, "message" => __("Email sent successfully")]);
        }
        $data['two_factor'] = $this->getSettingTwoFactor();
        if (UserAuth::user()->email_enabled == DISABLE) {
            UserAuth::logout();
            return redirect()->route("login")->with("dismiss", __("Something went wrong"));
        }
        $email = new User2FAService();
        $email->sendOtpCodeEmail();
        return view('auth.verify_email', $data);
    }
    // For Phone Two Factor
    public function verifyPhone(Request $request)
    {
        if ($request->ajax()) {
            if (UserAuth::user()->phone_enabled == DISABLE) {
                return response()->json(["success" => false, "message" => __("Something went wrong")]);
            }
            $sms = new User2FAService();
            $sms->sendOtpCode();
            return response()->json(["success" => true, "message" => __("SMS sent successfully")]);
        }
        $data['two_factor'] = $this->getSettingTwoFactor();
        $user = UserAuth::user();
        if (UserAuth::user()->phone_enabled == DISABLE) {
            UserAuth::logout();
            return redirect()->route("login")->with("dismiss", __("Something went wrong"));
        }
        if (!$user->phone_verified) {
            UserAuth::logout();
            return redirect()->route("login")->with("dismiss", __("Phone number is not verified"));
        }
        $sms = new User2FAService();
        $sms->sendOtpCode();
        return view('auth.verify_phone', $data);
    }

    public function twoFactorVerify(Request $request)
    {
        $twoFactorVerify = new User2FAService();
        $valid = $twoFactorVerify->userOtpVerification($request, UserAuth::user());

        if ($valid["success"]) {
            Session::put('g2f_checked', true);
            return redirect()->route('adminDashboard')->with('success', __("Login successful"));
        }
        return redirect()->back()->with('dismiss', $valid["message"]);
    }


    // g2fa verification
    public function g2fVerify(g2fverifyRequest $request)
    {

        $google2fa = new Google2FA();
        $google2fa->setAllowInsecureCallToGoogleApis(true);
        $valid = $google2fa->verifyKey(UserAuth::user()->google2fa_secret, $request->code, 8);

        if ($valid) {
            Session::put('g2f_checked', true);
            return redirect()->route('exchangeDashboard')->with('success', __('Login successful'));
        }
        return redirect()->back()->with('dismiss', __('Code doesn\'t match'));
    }
    // verify email
    //
    public function verifyEmailPost(Request $request)
    {
        $service = new AuthService();
        $response = $service->verifyEmailProcess($request);
        if ($response['success'] == true) {
            return redirect()->route('login')->with('success', $response['message']);
        } else {
            return redirect()->route('login')->with('dismiss', $response['message']);
        }
    }

    public function test()
    {
        $liquidationPriceBuy = bcsubx(72153, bcdivx(bcmulx(72153, 5, 8), 100, 8), 8);

        $liquidationPriceSell = bcaddx(71342, bcdivx(bcmulx(72153, 5, 8), 100, 8), 8);

        dd($liquidationPriceBuy, $liquidationPriceSell);
    }
}
