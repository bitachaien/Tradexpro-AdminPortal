<?php

namespace App\Http\Repositories;

use App\Model\ProgressStatus;

class ProgressStatusRepository extends CommonRepository{

    public function saveProgressStatus($id, $data)
    {
        try{
            if($id != null)
            {
                ProgressStatus::where('id', $id)->update($data);
                $response = ['success' => true, 'message' => __('Progress Status updated successfully!')];
            }else{
                ProgressStatus::create($data);
                $response = ['success' => true, 'message' => __('Progress Status created successfully!')];
            }
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("Faq Create",$e->getMessage());
        }
        
        return $response;
    }

    public function deleteProgressStatus($id)
    {
        ProgressStatus::where('id', $id)->delete();
        $response = ['success' => true, 'message' => __('Progress Status deleted successfully!')];
        return $response;
    }

    public function getProgressStatusActiveListBytype($type)
    {
        $progress_status_list = ProgressStatus::where('status', STATUS_ACTIVE)->where('progress_type_id', $type)->get();
        $response = ['success' => true, 'message' => __('Progress Status List'),'data'=>$progress_status_list];
        return $response;
    }
}