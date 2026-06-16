<?php

namespace App\Http\Repositories;


use App\Model\FaqType;

class FaqTypeRepository extends CommonRepository 
{
    function __construct($model) {
        parent::__construct($model);
    }
    public function getFaqTypeActiveList()
    {
        try{
            $data = FaqType::where('status',STATUS_ACTIVE)->orderBy('id', 'DESC')->get();
            $response = ['success' => true, 'message' => __('Something went wrong'),'data'=>$data];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("Faq Create",$e->getMessage());
        }
        
        return $response;
    }
    public function saveFaqType($data)
    {
        try{
            if(isset($data['id']))
            {
                FaqType::where('id', $data['id'])->update($data);
                $response = ['success' => true, 'message' => __('Faq Type updated successfully!')];
            }else{
                FaqType::create($data);
                $response = ['success' => true, 'message' => __('Faq Type created successfully!')];
            }
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("Faq Create",$e->getMessage());
        }
        
        return $response;
    }

    public function delete($id)
    {
        try{
            
            FaqType::where('id', $id)->delete();
            $response = ['success' => true, 'message' => __('Faq Type deleted successfully!')];
        
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("Faq Create",$e->getMessage());
        }
        
        return $response;
    }
}