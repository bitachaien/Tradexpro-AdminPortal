<?php 
namespace App\Http\Services;

use App\Model\ThirdPartyKycDetails;

class ThirdPartyKYCService {

    public function verifiedPersonaKYC($request)
    {
        $user = auth()->user();
        
        $settings = allsetting(['persona_kyc_api_key','persona_kyc_version']);
        $headers = [
            'Authorization: Bearer '.$settings['PERSONA_KYC_API_KEY'],
            'Persona-Version: '.$settings['PERSONA_KYC_VERSION'],
            'accept: application/json',
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://withpersona.com/api/v1/inquiries/'.$request->inquiry_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $result = json_decode($result);
        curl_close($ch);

        if(isset($result->data))
        {
            $status = STATUS_PENDING;
            if($result->data->attributes->status == 'completed')
            {
                $status = STATUS_SUCCESS;
            }
            $thirdPartyKYCDetails = ThirdPartyKycDetails::where('user_id', $user->id)->where('kyc_type',KYC_TYPE_PERSONA)->first();
            if(isset($thirdPartyKYCDetails))
            {
                $thirdPartyKYCDetails->is_verified = $status;
                $thirdPartyKYCDetails->key = $request->inquiry_id;
                $thirdPartyKYCDetails->save();
            }else{
                $thirdPartyKYCDetails = new ThirdPartyKycDetails;
                $thirdPartyKYCDetails->user_id = $user->id;
                $thirdPartyKYCDetails->kyc_type = KYC_TYPE_PERSONA;
                $thirdPartyKYCDetails->is_verified = $status;
                $thirdPartyKYCDetails->key = $request->inquiry_id;
                $thirdPartyKYCDetails->save();
            }
            $response = ['success'=>true, 'message'=>'Verification ' .$result->data->attributes->status];
        }else{
            $response = ['success'=>false, 'message'=> $result->errors[0]->title];
        }

        return $response;

    }
}