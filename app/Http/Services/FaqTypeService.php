<?php
namespace App\Http\Services;


use App\Http\Repositories\FaqTypeRepository;
use App\Model\FaqType;

class FaqTypeService extends BaseService
{
    public $model = FaqType::class;
    public $repository = FaqTypeRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }
    public function getFaqTypeActiveList()
    {
        try
        {
           $response = $this->object->getFaqTypeActiveList();
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("statusChange",$e->getMessage());
        }
        return $response; 
    }
    public function saveFaqType($data)
    {
        try
        {
           $response = $this->object->saveFaqType($data);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("statusChange",$e->getMessage());
        }
        return $response; 
    }

    public function delete($id)
    {
        try
        {
           $response = $this->object->delete($id);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("statusChange",$e->getMessage());
        }
        return $response; 
    }
    
}