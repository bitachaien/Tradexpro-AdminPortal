<?php
namespace App\Http\Services;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Log;
use App\Http\Repositories\SettingRepository;
use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Client;

class SmsService
{
    private $settingRepo;

    public function __construct()
    {
        $this->settingRepo = new SettingRepository;
    }

    public function send($number, $message)
    {
        try {
            $sid = allsetting('twillo_secret_key');
            $token = allsetting('twillo_auth_token');
            $from = allsetting('twillo_number');

            $twilio = new Twilio($sid, $token, $from);
            $twilio->message($number, $message);

        } catch (\Exception $e) {
            storeException('sms send problem -- ',$e->getMessage());
            return false;
        }

        return true;
    }

    public function adminChooseSmsSettings($request)
    {
        $response = $this->settingRepo->saveAdminSetting($request);
        return $response;
    }

    public function adminNexmoSmsSettingsSave($request)
    {
        $response = $this->settingRepo->saveAdminSetting($request);
        return $response;
    }

    public function adminAfricaTalkSmsSettingsSave($request)
    {
        $response = $this->settingRepo->saveAdminSetting($request);
        return $response;
    }

    public function sendTestSMS($request)
    {
        if(isset($request->mobile))
        {
            if(is_numeric($request->mobile))
            {
                $message = 'Test SMS Send';
                $select_sms_type = allsetting('select_sms_type');
                if($select_sms_type == SMS_TYPE_TWILIO)
                {
                   $response = $this->sendByTwilioSMSService($request->mobile, $message);

                }elseif($select_sms_type == SMS_TYPE_NEXMO)
                {
                    $response = $this->sendByNexmoSMSService($request->mobile, $message);
                }
                elseif($select_sms_type == SMS_TYPE_AFRICA_TALK)
                {
                    $response = $this->sendByAfricaTalkSMSService($request->mobile, $message);
                }
                else{
                    $response = ['suceess'=>false, 'message'=> __('Please, enable sms servcie first!')];
                }
            }else{
                $response = ['success'=>false, 'message'=>__('Invalid Mobile Number!')];
            }
        }else{
            $response = ['success'=>false, 'message'=>__('Mobile number is required!')];
        }

        return $response;
    }

    public function sendSMS($mobileNumber, $message)
    {
        $select_sms_type = allsetting('select_sms_type');
        if($select_sms_type == SMS_TYPE_TWILIO)
        {
            $response = $this->sendByTwilioSMSService("+".$mobileNumber, $message);

        } elseif($select_sms_type == SMS_TYPE_NEXMO)
        {
            $response = $this->sendByNexmoSMSService($mobileNumber, $message);
        } elseif($select_sms_type == SMS_TYPE_AFRICA_TALK)
        {
            $response = $this->sendByAfricaTalkSMSService($mobileNumber, $message);
        } else {
            $response = ['suceess'=>false, 'message'=> __('Please, enable sms service first!')];
        }

        return $response;
    }

    public function sendByTwilioSMSService($number, $message)
    {
        try {
            $sid = allsetting('twillo_secret_key');
            $token = allsetting('twillo_auth_token');
            $from = allsetting('twillo_number');

            $twilio = new Twilio($sid, $token, $from);
            $twilio->message($number, $message);

            $response = ['success'=>true, 'message'=> __("SMS is sent by Twilio successfully!")];
        } catch (\Exception $e) {
            storeException('sendByTwilioSMSService', $e->getMessage());
            $response = ['success'=>false, 'message'=> __("Something went wrong!")];

        }

        return $response;
    }

    public function sendByNexmoSMSService($mobileNumber, $message)
    {
        try {
            $nexmo_secret_key = allsetting('nexmo_secret_key');
            $nexmo_api_key = allsetting('nexmo_api_key');
            $company_name = allsetting('company_name')??'Unknown';

            // Instantiate a Guzzle HTTP client
            $client = new Client();

            // Prepare the request parameters
            $url = 'https://rest.nexmo.com/sms/json';

            $data = [
                'from' => $company_name,
                'text' => $message,
                'to' => $mobileNumber,
                'api_key' => $nexmo_api_key,
                'api_secret' => $nexmo_secret_key,
            ];

            // Send a POST request using Guzzle HTTP client
            $response_data = $client->post($url, [
                'form_params' => $data,
            ]);


            // Get the response body as JSON
            $responseData = json_decode($response_data->getBody()->getContents(), true);

            // Get the status from the response
            $status = $responseData['messages'][0]['status'];

            if ($status == 0) {
                $response = ['success'=>true, 'message'=> __('SMS is sent successfully!')];
            } else {
                storeException('sms test', $responseData);
                $response = ['success'=>false, 'message'=> __('SMS is not sent!')];
            }
        } catch (\Exception $e) {
            storeException('sendByNexmoSMSService', $e->getMessage());
            $response = ['success'=>false, 'message'=> __("Something went wrong!")];

        }
        return $response;
    }

    public function sendByAfricaTalkSMSService($mobileNumber, $message)
    {
        try {
            $username   = allsetting('africa_talk_user_name');
            $apiKey     = allsetting('africa_talk_api_key');
            $app_mode     = allsetting('africa_talk_app_mode');

            $recipients = "+".$mobileNumber;

            if($app_mode == 'live')
            {
                $url = 'https://api.africastalking.com/version1/messaging';

            }else{
                $url = 'https://api.sandbox.africastalking.com/version1/messaging';
            }

            $client = new Client();

            $response_data = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'apiKey' => $apiKey,
                ],
                'form_params' => [
                    'username' => $username,
                    'to' => $recipients,
                    'message' => $message,
                ],
            ]);

            $body = $response_data->getBody();

            // Decode the JSON response
            $data = json_decode($body, true);

            // Check if the response contains SMSMessageData and Recipients
            if (isset($data['SMSMessageData']) && isset($data['SMSMessageData']['Recipients'])) {
                $recipients = $data['SMSMessageData']['Recipients'];
                $status_code = $recipients[0]['statusCode'];
                $status = $recipients[0]['status'];
                if($status_code == 101)
                {
                    $response = ['success'=>true, 'message'=>__('SMS is sent successfully')];
                }else{
                    storeException('sendByAfricaTalkSMSService status', $status);
                    $response = ['success'=>false, 'message'=> __('SMS is not sent!')];
                }
            } else {
                $response = ['success'=>false, 'message'=> __('Failed to get status from response!')];
            }

        } catch (\Exception $e) {
            storeException('sendByAfricaTalkSMSService', $e->getMessage());
            $response = ['success'=>false, 'message'=> $e->getMessage()];

        }
        return $response;
    }
}
