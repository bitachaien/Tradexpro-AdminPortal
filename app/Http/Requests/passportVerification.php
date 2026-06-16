<?php

namespace App\Http\Requests;

use App\Model\VerificationDetails;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class passportVerification extends FormRequest
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
        $mb = 1024;
        $size = settings('upload_max_size') ?? 2;
        $file_size = is_numeric($size) ? ($mb * $size) : 2048;
        $check = VerificationDetails::where('user_id',Auth::id())->where('field_name','file_two')->exists();
        $check2 = VerificationDetails::where('user_id',Auth::id())->where('field_name','file_three')->exists();
        $check3 = VerificationDetails::where('user_id',Auth::id())->where('field_name','file_selfie')->exists();
        if ($check and $check2){
            return [
                'file_two'=>'mimes:jpeg,png,jpg,webp,gif|max:'.$file_size,
                'file_three'=>'mimes:jpeg,png,jpg,webp,gif|max:'.$file_size,
                'file_selfie'=>'mimes:jpeg,png,jpg,webp,gif|max:'.$file_size
            ];
        }else
            return [
                'file_two'=>'required|mimes:jpeg,png,jpg,webp,gif|max:'.$file_size,
                'file_three'=>'required|mimes:jpeg,png,jpg,webp,gif|max:'.$file_size,
                'file_selfie'=>'required|mimes:jpeg,png,jpg,webp,gif|max:'.$file_size
            ];

    }

    public function messages()
    {
        $messages=[
            'file_two.required'=>__('Passport front copy is required'),
            'file_two.mimes'=>__('Passport front copy is must be(jpeg,png,jpg) '),
            'file_three.mimes'=>__('Passport back copy is must be(jpeg,png,jpg) '),
            'file_three.required'=>__('Passport back copy is required'),
            'file_selfie.required'=>__('Selfie image is required'),
            'file_selfie.mimes'=>__('Selfie image is must be(jpeg,png,jpg) ')

        ];

        return $messages;
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        if ($validator->fails()) {
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
        }
        $json = ['success'=>false,
            'data'=>[],
            'message' => $errors[0],
        ];
        $response = new JsonResponse($json, 200);

        throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());


    }
}
