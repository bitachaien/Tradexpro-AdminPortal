<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CaptchaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'select_captcha_type' => 'required|integer'
        ];
       
        if(isset($this->select_captcha_type) && $this->select_captcha_type == CAPTCHA_TYPE_RECAPTCHA)
        {
            $rules['NOCAPTCHA_SECRET'] = 'required';
            $rules['NOCAPTCHA_SITEKEY'] = 'required';
        }

        if(isset($this->select_captcha_type) && $this->select_captcha_type == CAPTCHA_TYPE_GEETESTCAPTCHA)
        {
            $rules['GEETEST_CAPTCHA_ID'] = 'required';
            $rules['GEETEST_CAPTCHA_KEY'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        $messages=[
            'select_captcha_type.required'=>__('Enable Captcha is required'),
            'select_captcha_type.integer'=>__('Enable Captcha is invalid'),
            'NOCAPTCHA_SECRET.required'=>__('Google Re-Captcha Secret is required'),
            'NOCAPTCHA_SITEKEY.required'=>__('Google Re-Captcha Site Key is required'),
            'GEETEST_CAPTCHA_ID.required'=>__('GeeTest Captcha ID is required'),
            'GEETEST_CAPTCHA_KEY.required'=>__('GeeTest Captcha Key is required'),

        ];

        return $messages;
    }


    protected function failedValidation(Validator $validator)
    {
        if ($this->header('accept') == "application/json") {
            $errors = [];
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
            }
            $json = [
                'success' => false,
                'data' => [],
                'message' => $errors[0],
            ];
            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }

    }
}
