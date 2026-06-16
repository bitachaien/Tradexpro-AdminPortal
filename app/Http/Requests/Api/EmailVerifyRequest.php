<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EmailVerifyRequest extends FormRequest
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
        $rules =[
            'email'=>'required|email',
            'verify_code'=>'required|min:6|max:6|exists:user_verification_codes,code'
        ];
        $agent = checkUserAgent($this);
        if($agent == 'android' || $agent == 'ios') {
        } else {
            if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_RECAPTCHA)) {
                $rules['recapcha'] = 'required|captcha';
            }

            if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_GEETESTCAPTCHA)) {
                $rules['lot_number'] = 'required';
                $rules['captcha_output'] = 'required';
                $rules['pass_token'] = 'required';
                $rules['gen_time'] = 'required';
            }
        }
        return $rules;
    }

    public function messages()
    {
        $messages=[
            'email.required'=>__('Email can\'t be empty.')
            ,'verify_code.required'=>__('Code can\'t be empty.')
            ,'email.email'=>__('Invalid email format.')
            ,'email.exists'=>__('Email doesn\'t exists.')
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
                'success'=>false,
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
