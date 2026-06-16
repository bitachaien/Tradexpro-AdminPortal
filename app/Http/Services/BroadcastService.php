<?php


namespace App\Http\Services;


use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Pusher\Pusher;

class BroadcastService
{
    protected $broadcast;

    public function __construct()
    {
        $config = config('broadcasting.connections.pusher');
        $this->broadcast = new Pusher($config['key'], $config['secret'], $config['app_id'], $config['options']);
    }

    public function broadCast(string $channelName, string $eventName, $data)
    {
//        $txt = 'chanel='.$channelName. '   event='.$eventName.json_encode($data,1);
//        file_put_contents(base_path().'/logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
         return $this->broadcast->trigger($channelName, $eventName, $data);
    }

    /**
     * return user data by following user id
     * @param $userId integer
     * @param null $deviceId string
     * @return mixed json
     */
    public function _getUserDataById($userId,$deviceId)
    {
        $data = User::where('id', $userId)
            ->select('users.id', 'users.first_name', 'users.last_name'
                , 'users.phone', 'users.email', 'users.active_status'
                , 'users.email_verification_status', 'users.is_google_auth as two_fa_verification_status', 'users.is_google_auth'
                , 'users.phone_verification_status', 'users.google_verification_status', 'users.is_email_withdrawal_2fa')
            ->first();
        $savedDeviceCodes = DeviceConfirmation::where('user_id', $userId)
            ->where('device_cookie', $deviceId)
            ->first();
        $data['avatar'] = (isset($data->userInfo) && $data->userInfo->avatar) ? getImageUrl( "public/users/images/" . $data->userInfo->avatar) : null;

        if (!empty($savedDeviceCodes)) {
            $data['device_verification_status'] = 1;
        } else {
            $data['device_verification_status'] = env('APP_ENV') == 'production' ? 0 : 1;
        }
        if ($data->two_fa_verification_status) {
            if (!empty($savedDeviceCodes)) {
                $data['two_fa_verification_status'] = $savedDeviceCodes->google_authentication_checked;
            } else {
                $data['two_fa_verification_status'] = 0;
            }
        } else {
            $data['two_fa_verification_status'] = 1;
        }
        return $data;
    }

    /**
     * Check token is valid or not and response {'auth_status'=>in/out, user_data => null/object, token}
     * @param null $deviceUniqueId
     * @return \Illuminate\Http\JsonResponse
     */
    public function authStatus($deviceUniqueId=null){
        try{
            if(Auth::guard('api')->check()){
                return response()->json([
                    'auth_status' => 'in',
                    'user_data' => $this->_getUserDataById(auth()->guard('api')->id(),$deviceUniqueId),
                ]);
            }else{
                return response()->json([
                    'auth_status' => 'out',
                    'user_data' => null,

                ]);
            }

        }catch (\Exception $e){
           return response()->json([
               'auth_status' => 'out',
               'user_data' => null,
               'message' => 'Exception'.getError($e)
            ]);
        }
    }

    /**
     * reset token and assign user id,
     * broadcast reconnect for all connect chanel with new token
     * @param $token string
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetToken($token){
        try{


            DBService::beginTransaction();
            $userToken = WebSocketToken::where(['token'=>$token])->first();


            if (is_null($userToken)){
                return response()->json([
                    'status' => 'false',
                    'message' => 'token.mismatch',
                    'data' => [],
                ]);
            }

            if($userToken->user_id){
                return response()->json([
                    'status' => 'false',
                    'message' => 'already.login',
                    'data' => [],
                ]);
            } else {
                $newToken = Str::uuid();
                $NewUserTokenObj = new WebSocketToken();
                $NewUserTokenObj->user_id = Auth::id();
                $NewUserTokenObj->token = $newToken;
                $NewUserTokenObj->save();
                DBService::commit();
                return response()->json([
                    'status' => 'true',
                    'token_type' => 'new',
                    'token' => $newToken,
                ]);
            }

        }catch (\Exception $e){


            DBService::rollBack();
            return response()->json([
                'status' => 'false',
                'message' => 'error',
                'data' => [],
            ]);
        }
    }

    /**
     * broadcast data for user Data Change
     * @param $token string
     * @param $deviceCookie string
     * @return array|bool
     */
    function broadcastUserDataChange($token, $deviceCookie){
        //$authData = WebSocketToken::where(['token'=>$token])->first();
        $data = $this->_getUserDataById(Auth::id(), $deviceCookie);
        return $this->broadCast('private-chanel.'.$token,'userDataChange', $data);
    }
    /**
     * broadcast for reconnect
     * @param $token string
     * @return array|bool|\Illuminate\Http\JsonResponse
     */
    public function socketReconnect($token, $deviceCookie){
        try{
            $tokenObj = WebSocketToken::where(['token'=> $token])->first();
           $x = $this->broadCast($tokenObj->token,'reconnect',[]);



        }catch (\Exception $e){
            storeException('socketReconnect ex',json_encode($e));
            return response()->json([
                'status' => 'false',
                'message' => 'error',
                'data' => [],
            ]);
        }
    }

    /**
     * logout and remove token and broadcast
     * @param $token
     * @param $deviceCookie
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout($deviceId) {

    }
}
