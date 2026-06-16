<?php
namespace App\Http\Repositories;

use App\Http\Services\Logger;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\ActivityLog;
use Illuminate\Support\Collection;

class UserRepository
{
    protected $affiliateRepository;

    public function __construct(private User $model)
    {
        $this->affiliateRepository = new AffiliateRepository();
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
    }

    public static function createUser($request){

        $data=[
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
            'role'=>USER_ROLE_USER,
        ];
       return User::create($data);
    }
    public static function updatePassword($request,$user_id){
       return User::where(['id'=>$user_id])->update(['password'=>bcrypt($request->password)]);
    }
    public static function apiUpdatePassword($request,$user_id){
        return User::where(['id'=>$user_id])->update(['password'=>bcrypt($request->new_password)]);
    }

    // update user profile
    public function profileUpdate($request, $user_id)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $response['success'] = false;
        $response['user'] = (object)[];
        $response['message'] = __('Invalid Request');
        try {
            $user = User::find($user_id);
            $userData = [];
            if ($user) {

                $userData = [
                    'nickname' => $request['nickname'],
                    'first_name' => $request['first_name'],
                    'last_name' => $request['last_name'],
                    'phone' => $request['phone'],
                ];
                if (!empty($request['country'])) {
                    $userData['country'] = $request['country'];
                }
                if (!empty($request['gender'])) {
                    $userData['gender'] = $request['gender'];
                }
                if (!empty($request['birth_date'])) {
                    $userData['birth_date'] = $request['birth_date'];
                }

                if (!empty($request['photo'])) {
                    $old_img = '';
                    if (!empty($user->photo)) {
                        $old_img = $user->photo;
                    }
                    $userData['photo'] = uploadFile($request['photo'], IMG_USER_PATH, $old_img);
                }
                if ($user->phone != $request->phone){
                    $userData['phone'] =  $request->phone;
                    $userData['phone_verified'] = 0;
                }

                $affected_row = User::where('id', $user_id)->update($userData);
                if ($affected_row) {
                    $response['success'] = true;
                    $response['user'] = $this->userProfile($user_id)['user'];
                    $response['message'] = __('Profile updated successfully');
                }
            } else {
                $response['success'] = false;
                $response['user'] = (object)[];
                $response['message'] = __('Invalid User');
            }
        } catch (\Exception $e) {
            storeException('profileUpdate', $e->getMessage());
            $response = [
                'success' => false,
                'user' => (object)[],
                'message' => $e->getMessage()
            ];
            return $response;
        }

        return $response;
    }

    public function passwordChange($request, $user_id)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        try {
            $user = User::find($user_id);
            if ($user) {
                $old_password = $request['old_password'];
                if (Hash::check($old_password, $user->password)) {
                    if(!Hash::check($request->password,$user->password)) {
                        $user->password = bcrypt($request['password']);
                        $user->save();
                        $affected_row = $user->save();

                        if (!empty($affected_row)) {
                            $response['success'] = true;
                            $response['message'] = __('Password changed successfully.');
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = __('You already used password');
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = __('Incorrect old password');
                }
            } else {
                $response['success'] = false;
                $response['message'] = __('Invalid user');
            }
        } catch (\Exception $e) {
            storeException('passwordChange', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }
        return $response;
    }

    // user profile
    public function userProfile($user_id)
    {
        try {
            if (isset($user_id)) {
                $user = User::select(
                    'id',
                    'nickname',
                    'first_name',
                    'last_name',
                    'email',
                    'country',
                    'google2fa_secret',
                    'phone_verified',
                    'phone',
                    'gender',
                    'birth_date',
                    'photo',
                    'status',
                    'is_verified',
                    'phone_verified',
                    'created_at',
                    'updated_at',
                    'currency'
                )->findOrFail($user_id);

                $data['user'] = $user;
                $data['user']->photo = imageSrcUser($user->photo,IMG_USER_VIEW_PATH);
                $data['user']->online_status = lastSeenStatus($user->id)['data'];
                $data['user']->country_name = !empty($user->country) ? country(strtoupper($user->country)) : '';
                $data['activityLog'] = ActivityLog::where('user_id', $user_id)->where('action',USER_ACTIVITY_LOGIN)->latest()->take(5)->get();
                $data['success'] = true;
                $data['message'] = __('Successful');
            } else {
                $data= [
                    'success' => false,
                    'user' => (object)[],
                    'message' => __('User not found'),
                ];
            }
        } catch (\Exception $e) {
            storeException('userProfile', $e->getMessage());
            $data = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }
        return $data;
    }

    // user referral info
    public function myReferralInfo()
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        $data['user'] = Auth::user();
        $data['referrals_3'] = DB::table('referral_users as ru1')->where('ru1.parent_id', Auth::user()->id)
            ->Join('referral_users as ru2', 'ru2.parent_id', '=', 'ru1.user_id')
            ->Join('referral_users as ru3', 'ru3.parent_id', '=', 'ru2.user_id')
            ->join('users', 'users.id', '=', 'ru3.user_id')
            ->select('ru3.user_id as level_3', 'users.email','users.first_name as full_name','users.created_at as joining_date')
            ->get();
        $data['referrals_2'] = DB::table('referral_users as ru1')->where('ru1.parent_id', Auth::user()->id)
            ->Join('referral_users as ru2', 'ru2.parent_id', '=', 'ru1.user_id')
            ->join('users', 'users.id', '=', 'ru2.user_id')
            ->select('ru2.user_id as level_2','users.email','users.first_name as full_name','users.created_at as joining_date')
            ->get();
        $data['referrals_1'] = DB::table('referral_users as ru1')->where('ru1.parent_id', Auth::user()->id)
            ->join('users', 'users.id', '=', 'ru1.user_id')
            ->select('ru1.user_id as level_1','users.email','users.first_name as full_name','users.created_at as joining_date')
            ->get();
        $referralUsers = [];

        foreach ($data['referrals_1'] as $level1) {
            $referralUser['id'] = $level1->level_1;
            $referralUser['full_name'] = $level1->full_name;
            $referralUser['email'] = $level1->email;
            $referralUser['joining_date'] = $level1->joining_date;
            $referralUser['level'] = __("Level 1");
            $referralUsers [] = $referralUser;
        }

        foreach ($data['referrals_2'] as $level2) {
            $referralUser['id'] = $level2->level_2;
            $referralUser['full_name'] = $level2->full_name;
            $referralUser['email'] = $level2->email;
            $referralUser['joining_date'] = $level2->joining_date;
            $referralUser['level'] = __("Level 2");
            $referralUsers [] = $referralUser;
        }

        foreach ($data['referrals_3'] as $level3) {
            $referralUser['id'] = $level3->level_3;
            $referralUser['full_name'] = $level3->full_name;
            $referralUser['email'] = $level3->email;
            $referralUser['joining_date'] = $level3->joining_date;
            $referralUser['level'] = __("Level 3");
            $referralUsers [] = $referralUser;
        }
        $data['referrals'] = $referralUsers;

        if (!$data['user']->Affiliate) {
            $created = app(affiliateRepository::class)->create($data['user']->id);
            if ($created < 1) {
                $response = ['success' => false, 'message' => __('Failed to generate new referral code.')];
                return $response;
            }
        }

        $data['url'] = url('') . '/referral-reg?ref_code=' . $data['user']->affiliate->code;

        $response = [
            'success' => true,
            'message' => __('Data get successfully'),
            'url' => $data['url'],
            'my_referral_list' => $data['referrals']
        ];

        return $response;
    }

    public function profileDeleteRequest($request)
    {
        $user = auth()->user();

        $user_details = DB::table('users')->where('id', $user->id)->first();
        if($user_details)
        {
            if(Hash::check($request->password, $user_details->password)){
                if($user_details->delete_request == 0 || $user_details->delete_request == USER_DELETE_REQUEST_STATUS_REJECTED)
                {
                    $user_profile = User::find($user->id);
                    $user_profile->delete_request = USER_DELETE_REQUEST_STATUS_PENDING;
                    $user_profile->delete_request_reason = $request->delete_request_reason;
                    $user_profile->save();

                    $response = responseData(true, __('Profile delete request is submitted!'));
                }else{
                    $response = responseData(false, __('You already requested to delete your profile!'));
                }
            }else{
                $response = responseData(false, __('Your password is incorrect!'));
            }


        }else{
            $response = responseData(false, __('Invalid Request!'));
        }
        return $response;
    }

    public function getUserIdAndEmails(): Collection
    {
        return $this->model->select('id', 'email')->get();
    }
}
