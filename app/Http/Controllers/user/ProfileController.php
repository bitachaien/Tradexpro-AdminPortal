<?php

namespace App\Http\Controllers\user;

use App\Http\Requests\driveingVerification;
use App\Http\Requests\passportVerification;
use App\Http\Requests\resetPasswordRequest;
use App\Http\Requests\UserProfileUpdate;
use App\Http\Requests\verificationNid;
use App\Http\Services\AuthService;
use App\Http\Services\SmsService;
use App\Model\ActivityLog;
use App\Model\VerificationDetails;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    // profile upload image
    public function uploadProfileImage(Request $request)
    {
        if (env('APP_MODE') == 'demo') {
            return redirect()->back()->with('dismiss',__('Currently disable only for demo'));
        }
        $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
        $rules['file_one'] = "required|image|mimes:jpg,jpeg,png,jpg,gif,svg|max:$file_size";

        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) {
            $message = $validator->getMessageBag()->getMessages()['file_one'][0];
            if ($message == 'The file one has invalid image dimensions.')
                $message =  __('Image size must be less than (height:500,width:500)');

            return redirect()->back()->with('dismiss',$message);
        }
        try {
            $img = $request->file('file_one');
            $user_data = (!empty($request->id) ) ? User::find(decrypt($request->id)) : Auth::user();
            if ($img !== null) {
                $photo = uploadFile($img, IMG_USER_PATH, !empty($user_data->photo) ? $user_data->photo : '');
                $user = User::find($user_data->id);
                $user->photo  = $photo;
                $user->save();
                return redirect()->back()->with('success',__('Profile picture uploaded successfully'));
            } else {
                return redirect()->back()->with('dismiss',__('Please input a image'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('dismiss', $e->getMessage());
        }

    }


    // update user profile
    public function userProfileUpdate(UserProfileUpdate $request)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        if (strpos($request->phone, '+') !== false) {
            return redirect()->back()->with('dismiss',__("Don't put plus sign with phone number"));
        }
        $data['first_name'] = $request->first_name;
        $data['last_name'] = $request->last_name;
        $data['country'] = $request->country;
        $data['gender'] = $request->gender;
        $user = (!empty($request->id)) ? User::find(decrypt($request->id)) : Auth::user();
        if ($user->phone != $request->phone){
            $data['phone'] =  $request->phone;
            $data['phone_verified'] = 0;
        }
        $user->update($data);

        return redirect()->back()->with('success',__('Profile updated successfully'));
    }

    // send sms
    public function sendSMS()
    {
        if (!empty(Auth::user()->phone)) {
            if (!empty(Cookie::get('code'))) {
                $key = Cookie::get('code');
            } else {
                $key = randomNumber(8);
            }
            $minute = 100;
            try {
                Cookie::queue(Cookie::make('code', $key, $minute * 60));
                $text = __('Your verification code id ') . ' ' . $key;
                $number = Auth::user()->phone;
                if (settings('sms_getway_name') == 'twillo') {
                    $sendSms = app(SmsService::class)->send("+".$number, $text);
                }

                return redirect()->back()->with('success', __('We sent a verification code in your phone please input this code in this box.'));
            } catch (\Exception $exception) {
                Cookie::queue(Cookie::forget('code'));
                return redirect()->back()->with('dismiss', __('Please contact your system admin,Something went wrong.'));
            }
        } else {
            return redirect()->back()->with('dismiss', 'you should add your phone number first.');
        }
    }

    // phone verification process
    public function phoneVerify(Request $request)
    {
        if (!empty($request->code)) {
            $cookie = Cookie::get('code');
            if (!empty($cookie)) {
                if ($request->code == $cookie) {
                    $user = User::find(Auth::id());
                    $user->phone_verified = 1;
                    $user->save();
                    Cookie::queue(Cookie::forget('code'));

                    return redirect()->back()->with('success',__('Phone verified successfully.'));
                } else {
                    return redirect()->back()->with('dismiss',__('You entered wrong OTP.'));
                }
            } else {
                return redirect()->back()->with('dismiss',__('Your OTP is expired.'));
            }
        } else {
            return redirect()->back()->with('dismiss',__("OTP can't be empty."));
        }
    }


    public function changePasswordSave(resetPasswordRequest $request)
    {
        $service = new AuthService();
        $change = $service->changePassword($request);
        if ($change['success']) {
            return redirect()->back()->with('success',$change['message']);
        } else {
            return redirect()->back()->with('dismiss',$change['message']);
        }
    }
}
