<?php
namespace App\Http\Services;
use App\Http\Repositories\KycRepository;
use App\Model\KycList;

class KycService extends BaseService
{

    public $model = KycList::class;
    public $repository = KycRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function getKycList()
    {
        try{
            $data = $this->object->getKycList();
            $response = ['success' => true, 'message' => __('KYC list'),'data'=>$data];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getKycList",$e->getMessage());
        }
        return $response;
    }
    public function getKycActiveList()
    {
        try{
            $data = $this->object->getKycActiveList();
            $response = ['success' => true, 'message' => __('KYC active list'),'data'=>$data];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getKycActiveList",$e->getMessage());
        }
        return $response;
    }

    public function changeStatus($id)
    {
        try{
            $this->object->changeStatus($id);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getKycList",$e->getMessage());
        }
    }
    public function getKycItemDetails($id)
    {
        try{
           $data =  $this->object->getKycItemDetails($id);
           $response = ['success' => true, 'message' => __('KYC item details'),'data'=>$data];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getKycList",$e->getMessage());
        }
        return $response;
    }
    public function storeKYCItemImage($request)
    {
        try{
            $kycDetails = $this->object->getKycItemDetails($request->id);
            $response = ['success' => true, 'message' => __('Enter name and image')];
            if(isset($kycDetails))
            {
                $old_img = $kycDetails->image;
                if (!empty($request->image) || !empty($request->name)) {
                    if(!empty($request->image))
                     $icon = uploadFile($request->image,IMG_PATH,$old_img);
                    else $icon = false;
                    if ($icon != false) {
                        $imageName = $icon;
                        $data['photo'] = $imageName;

                    }
                    if(!empty($request->name)){
                        $data['name'] = $request->name;
                    }
                    $data = $this->object->storeKYCItemImage($request->id,$data);
                    $response = ['success' => true, 'message' => __('Updated successfully')];
                }
            }else{
                $response = ['success' => true, 'message' => __('Image not uploaded!')];
            }

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getKycList",$e->getMessage());
        }
        return $response;
    }
}
