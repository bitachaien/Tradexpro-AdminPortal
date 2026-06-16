<?php

namespace App\Http\Controllers\Api\User;

use App\Model\ActivityLog;
use App\Model\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Services\Logger;
use App\Http\Services\UserService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\verificationNid;
use App\Http\Requests\driveingVerification;
use App\Http\Requests\passportVerification;
use App\Http\Requests\ProfileDeleteRequest;
use App\Http\Services\ThirdPartyKYCService;
use App\Http\Requests\voterCardVerification;
use App\Http\Repositories\AffiliateRepository;
use App\Http\Requests\Api\ApiWhiteListRequest;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\UpdateCurrencyRequest;

class ProfileController extends Controller
{
    private $service;
    protected $affiliateRepository;
    protected $thirdPartyKYCService;
    public function __construct(AffiliateRepository $affiliateRepository)
    {
        $this->affiliateRepository = $affiliateRepository;
        $this->service = new UserService();
        $this->thirdPartyKYCService = new ThirdPartyKYCService;
    }

    /**
     * user profile
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        try {
            return $response = $this->service->userProfile(Auth::id());
        } catch (\Exception $e) {
            storeException('profile', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * update profile
     * @param ProfileUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(ProfileUpdateRequest $request)
    {
        try {
            $response = $this->service->userProfileUpdate($request, Auth::id());
        } catch (\Exception $e) {
            storeException('updateProfile', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * change password
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $response = $this->service->userChangePassword($request, Auth::id());
        } catch (\Exception $e) {
            storeException('changePassword', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * send phone verification sms
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPhoneVerificationSms(Request $request)
    {
        try {
            $response = $this->service->sendPhoneVerificationSms(Auth::user());
        } catch (\Exception $e) {
            storeException('sendPhoneVerificationSms', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * phone verification
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function phoneVerifyProcess(Request $request)
    {
        try {
            $response = $this->service->phoneVerifyProcess($request, Auth::user());
        } catch (\Exception $e) {
            storeException('phoneVerifyProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * upload nid
     * @param verificationNid $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadNid(verificationNid $request)
    {
        try {
            $response = $this->service->nidUploadProcess($request, Auth::user());
        } catch (\Exception $e) {
            storeException('uploadNid', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * upload passport
     * @param passportVerification $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPassport(passportVerification $request)
    {
        try {
            $response = $this->service->passportUploadProcess($request, Auth::user());
        } catch (\Exception $e) {
            storeException('uploadPassport', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * upload driving licence
     * @param driveingVerification $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDrivingLicence(driveingVerification $request)
    {
        try {
            $response = $this->service->drivingUploadProcess($request, Auth::user());
        } catch (\Exception $e) {
            storeException('uploadDrivingLicence', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * upload driving licence
     * @param voterCardVerification $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadVoterCard(voterCardVerification $request)
    {
        try {
            $response = $this->service->voterCardUploadProcess($request, Auth::user());
        } catch (\Exception $e) {
            storeException('uploadDrivingLicence', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * kyc details
     * @return \Illuminate\Http\JsonResponse
     */
    public function kycDetails()
    {
        try {
            $response = $this->service->kycStatusDetails(Auth::user());
        } catch (\Exception $e) {
            storeException('kycDetails', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * user setting
     * @return \Illuminate\Http\JsonResponse
     */
    public function userSetting()
    {
        try {
            $response = $this->service->userSettingDetails(Auth::user());
        } catch (\Exception $e) {
            storeException('userSetting', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * language list
     * @return \Illuminate\Http\JsonResponse
     */
    public function languageList()
    {
        try {
            $response = $this->service->languageList();
        } catch (\Exception $e) {
            storeException('languageList', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * user gauth setup
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function google2faSetup(Request $request)
    {
        try {
            $response = $this->service->setupGoogle2fa($request);
        } catch (\Exception $e) {
            storeException('google2faSetup', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * user language setup
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function languageSetup(Request $request)
    {
        try {
            $response = $this->service->languageSetup($request);
        } catch (\Exception $e) {
            storeException('languageSetup', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * user 2fa login setup
     * @return \Illuminate\Http\JsonResponse
     */
    public function setupGoogle2faLogin()
    {
        try {
            $response = $this->service->setupGoogle2faLogin(Auth::user());
        } catch (\Exception $e) {
            storeException('setupGoogle2faLogin', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * user 2fa login setup
     * @return \Illuminate\Http\JsonResponse
     */
    public function myReferralApp()
    {
        $response = $this->affiliateRepository->myReferral();
        return response()->json($response);
    }

    /**
     * @return void
     */
    public function activityList()
    {
        try {
            $result = ActivityLog::where('user_id', '=', Auth::id())->get();
            $response = ['success' => true, 'message' => __('Activity List'), 'data' => $result];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    // user notification
    public function userNotification()
    {

        try {
            $result = Notification::where(['user_id' => Auth::id(), 'status' => STATUS_PENDING])->orderBy('id', 'desc')->get();

            if (function_exists("getNotificationListt")) {
                $support = getNotificationList();
                $result = $support->merge($result);

            }

            $result->map(function ($query) {
                if (isset($query->ticket_id)) {
                    $query->notification_type = 'support';
                } else {
                    $query->notification_type = 'old';
                }
            });
            $response = ['success' => true, 'message' => __('Notification List'), 'data' => $result];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    // user notification
    public function userNotificationSeen(Request $request)
    {
        try {
            if (isset($request->ids[0])) {
                $result = Notification::where(['user_id' => Auth::id(), 'status' => STATUS_PENDING])
                    ->whereIn('id', $request->ids)->update(['status' => STATUS_ACTIVE]);
                $response = ['success' => true, 'message' => __('Updated'), 'data' => $result];
            } else {
                $response = ['success' => false, 'message' => __('Id is required'), 'data' => []];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    // update fiat currency
    public function updateFiatCurrency(UpdateCurrencyRequest $request)
    {
        try {
            $response = $this->service->updateFiatCurrency($request);
            if ($response['success'] == true) {
                $response = ['success' => true, 'message' => $response['message'], 'data' => []];
            } else {
                $response = ['success' => false, 'message' => $response['message'], 'data' => []];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    public function userKycSettingsDetails()
    {
        $response = $this->service->userKycSettingsDetails();

        return response()->json($response);
    }
    public function thirdPartyKycVerified(Request $request)
    {
        if (isset($request->inquiry_id)) {
            $response = $this->thirdPartyKYCService->verifiedPersonaKYC($request);

        } else {
            $response = ['success' => false, 'message' => __('Inquiry id is required!')];

        }
        return response()->json($response);
    }

    public function profileDeleteRequest(ProfileDeleteRequest $request)
    {
        $response = $this->service->profileDeleteRequest($request);

        return response()->json($response);
    }

    /**
     * generate secret key for api access
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSecretKey(Request $request)
    {
        try {
            $response = $this->service->generateSecretKeyToAccessApi($request, Auth::id());
        } catch (\Exception $e) {
            storeException('generateSecretKey', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    /**
     * show secret key for api access
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSecretKey(Request $request)
    {
        try {
            $response = $this->service->showSecretKeyToAccessApi($request, Auth::id());
        } catch (\Exception $e) {
            storeException('showSecretKeyToAccessApi', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    public function getApiSettings()
    {
        try {
            $response = $this->service->getApiSettings();
        } catch (\Exception $e) {
            storeException('getApiSettings', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    public function updateApiSettings(Request $request)
    {
        try {
            $response = $this->service->updateApiSettings($request);
        } catch (\Exception $e) {
            storeException('updateApiSettings', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    public function getApiWhiteList(Request $request)
    {
        return $this->handlerApiResponse(
            function () use ($request) {
                return $this->service->getApiWhiteList(auth()->id(), $request);
            },
        );
    }

    public function addApiWhiteList(ApiWhiteListRequest $request): JsonResponse
    {
        return $this->handlerApiResponse(
            function () use ($request): array {
                return $this->service->addApiWhiteList($request);
            },
        );
    }

    public function changeApiWhiteListStatus($id, $type, $value)
    {
        try {
            $response = $this->service->changeApiWhiteListStatus($id, $type, $value);
        } catch (\Exception $e) {
            storeException('changeApiWhiteListStatus', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }

    public function deleteApiWhiteList($id)
    {
        try {
            $response = $this->service->deleteApiWhiteList($id);
        } catch (\Exception $e) {
            storeException('deleteApiWhiteList', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return response()->json($response);
    }
}
