<?php

namespace App\Http\Services;

use App\Http\Repositories\User2FARepositorie;
use App\Jobs\MailSend;
use App\Model\AdminSetting;
use App\Traits\ResponseFormatTrait;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

class User2FAService extends BaseService
{
    use ResponseFormatTrait;

    public $model = AdminSetting::class;
    public $repository = User2FARepositorie::class;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
    }

    // user google 2fa validation check
    public function userGoogle2faValidation($user, $request)
    {
        if ($request->payment_method_id == 'pay_stack')
            return responseData(true, __('Success'));

        if (empty($user->google2fa_secret))
            throw new Exception(__('Enable Your google2fa first'));

        else {
            if (!empty($request->code)) {
                $google2fa = new Google2FA();

                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
                if ($valid)
                    return $this->responseData(true, __('Success'));
                else
                    throw new Exception(__('Verify code is invalid'));
            } else
                throw new Exception(__('Verify code is required'));
        }
    }

    public function changeStatus($id)
    {
        try {
            return $this->object->changeStatus($id);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("Update Two Factor Status: ", $e->getMessage());
        }
    }
    public function saveTwoFactor($request)
    {
        $response = ['success' => true, 'message' => __('Updated successfully')];
        try {
            if ($this->object->saveTwoFactor($request)) {
                return $response;
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("Update Two Factor Status: ", $e->getMessage());
            return $response;
        }
        return ['success' => false, 'message' => __('Updated failed')];
    }

    // google 2fa secret save
    public function g2fSecretSave($request)
    {
        if (!empty($request->code)) {
            $user = User::find(Auth::id() ?? Auth::guard('api')->id());
            $google2fa = new Google2FA();

            if ($request->remove != 1) {
                $valid = $google2fa->verifyKey($request->google2fa_secret, $request->code);
                if ($valid) {
                    $user->google2fa_secret = $request->google2fa_secret;
                    $user->g2f_enabled = 1;
                    $user->save();
                    return ["success" => true, "message" => __('Google authentication code added successfully')];
                } else {
                    return ["success" => false, "message" => __('Google authentication code is invalid')];
                }
            } else {
                if (!empty($user->google2fa_secret)) {
                    $google2fa = new Google2FA();
                    $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
                    if ($valid) {
                        $user->google2fa_secret = null;
                        $user->g2f_enabled = '0';
                        $user->save();
                        return ["success" => true, "message" => __('Google authentication code remove successfully')];
                    } else {
                        return ["success" => false, "message" => __('Google authentication code is invalid')];
                    }
                } else {
                    return ["success" => false, "message" => __('Google authentication code is invalid')];
                }
            }
            return ["success" => false, "message" => __('Google authentication code is invalid')];
        }
        return ["success" => false, "message" => __('Google authentication code can not be empty')];
    }

    public function updateTwoFactor($request, $users = null)
    {
        try {
            $response = ['success' => true, 'message' => __('Security updated successfully')];
            $check = false;
            $settings = settings();
            //            $check2fa = true;
            $user = $users == null ? Auth::user() : $users;
            $mandatory = ($user->role == USER_ROLE_ADMIN)
                ? filter_var($settings["two_factor_admin"], FILTER_VALIDATE_BOOLEAN)
                : filter_var($settings["two_factor_user"], FILTER_VALIDATE_BOOLEAN);


            //        if(!empty($request->code_type))
            //        {
            //            $checkOtp = new OtpCheck();
            //            $res = $this->userOtpVerification($request,Auth::user());
            //            if(!$res['success'])
            //                return redirect()->back()->with('dismiss',$res['message']);
            //            $check2fa = true;
            //        }

            if (isset($request->phone)) {
                if ($request->phone == ENABLE) {
                    if ($user->phone_verified) {
                        $user->phone_enabled = '1';
                        $check = true;
                    } else {
                        $response = ['success' => false, 'message' => __('Phone 2FA need verified phone')];
                    }
                } else {
                    if ($user->email_enabled == DISABLE && $user->g2f_enabled == DISABLE)
                        $check = false;
                    $user->phone_enabled = '0';
                }
            }
            //            else {
            //                if ($check2fa) {
            //                    $user->phone_enabled = false;
            //                }
            //            }

            if (isset($request->email)) {
                if ($request->email == ENABLE) {
                    $user->email_enabled = '1';
                    $check = true;
                } else {
                    if ($user->phone_enabled == DISABLE && $user->g2f_enabled == DISABLE)
                        $check = false;
                    $user->email_enabled = '0';
                }
            }

            if (isset($request->google)) {
                if ($request->google == ENABLE) {
                    if (!empty(Auth::user()->google2fa_secret)) {
                        $user->g2f_enabled = '1';
                        $check = true;
                    } else {
                        $response = ['success' => false, 'message' => __('For using google two factor authentication,please setup your authentication')];
                    }
                } else {
                    if ($user->phone_enabled == DISABLE && $user->email_enabled == DISABLE)
                        $check = false;
                    $user->g2f_enabled = '0';
                }
            }

            if ($check || !$mandatory) {
                $user->update();
            } else {
                return ['success' => false, 'message' => __('You have to active minimum 1 two factor security')];
            }
        } catch (\Exception $e) {
            storeException("Update Two Factor: ", $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }
    public function userOtpVerification($request, $user)
    {
        $data = [];
        $response = ['success' => true, 'message' => __('Otp verification success'), 'data' => $data];
        try {
            if (empty($request->code)) {
                return ['success' => false, 'message' => __('Verify code is required'), 'data' => $data];
            }
            if (empty($request->code_type)) {
                return ['success' => false, 'message' => __('Code type is required'), 'data' => $data];
            }
            if (!in_array($request->code_type, [GOOGLE_AUTH, EMAIL_AUTH, PHONE_AUTH])) {
                return ['success' => false, 'message' => __('Invalid code type'), 'data' => $data];
            }
            $valid = false;
            if ($request->code_type == GOOGLE_AUTH) {
                $google2fa = new Google2FA();
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
                if (!$valid) {
                    $response = [
                        'success' => false,
                        'message' => __('Google two factor authentication is invalid'),
                        'data' => $data
                    ];
                    return $response;
                }
            }
            if ($request->code_type == PHONE_AUTH) {
                //                $valid = $user->otp_code == $request->code;
                $valid = $this->checkOtpCode($request->code);
                if (!$valid) {
                    $response = [
                        'success' => false,
                        'message' => __('Verify code not match'),
                        'data' => $data
                    ];
                    return $response;
                }
            }
            if ($request->code_type == EMAIL_AUTH) {
                $valid = $user->otp_code == $request->code;
                if (!$valid) {
                    $response = [
                        'success' => false,
                        'message' => __('Verify code not match'),
                        'data' => $data
                    ];
                    return $response;
                }
            }
        } catch (\Exception $e) {
            storeException('userOtpVerification', $e->getMessage());
            $response = ['success' => false, 'message' => __('Otp verification process failed')];
        }
        return $response;
    }
    public function checkOtpCode($otp)
    {
        $now = Carbon::now();
        $user = User::find(Auth::id() ?? Auth::guard('api')->id());
        $time = Carbon::createFromFormat('Y-m-d H:i:s', $user->updated_at);
        $diff = $time->diffInMinutes($now);
        if ($diff < 15 && $otp == $user->otp_code) {
            Session::put('g2f_checked', true);
            return true;
        }
        return false;
    }
    public function sendOtpCode()
    {
        $sms = new SmsService();
        $user = Auth::user() ?? Auth::guard("api")->user();
        DB::beginTransaction();
        try {
            if (!$user->phone_verified) return false;
            $otp = rand(111111, 999999);
            $phone = '+' . $user->phone;
            $title = settings("app_title");
            $title = ($title !== null || $title !== '') ? $title : 'Tradexpro';
            $message = __('Your ' . $title . ' verification code is : ') . $otp;
            User::find($user->id)->update(['otp_code' => $otp]);
            $sms->send($phone, $message);
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('sending otp to phone', $e->getMessage());
            return false;
        }
        DB::commit();
        return true;
    }

    public function sendOtpCodeEmail()
    {
        $appTitle = settings("app_title");
        $companyName = isset($appTitle) && !empty($appTitle) ? $appTitle : __('Tradexpro');
        $subject = __('Email Authentication | :companyName', ['companyName' => $companyName]);
        $data['mailTemplate'] = emailTemplateName('two_factor_email');
        $data['name'] = '';
        $data['subject'] = $subject;
        $user = Auth::user() ?? Auth::guard("api")->user();
        DB::beginTransaction();
        try {
            $otp = rand(111111, 999999);
            $email =  $user->email;
            User::find($user->id)->update(['otp_code' => $otp]);
            $data['key'] = $otp;
            $data['to'] = $user->email;
            dispatch(new MailSend($data))->onQueue('send-mail-otp');
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('sending otp to Email', $e->getMessage());
            storeException('sending otp to Email', $e->getTraceAsString());
            return false;
        }
        DB::commit();
        return true;
    }

    public function twoFactorList($user)
    {
        try {
            $setting = settings();
            $two_factor = $setting["two_factor_list"] ?? '{}';
            $two_factor = json_decode($two_factor, true);
            $twoFactorArray = [];
            if (in_array(GOOGLE_AUTH, $two_factor)) $twoFactorArray[GOOGLE_AUTH] = getTwoFactorArray(GOOGLE_AUTH);
            if (in_array(EMAIL_AUTH, $two_factor)) $twoFactorArray[EMAIL_AUTH] = getTwoFactorArray(EMAIL_AUTH);
            if (in_array(PHONE_AUTH, $two_factor)) $twoFactorArray[PHONE_AUTH] = getTwoFactorArray(PHONE_AUTH);
        } catch (\Exception $e) {
            storeException('two_factor_list API: ', $e->getMessage());
            return ["success" => false, "message" => __("Something went wrong"), 'data' => []];
        }
        return ["success" => true, "message" => __("Two factor list get successfully"), 'data' => $twoFactorArray];
    }

    public static function twoFactorListCommonSetting($setting)
    {
        try {
            $two_factor = $setting["two_factor_list"] ?? '{}';
            $two_factor = json_decode($two_factor, true);
            $twoFactorArray = [];
            if (in_array(GOOGLE_AUTH, $two_factor)) $twoFactorArray[GOOGLE_AUTH] = getTwoFactorArray(GOOGLE_AUTH);
            if (in_array(EMAIL_AUTH, $two_factor)) $twoFactorArray[EMAIL_AUTH] = getTwoFactorArray(EMAIL_AUTH);
            if (in_array(PHONE_AUTH, $two_factor)) $twoFactorArray[PHONE_AUTH] = getTwoFactorArray(PHONE_AUTH);
        } catch (\Exception $e) {
            storeException('two_factor_list API: ', $e->getMessage());
        }
        return $twoFactorArray;
    }
}
