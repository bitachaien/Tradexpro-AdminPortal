<?php

namespace App\Http\Services;

use App\Jobs\BulkWalletGenerateJob;
use App\User;
use Carbon\Carbon;
use Google\Client;
use App\Model\SocialLogin;
use Google\Service\Oauth2;

use App\Model\AffiliationCode;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\DB;
use App\Model\UserVerificationCode;
use Azimo\Apple\Api\AppleApiClient;
use Azimo\Apple\Auth\Jwt\JwtParser;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Azimo\Apple\Auth\Jwt\JwtVerifier;
use Azimo\Apple\Auth\Jwt\JwtValidator;
use Illuminate\Support\Facades\Session;
use App\Http\Repositories\AuthRepositories;
use Azimo\Apple\Api\Factory\ResponseFactory;
use App\Http\Requests\Api\SocialLoginRequest;
use App\Http\Repositories\AffiliateRepository;
use Azimo\Apple\Auth\Factory\AppleJwtStructFactory;
use Azimo\Apple\Auth\Service\AppleJwtFetchingService;

class AuthService
{
    public $repository;
    public $emailService;
    public function __construct()
    {
        $this->repository = new AuthRepositories;
        $this->emailService = new MailService;
    }

    // sign up process
    public function signUpProcess($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => (object) []];
        DB::beginTransaction();
        $parentUserId = 0;
        try {
            if ($request->has('ref_code')) {
                $parentUser = AffiliationCode::where('code', $request->ref_code)->first();
                if (!$parentUser) {
                    return ['success' => false, 'message' => __('Invalid referral code.'), 'data' => (object) []];
                } else {
                    $parentUserId = $parentUser->user_id;
                }
            }
            $mail_key = $this->repository->generate_email_verification_key();

            $setting = defined('ADMIN_SETTINGS_ARRAY')
                ? ADMIN_SETTINGS_ARRAY
                : allsetting("signup_email_verification");

            $userData = [
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'role' => USER_ROLE_USER,
                'password' => Hash::make($request['password']),
            ];

            if (isset($request['phone']))
                $userData['phone'] = $request['phone'];

            if (!($setting['signup_email_verification'] ?? false)) {
                $userData["is_verified"] = STATUS_ACTIVE;
            }

            $user = $this->repository->create($userData);
            if ($user) {
                $userVerificationData = [
                    'user_id' => $user->id,
                    'code' => $mail_key,
                    'expired_at' => date('Y-m-d', strtotime('+15 days'))
                ];
                $userVerification = $this->repository->createUserVerification($userVerificationData);

                BulkWalletGenerateJob::dispatch($user->id, WALLET_GENERATE_BY_USER);

                if ($parentUserId > 0) {
                    $referralRepository = new AffiliateRepository;
                    $createdReferral = $referralRepository->createReferralUser($user->id, $parentUserId);
                }

                DB::commit();
                if ($setting['signup_email_verification'] ?? true)
                    $this->sendEmail($user, $mail_key, 'verify');
                // all good
                $response = [
                    'success' => true,
                    'message' => ($setting['signup_email_verification'] ?? true) ? __('Sign up successful. Please verify your email') : __('Sign up successful.'),
                    'data' => $user
                ];
            }

        } catch (\Exception $e) {
            DB::rollback();
            storeException('signUpProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => (object) []];
        }

        return $response;
    }


    // send verify email
    public function sendVerifyemail($user, $mail_key)
    {
        try {
            $userName = $user->first_name . ' ' . $user->last_name;
            $userEmail = $user->email;
            $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
            $subject = __('Email Verification 5 | :companyName', ['companyName' => $companyName]);
            $data['user'] = $user;
            $data['token'] = $mail_key;
            if ($user->role == USER_ROLE_ADMIN) {
                $template = emailTemplateName('verifyWeb');
                // $template = 'email.verifyWeb';
            } else {
                $template = emailTemplateName('verifyapp');
                // $template = 'email.verifyapp';
            }
            $this->emailService->send($template, $data, $userEmail, $userName, $subject);
        } catch (\Exception $e) {
            storeException('sendVerifyemail', $e->getMessage());
        }
    }

    // password change process
    public function changePassword($request)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $data = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $user = Auth::user();
            if (!Hash::check($request->password, $user->password)) {

                $data['message'] = __('Old password doesn\'t match');
                return $data;
            }
            if (Hash::check($request->new_password, $user->password)) {
                $data['message'] = __('You already used this password');
                return $data;
            }

            $user->password = Hash::make($request->new_password);

            $user->save();
            //         DB::table('oauth_access_tokens')
//             ->where('user_id', Auth::id())->where('id', '!=', Auth::user()->token()->id)
//             ->delete();

            return ['success' => true, 'message' => __('Password change successfully')];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    // send forgot mail process
    public function sendForgotMailProcess($request)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $response = ['success' => false, 'message' => __('Something went wrong')];
        $user = User::where(['email' => $request->email])->first();

        if ($user) {
            DB::beginTransaction();
            try {
                $key = randomNumber(6);
                $existsToken = User::join('user_verification_codes', 'user_verification_codes.user_id', 'users.id')
                    ->where('user_verification_codes.user_id', $user->id)
                    ->whereDate('user_verification_codes.expired_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->first();
                if (!empty($existsToken)) {
                    $token = $existsToken->code;
                } else {
                    UserVerificationCode::create(['user_id' => $user->id, 'code' => $key, 'expired_at' => date('Y-m-d', strtotime('+15 days')), 'status' => STATUS_PENDING]);
                    $token = $key;
                }

                $this->sendEmail($user, $token);
                $data['message'] = __('Mail sent successfully to ') . $user->email . __(' with password reset code.');
                $data['success'] = true;
                Session::put(['resend_email' => $user->email]);
                DB::commit();

                $response = ['success' => true, 'message' => $data['message']];
            } catch (\Exception $e) {
                DB::rollBack();
                storeException('sendForgotMailProcess', $e->getMessage());
                $response = ['success' => false, 'message' => __('Something went wrong')];
            }
        } else {
            $response = ['success' => false, 'message' => __('Email not found')];
        }

        return $response;
    }

    // send forgot mail
    public function sendEmail($user, $mail_key, $type = null)
    {
        try {
            $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
            $user_data = [
                'user' => $user,
                'token' => $mail_key,
            ];
            $userName = $user->first_name . ' ' . $user->last_name;
            $userEmail = $user->email;
            if (!empty($type) && $type == 'verify') {
                $subject = __('Email Verify | :companyName', ['companyName' => $companyName]);
                if ($user->role == USER_ROLE_ADMIN) {
                    $template = emailTemplateName('verifyWeb');
                    // $template = 'email.verifyWeb';
                } else {
                    $template = emailTemplateName('verifyapp');
                    // $template = 'email.verifyapp';
                }
                $this->emailService->send($template, $user_data, $userEmail, $userName, $subject);
            } else {
                $subject = __('Forgot Password | :companyName', ['companyName' => $companyName]);
                $template = emailTemplateName('password_reset');
                $this->emailService->send($template, $user_data, $userEmail, $userName, $subject);
            }

        } catch (\Exception $e) {
            storeException('sendEmail ' . $type, $e->getMessage());
        }
    }

    // reset password process
    public function passwordResetProcess($request)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $vf_code = UserVerificationCode::where(['code' => $request->token, 'status' => STATUS_PENDING, 'type' => CODE_TYPE_EMAIL])
                ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                ->first();

            if (!empty($vf_code)) {
                $user = User::where(['id' => $vf_code->user_id, 'email' => $request->email])->first();
                if (empty($user)) {
                    $response = ['success' => false, 'message' => __('User not found')];
                }
                $data_ins['password'] = hash::make($request->password);
                $data_ins['is_verified'] = STATUS_SUCCESS;
                if (!Hash::check($request->password, User::find($vf_code->user_id)->password)) {

                    User::where(['id' => $vf_code->user_id])->update($data_ins);
                    UserVerificationCode::where(['id' => $vf_code->id])->delete();

                    $data['success'] = 'success';
                    $data['message'] = __('Password Reset Successfully');

                    $response = ['success' => true, 'message' => $data['message']];
                } else {
                    $data['success'] = 'dismiss';
                    $data['message'] = __('You already used this password');
                    $response = ['success' => false, 'message' => $data['message']];
                }
            } else {
                $data['success'] = 'dismiss';
                $data['message'] = __('Invalid code');

                $response = ['success' => false, 'message' => $data['message']];
            }
        } catch (\Exception $e) {
            storeException('passwordResetProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }

        return $response;
    }

    // add new user process
    public function addNewUser($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        DB::beginTransaction();
        try {
            $userData = [
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'role' => USER_ROLE_USER,
                'phone' => $request->phone,
                'status' => STATUS_SUCCESS,
                'is_verified' => STATUS_SUCCESS,
                'password' => Hash::make(randomString(8)),
            ];
            $user = $this->repository->create($userData);
            if ($user) {
                BulkWalletGenerateJob::dispatch($user->id, WALLET_GENERATE_BY_USER);

                $key = randomNumber(6);
                $existsToken = User::join('user_verification_codes', 'user_verification_codes.user_id', 'users.id')
                    ->where('user_verification_codes.user_id', $user->id)
                    ->whereDate('user_verification_codes.expired_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->first();

                if (!empty($existsToken)) {
                    $token = $existsToken->code;
                } else {
                    $s = UserVerificationCode::create(['user_id' => $user->id, 'code' => $key, 'expired_at' => date('Y-m-d', strtotime('+15 days')), 'status' => STATUS_PENDING]);
                    $token = $key;
                }

                $user_data = [
                    'email' => $user->email,
                    'user' => $user,
                    'token' => $token,
                ];
                DB::commit();
                try {
                    $userName = $user->first_name . ' ' . $user->last_name;
                    $userEmail = $user->email;
                    $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
                    $subject = __('Change Password | :companyName', ['companyName' => $companyName]);
                    $template = emailTemplateName('password_reset');
                    $this->emailService->send($template, $user_data, $userEmail, $userName, $subject);

                    $data['message'] = __('New user created and Mail sent successfully to ') . $user->email . __(' with password reset Code.');
                    $data['success'] = true;
                    Session::put(['resend_email' => $user->email]);

                    $response = ['success' => true, 'message' => $data['message']];
                } catch (\Exception $e) {
                    $response = ['success' => true, 'message' => __('New user created successfully but Mail not sent')];
                }
            } else {
                $response = ['success' => false, 'message' => __('Failed to create user')];
            }

        } catch (\Exception $e) {
            DB::rollback();
            storeException('addNewUser', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }

        return $response;
    }
    //resend verification code to mail
    public function resendVerifyEmailCode($request)
    {
        try {
            $mail_key = $this->repository->generate_email_verification_key();
            $user = User::where(['email' => $request->email])->first();
            if ($user) {
                $userVerificationData = [
                    'user_id' => $user->id,
                    'code' => $mail_key,
                    'expired_at' => date('Y-m-d', strtotime('+15 days'))
                ];

                $this->repository->createUserVerification($userVerificationData);

                $this->sendVerifyemail($user, $mail_key);

            }
            $response = ['success' => true, 'message' => 'Verification Code send to your mail'];
        } catch (\Exception $e) {
            storeException('resendVerifyEmailCode', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    // verify email
    public function verifyEmailProcess($request)
    {
        $data = ['success' => false, 'message' => __('Something went wrong')];
        try {
            if ($request->token) {
                $token = explode('email', $request->token);
                $user = User::where(['email' => decrypt($token[1])])->first();
            } else {
                $user = User::where(['email' => $request->email])->first();
            }
            if (!empty($user)) {
                if ($request->token) {
                    $verify = UserVerificationCode::where(['user_id' => $user->id])
                        ->where('code', decrypt($token[0]))
                        ->where(['status' => STATUS_PENDING, 'type' => CODE_TYPE_EMAIL])
                        ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                        ->first();
                } else {
                    $verify = UserVerificationCode::where(['user_id' => $user->id])
                        ->where('code', $request->verify_code)
                        ->where(['status' => STATUS_PENDING, 'type' => CODE_TYPE_EMAIL])
                        ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                        ->first();
                }

                if ($verify) {
                    $check = $user->update(['is_verified' => STATUS_SUCCESS]);
                    if ($check) {
                        UserVerificationCode::where(['user_id' => $user->id, 'id' => $verify->id])->delete();
                        $data = ['success' => true, 'message' => __('Verify successful,you can login now')];
                    }
                } else {
                    Auth::logout();
                    $data = ['success' => false, 'message' => __('Your verify code was expired,you can generate new one')];
                }
            } else {
                $data = ['success' => false, 'message' => __('Your email not found or token expired')];
            }
        } catch (\Exception $e) {
            storeException('signUpProcess', $e->getMessage());
            $data = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $data;
    }

    // g2fa verify process
    public function g2fVerifyProcess($request)
    {
        try {
            $user = User::where('id', $request->user_id)->first();
            if ($request->code) {
                $google2fa = new Google2FA();
                $google2fa->setAllowInsecureCallToGoogleApis(true);
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code, 8);

                if ($valid) {
                    Session::put('g2f_checked', true);
                    $token = $user->createToken($user->email)->accessToken;
                    $data['access_token'] = $token;
                    $data['access_type'] = 'Bearer';
                    $data['user'] = $user;
                    $data['user']->photo = show_image_path($user->photo, IMG_USER_PATH);
                    $data = ['success' => true, 'message' => __('Code verify success'), 'data' => $data];
                } else {
                    $data = ['success' => false, 'message' => __('Code doesn\'t match'), 'data' => []];
                }
            } else {
                $data = ['success' => false, 'message' => __('Code is required'), 'data' => []];
            }

        } catch (\Exception $e) {
            storeException('g2fVerifyProcess', $e->getMessage());
            $data = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $data;
    }

    public function loginWithSocialPlatform(SocialLoginRequest $request)
    {
        try {
            $requestUser = $this->validateSocialLoginResponse($request);
            if (!$requestUser) {
                return responseData(false, __("Invalid social user request"));
            }
            if (empty($requestUser->email)) {
                return responseData(false, __("Email not found in your social account"));
            }

            // Check if user exists and has matching social login
            $user = User::where("email", $requestUser->email)->first();
            $existingSocialLogin = SocialLogin::where([
                "user_id" => @$user->id,
                "login_type" => $requestUser->login_type
            ])->exists();

            // If existing user with social login found, log them in
            if ($user && $existingSocialLogin) {
                return $this->loginByUser($user);
            }

            DB::beginTransaction();

            // Try to use existing user or create new one
            if (!$user) {
                $user = $this->createNewUser($requestUser);
            }

            // If we have a valid user and can create social login
            if ($user && $this->createNewSocialLogin($requestUser, $user)) {
                DB::commit();
                return $this->loginByUser(User::find($user->id));
            }
            return responseData(false, __("Login failed!"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException("loginWithSocialPlatform", $e->getMessage() . $e->getTraceAsString());
            return responseData(false, __("Login failed! error occurred"));
        }
    }

    public function loginByUser(User $user)
    {
        if ($user->role == USER_ROLE_USER) {
            Auth::login($user);
            if (Auth::check()) {
                $token = $user->createToken($user->email)->accessToken;
                //Check email verification
                if ($user->status == STATUS_SUCCESS) {
                    if (!empty($user->is_verified)) {
                        $data['success'] = true;
                        $data['message'] = __('Login successful');
                        $data['email_verified'] = $user->is_verified;
                        create_coin_wallet(Auth::id());

                        $data['access_token'] = $token;
                        $data['access_type'] = 'Bearer';

                        $data['user'] = $user;
                        $data['user']->photo = show_image_path($user->photo, IMG_USER_PATH);
                        createUserActivity(Auth::user()->id, USER_ACTIVITY_LOGIN);

                        return $data;
                    }
                } elseif ($user->status == STATUS_SUSPENDED) {
                    $data['email_verified'] = 1;
                    $data['success'] = false;
                    $data['message'] = __("Your account has been suspended. please contact support team to active again");
                    Auth::logout();
                    return $data;
                } elseif ($user->status == STATUS_DELETED) {
                    $data['email_verified'] = 1;
                    $data['success'] = false;
                    $data['message'] = __("Your account has been deleted. please contact support team to active again");
                    Auth::logout();
                    return $data;
                } elseif ($user->status == STATUS_PENDING) {
                    $data['email_verified'] = 1;
                    $data['success'] = false;
                    $data['message'] = __("Your account has been pending for admin approval. please contact support team to active again");
                    Auth::logout();
                    return $data;
                } elseif ($user->status == STATUS_USER_DEACTIVATE) {
                    $data['email_verified'] = 1;
                    $data['success'] = false;
                    $data['message'] = __("Your account has been deactivated. please contact support team to active again");
                    Auth::logout();
                    return $data;
                } else {
                    $data['success'] = false;
                    $data['message'] = __("User not found!");
                    return $data;
                }
            } else {
                $data['success'] = false;
                $data['message'] = __("Email or Password doesn't match");
                return $data;
            }
        } else {
            $data['success'] = false;
            $data['message'] = __("You have no login access");
            Auth::logout();
            return $data;
        }
    }

    public function createNewUser($request)
    {
        $userName = explode(" ", $request->name);
        $userData = [
            "first_name" => $userName[0],
            "last_name" => ($userName[1] ?? "") . (isset($userName[2]) ? " $userName[2]" : ''),
            "email" => $request->email,
            "is_verified" => STATUS_ACCEPTED,
            "password" => Hash::make(rand(111111, 999999)),
            "role" => USER_ROLE_USER,
            "status" => STATUS_SUCCESS,
            "nickname" => trim(strtolower(implode("", $userName))),
        ];

        try {
            return User::create($userData);
        } catch (\Exception $e) {
            storeException("loginWithSocialPlatform createNewUser", $e->getMessage());
            return false;
        }
    }
    public function createNewSocialLogin($request, User $user)
    {
        $socialLoginData = [
            "user_id" => $user->id,
            "userID" => $request->userID,
            "login_type" => $request->login_type,
            "email" => $request->email,
            "access_token" => $request->access_token,
        ];

        try {
            return SocialLogin::create($socialLoginData);
        } catch (\Exception $e) {
            storeException("loginWithSocialPlatform createNewSocialLogin", $e->getMessage());
            return false;
        }
    }

    public function validateSocialLoginResponse($request)
    {
        $setting = defined("ADMIN_SETTINGS") ? ADMIN_SETTINGS : settings();

        if ($request->login_type == LOGIN_WITH_APPLE) {

            $apple_id = (checkUserAgent($request) == 'ios')
                ? $setting['social_login_app_apple_id'] ?? ""
                : $setting['social_login_apple_id'] ?? "";

            $appleJwtFetchingService = new AppleJwtFetchingService(
                new JwtParser(new \Lcobucci\JWT\Token\Parser(new \Lcobucci\JWT\Encoding\JoseEncoder())),
                new JwtVerifier(
                    new AppleApiClient(
                        new HttpClient(
                            [
                                'base_uri' => 'https://appleid.apple.com',
                                'timeout' => 5,
                                'connect_timeout' => 5,
                            ]
                        ),
                        new ResponseFactory()
                    ),
                    new \Lcobucci\JWT\Validation\Validator(),
                    new \Lcobucci\JWT\Signer\Rsa\Sha256()
                ),
                new JwtValidator(
                    new \Lcobucci\JWT\Validation\Validator(),
                    [
                        new \Lcobucci\JWT\Validation\Constraint\IssuedBy('https://appleid.apple.com'),
                        new \Lcobucci\JWT\Validation\Constraint\PermittedFor($apple_id),
                    ]
                ),
                new AppleJwtStructFactory()
            );
            $response = $appleJwtFetchingService->getJwtPayload($request->access_token);
            $name = "Your Name";
            $id = $response->getSub();
            $email = $response->getEmail();
            if (filled($email)) {
                return (object) [
                    "email" => $email,
                    "name" => $name,
                    "userID" => $id,
                    "access_token" => $request->access_token,
                    "login_type" => $request->login_type,
                ];
            }

            return false;
        }

        if ($request->login_type == LOGIN_WITH_FACEBOOK) {
            $url = "https://graph.facebook.com/v20.0/me?access_token=$request->access_token&debug=all&fields=id,name,email,picture&format=json&method=get&origin_graph_explorer=1&pretty=0&suppress_http_code=1&transport=cors";

            $client = new HttpClient();
            $fbResponse = $client->get($url);
            $userResponse = json_decode($fbResponse->getBody(), true);

            if ($userResponse) {
                return (object) [
                    "email" => $userResponse["email"],
                    "name" => $userResponse["name"],
                    "userID" => $userResponse["id"],
                    "access_token" => $request->access_token,
                    "login_type" => $request->login_type,
                ];
            }
            return false;
        }

        if ($request->login_type == LOGIN_WITH_GOOGLE) {
            $CLIENT_ID = $setting['social_login_google_client_id'] ?? "";

            $client = new Client(['client_id' => $CLIENT_ID]);

            $user_agent = checkUserAgent($request);
            if (in_array($user_agent, ['android', 'ios'])) {
                $client->setAccessToken($request->access_token);
                $oauth2 = new Oauth2($client);
                $userResponse = $oauth2->userinfo->get();
            } else {
                $userResponse = $client->verifyIdToken($request->access_token);
            }
            if ($userResponse) {
                return (object) [
                    "email" => $userResponse["email"],
                    "name" => $userResponse["name"],
                    "userID" => $userResponse["sub"] ?? $userResponse["id"],
                    "access_token" => $request->access_token,
                    "login_type" => $request->login_type,
                ];
            }
            return false;
        }
        return false;
    }
}
