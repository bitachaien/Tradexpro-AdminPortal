<?php
namespace App\Http\Services;
use App\Http\Repositories\FaqRepository;
use App\Model\Faq;

class FaqService extends BaseService
{

    public $model = Faq::class;
    public $repository = FaqRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function getFaqActiveList($request=null)
    {
        try{
            $faqList = $this->object->getFaqActiveList($request);
            $response = ['success' => true, 'message' => __('Faq List!'),'data'=>$faqList];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("statusChange",$e->getMessage());
        }
        return $response; 
    }

    public function getActiveStakingFAQList()
    {
        $faqList = Faq::where('status', STATUS_ACTIVE)
                    ->Where('faq_type_id', FAQ_TYPE_STACKING)
                    ->orderBy('id', 'DESC')->get();
        
        $response = responseData(true, __('Staking FAQ list'), $faqList);
        return $response;
        
    }
}