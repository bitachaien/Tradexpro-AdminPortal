<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\FaqService;

class FaqController extends Controller
{
    private $serviceFaq;

    function __construct()
    {
        $this->serviceFaq = new FaqService();
    }

    public function faqList(Request $request)
    {
        try{
            $response = $this->serviceFaq->getFaqActiveList($request);
        } catch (\Exception $e) {
            storeException("faqList",$e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }
}
