<?php
namespace App\Http\Services;

use App\Http\Repositories\ProgressStatusRepository;
use App\Model\ProgressStatus;

class ProgressStatusService extends BaseService{

    public $model = ProgressStatus::class;
    public $repository = ProgressStatusRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function saveProgressStatus($id, $data)
    {
        try{
            $data = $this->object->saveProgressStatus($id, $data);
            $response = ['success' => true, 'message' => $data['message']];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("saveProgressStatus",$e->getMessage());
        }
        return $response; 
    }

    public function deleteProgressStatus($id)
    {
        try{
            $data = $this->object->deleteProgressStatus($id);
            $response = ['success' => true, 'message' => $data['message']];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("deleteProgressStatus",$e->getMessage());
        }
        return $response;
    }
    public function getProgressStatusActiveListBytype($type)
    {
        $response = $this->object->getProgressStatusActiveListBytype($type);
        return $response;
    }
}