<?php

namespace App\Http\Repositories;


use App\Model\Faq;

class FaqRepository extends CommonRepository 
{
    function __construct($model) {
        parent::__construct($model);
    }

    public function getFaqActiveList($request)
    {
        $faqList = Faq::where('status', STATUS_ACTIVE)
                        ->when(isset($request->faq_type_id),function($query)use($request){
                            $query->Where('faq_type_id',$request->faq_type_id);
                        })
                        ->orderBy('id', 'DESC')->paginate($request->per_page ?? 200);
        return $faqList;
    }
}