<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\VerificationDetails;

class KYCVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next,$kycVerificationType)
    {
        $user = Auth::user();
        if (!empty($user))
        {
            $checkKYCEnabledType = getEnabledKYCType($kycVerificationType);
            if($checkKYCEnabledType['success'])
            {
                $verification_type = $checkKYCEnabledType['data'];
                if($verification_type == KYC_TYPE_PERSONA)
                {
                    $checkVerificationStatus = checkThirdPartyVerificationStatus($user);
                    if(!$checkVerificationStatus['success'])
                    {
                        return response()->json($checkVerificationStatus);
                    }
                }else{
                    $kycVerification = getKYCVerificationActiveList($kycVerificationType);
                    $userVerification = userVerificationActiveList($user);
        
                    if($kycVerification['success']==true)
                    {
                        $kycVerificationActiveList = json_decode($kycVerification['data']);
        
                        if(isset($kycVerificationActiveList))
                        {
                            foreach($kycVerificationActiveList as $item)
                            {
                                if($item == KYC_PHONE_VERIFICATION && !in_array($item, $userVerification))
                                {
                                    return response()->json(['success' => false, 'message' => __('Phone is not verified'),'data'=>KYC_PHONE_VERIFICATION]);
                                }
                                if($item == KYC_EMAIL_VERIFICATION && !in_array($item, $userVerification))
                                {
                                    return response()->json(['success' => false, 'message' => __('Email is not verified'),'data'=>KYC_EMAIL_VERIFICATION]);
                                }
                                if($item == KYC_NID_VERIFICATION && !in_array($item, $userVerification))
                                {
                                    return response()->json(['success' => false, 'message' => __('NID is not verified'),'data'=>KYC_NID_VERIFICATION]);
                                }
                                if($item == KYC_PASSPORT_VERIFICATION && !in_array($item, $userVerification))
                                {
                                    return response()->json(['success' => false, 'message' => __('Passport is not verified'),'data'=>KYC_PASSPORT_VERIFICATION]);
                                }
                                if($item == KYC_DRIVING_VERIFICATION && !in_array($item, $userVerification))
                                {
                                    return response()->json(['success' => false, 'message' => __('Driving licence is not verified'),'data'=>KYC_DRIVING_VERIFICATION]);
                                }
                                if($item == KYC_VOTERS_CARD_VERIFICATION && !in_array($item, $userVerification))
                                {
                                    return response()->json(['success' => false, 'message' => __('Voter Card is not verified'),'data'=>KYC_VOTERS_CARD_VERIFICATION]);
                                }
                            }
                        }
                    }
                    
                }
                
            }
            return $next($request);
        } else {
            return response()->json(['success' => false, 'message' => __('User not found')]);
        }

    }
}
