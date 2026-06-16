<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\CountryService;

class CountryController extends Controller
{
    private $countryService;

    public function __construct()
    {
        $this->countryService = new CountryService();
    }

    public function countryList()
    {
        try{
            $data['title'] = __('Country List');
            $data['countries'] = $this->countryService->getCountries();

            return view('admin.country.list',$data);

        } catch (\Exception $e) {
            storeException("bankList",$e->getMessage());
        }
    }

    public function countryStatusChange(Request $request)
    {
        $response = $this->countryService->statusChange($request);

        if ($response['success'] == true) {
            return response()->json(['message'=>$response['message']]);
        } else {
            return response()->json(['message'=>$response['message']]);
        }
    }
}
