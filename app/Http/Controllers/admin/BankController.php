<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\BankService;
use App\Http\Services\CountryService;

class BankController extends Controller
{
    private $bankService;

    public function __construct()
    {
        $this->bankService = new BankService();
        $this->countryService = new CountryService();
    }

    public function bankList(Request $request)
    {
        try{
            $data['title'] = __('Bank List');
            $data['banks'] = $this->bankService->getBanks();

            return view('admin.bank.list',$data);

        } catch (\Exception $e) {
            storeException("bankList",$e->getMessage());
        }
    }

    public function bankAdd()
    {
        $data['title'] = __('Add New Bank');
        $data['button_title'] = __('Save');
        $data['countries'] = $this->countryService->getActiveCountries();

        return view('admin.bank.addEdit', $data);
    }

    public function bankStore(Request $request)
    {
        $response = $this->bankService->saveBank($request);

        if ($response['success'] == true) {
            return redirect()->route('bankList')->with(['success'=> $response['message']]);
        } else {
            return redirect()->back()->with(['dismiss'=> $response['message']]);
        }

    }

    public function bankStatusChange(Request $request)
    {
        $response = $this->bankService->statusChange($request);

        if ($response['success'] == true) {
            return response()->json(['message'=>$response['message']]);
        } else {
            return response()->json(['message'=>$response['message']]);
        }
    }

    public function bankDelete($id)
    {
        $response = $this->bankService->deleteBank($id);

        if ($response['success'] == true) {
            return redirect()->back()->with("success",__('Deleted successfully'));
        } else {
            return redirect()->back()->with("dismiss",$response['message']);;
        }
    }

    public function bankEdit($id)
    {
        $data['title'] = __('Update Bank');

        $response = $this->bankService->getBank($id);
        if($response['success']==true)
        {
            $data['countries'] = $this->countryService->getActiveCountries();
            $data['item'] = $response['item'];

            return view('admin.bank.addEdit', $data);
        }else {
            return redirect()->back()->with("dismiss",__('Bank not found!'));
        }

    }
}
