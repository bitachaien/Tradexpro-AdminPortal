<?php 
namespace App\Http\Services;

class GeeTestService {

    public function checkValidation($request)
    {
        // 1. Initialize GeeTest parameter information
        $captcha_id = allsetting('GEETEST_CAPTCHA_ID');
        $captcha_key = allsetting('GEETEST_CAPTCHA_KEY');
        $api_server = 'http://gcaptcha4.geetest.com';

        // 2. Get the verification parameters passed from the front end after user verification  
        $lot_number = $request->lot_number;
        $captcha_output = $request->captcha_output;
        $pass_token = $request->pass_token;
        $gen_time = $request->gen_time;

        // 3. Generate signature
        // Using standard hmac algorithms to generate signatures, using the user's current verification serial number lot_number as the original message, and the client's verification private key as the key
        // Using sha256 hash algorithm to hash message and key in one direction to generate the final signature
        $lotnumber_bytes = utf8_encode($lot_number);
        $prikey_bytes = utf8_encode($captcha_key);
        $sign_token = hash_hmac('sha256', $lotnumber_bytes, $prikey_bytes);

        // 4. Upload verification parameters to the second verification interface of GeeTest to verify the user verification status
        $query = [
            'lot_number' => $lot_number,
            'captcha_output' => $captcha_output,
            'pass_token' => $pass_token,
            'gen_time' => $gen_time,
            'sign_token' => $sign_token,
        ];

        // captcha_idParameter is recommended to be placed after url, so that when an exception is requested, it can be quickly located in the log according to the id
        $url = $api_server . '/validate' . '?captcha_id=' . $captcha_id;

        // Pay attention to handling interface exceptions, and make corresponding exception handling when requesting GeeTest secondary verification interface exceptions or response status is not 200
        // Guarantee that the business process will not be blocked by interface request timeout or service non-response
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_TIMEOUT => 5, // set timeout to 5 seconds
        ));
        $res = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code === 200) {
            $gt_msg = json_decode($res, true);

            // 5. According to the user authentication status returned by GeeTest, the website owner conducts his own business logic
            if ($gt_msg['result'] == 'success') {

                $response = responseData(true, $gt_msg['reason']);
                
            } else {

                $response = responseData(false, $gt_msg['reason']);
            }
        } else {
            $response = responseData(false, __('request geetest api fail'));
        }
        return $response;
    }
}