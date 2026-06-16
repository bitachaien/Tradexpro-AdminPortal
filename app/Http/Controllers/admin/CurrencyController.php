<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\Admin\CurrencyRequest;
use App\Http\Services\CurrencyService;
use App\Model\CurrencyList;
use App\Model\FiatWithdrawalCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CurrencyController extends Controller
{
    private $service;
    function __construct()
    {
        $this->service = new CurrencyService();
    }
    /*
   *
   * adminCurrencyList
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function adminCurrencyList()
    {
        $data['title'] = __('Fiat Currency List');
        $data['items'] = CurrencyList::get();

        return view('admin.currency.list', $data);
    }
    /*
     * New Currency Add
     *
     * Show the Empty form.
     * @return \Illuminate\Http\Response
     */
    public function adminCurrencyAdd(){
        $data['title'] = __('Fiat Currency Add');
        return view('admin.currency.addEdit',$data);
    }
    /*
     * Specific Currency Edit Form
     *
     * Show the Specific Currency form.
     * @return \Illuminate\Http\Response
     */
    public function adminCurrencyEdit($id){
        $data['title'] = __('Fiat Currency Edit');
        $data['item'] = CurrencyList::find($id);
        return view('admin.currency.addEdit',$data);
    }
    /*
     * Currency Add Edit
     *
     * @return \Illuminate\Http\Response
     */
    public function adminCurrencyAddEdit(CurrencyRequest $request)
    {
        $response = $this->service->currencyAddEdit($request);
        if($response["success"]) return redirect()->route("adminCurrencyList")->with("success",$response["message"]);
        return redirect()->route("adminCurrencyList")->with("dismiss",$response["message"]);
    }
    /*
     * Currency item status update
     *
     * @return \Illuminate\Http\Response json
     */
    public function adminCurrencyStatus(Request $request){
        return response()->json(
            $this->service->currencyStatusUpdate($request->active_id ?? 0)
        );
    }
    /*
     * Currency rate update Api
     *
     * @return \Illuminate\Http\Response
     */
    public function adminCurrencyRate(){
        try {
            $this->service->currencyRateSave();
            $response = $this->service->response;
            if($response["success"]) return redirect()->route("adminCurrencyList")->with("success",$response["message"]);
            return redirect()->route("adminCurrencyList")->with("dismiss",$response["message"]);
        } catch(\Exception $e) {
            storeException('adminCurrencyRate ex', $e->getMessage());
            return redirect()->route("adminCurrencyList")->with("dismiss",__('Currency api key is not valid'));
        }

    }
    /*
     * Get All Currency
     *
     * @return \Illuminate\Http\Response
     */
    public function adminAllCurrency(Request $request){
        $this->service->saveAllCurrency();
        return response()->json(["status" => true]);
    }

    /*
  *
  * adminFiatCurrencyList
  * Show the list of specified resource.
  * @return \Illuminate\Http\Response
  *
  */
    public function adminFiatCurrencyList()
    {
        $data['title'] = __('Fiat Withdrawal Currency List');
        $data['items'] = FiatWithdrawalCurrency::join('currency_lists','currency_lists.id', '=','fiat_withdrawal_currencies.currency_id')
            ->select('currency_lists.rate','currency_lists.code','currency_lists.symbol','currency_lists.name','fiat_withdrawal_currencies.id','fiat_withdrawal_currencies.status')
            ->get();
        $data['currency_list'] = CurrencyList::where(['status' => STATUS_ACTIVE])->get();

        return view('admin.fiat-withdraw.currency_list', $data);
    }

    /*
    * Currency item status update
    *
    * @return \Illuminate\Http\Response json
    */
    public function adminWithdrawalCurrencyStatus(Request $request)
    {
        $response = $this->service->withdrawalCurrencyStatusUpdate($request->active_id);
        return response()->json(["success" => $response]);
    }

    // save withdrawal currency
    public function adminFiatCurrencySaveProcess(Request $request)
    {
        $response = $this->service->withdrawalCurrencySaveProcess($request);
        if ($response['success'] == true) {
            return redirect()->back()->with('success',$response['message']);
        } else {
            return redirect()->back()->with('dismiss',$response['message']);
        }
    }

    // delete withdrawal currency
    public function adminFiatCurrencyDelete($id)
    {
        $response = $this->service->withdrawalCurrencyDeleteProcess($id);
        if ($response['success'] == true) {
            return redirect()->back()->with('success',$response['message']);
        } else {
            return redirect()->back()->with('dismiss',$response['message']);
        }
    }

}
