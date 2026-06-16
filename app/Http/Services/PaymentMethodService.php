<?php
namespace App\Http\Services;


use App\Http\Repositories\PaymentMethodRepository;
use App\Model\CurrencyDepositPaymentMethod;

class PaymentMethodService extends BaseService {

    public $model = CurrencyDepositPaymentMethod::class;
    public $repository = PaymentMethodRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function getCurrencyDepositePaymentMethods()
    {
        return $this->object->getCurrencyDepositePaymentMethods();
    }

    public function savePaymentMethod($request)
    {
        try{
            $data = [
                'title' => $request->title,
                'payment_method' => $request->payment_method_id,
                'status' => isset($request->status) ? true : false,
                'type' => $request->type,
            ];

            if(isset($request->id)) {
                $data['id'] = $request->id;
            }
            $response = $this->object->savePaymentMethod($data);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("savePaymentMethod",$e->getMessage());
        }

        return $response;
    }

    public function statusChange($request)
    {
        try{

            $data = [
                'id' => $request->id
            ];

            $status = $this->object->statusChange($data);

            if($status)
            {
                $response = ['success' => true, 'message' => __('Payment method status updated successfully!')];
            }else {
                $response = ['success' => false, 'message' => __('Payment method status is not updated!')];
            }

        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("statusChange",$e->getMessage());
        }

        return $response;
    }

    public function deleteCurrencyPaymentMethod($id)
    {
        try{

            $data = [
                'id' => $id
            ];

            $response = $this->object->deleteCurrencyPaymentMethod($data);

        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("deleteCurrencyPaymentMethod",$e->getMessage());
        }

        return $response;
    }

    public function getCurrencyDepositePaymentMethod($id)
    {
        try{

            $data = [
                'id' => $id
            ];

            $currency_payment_method = $this->object->getCurrencyDepositePaymentMethod($data);

            if($currency_payment_method)
            {
                $response = ['success' => true, 'item' => $currency_payment_method];
            }else {
                $response = ['success' => false, 'item' => $currency_payment_method];
            }

        } catch (\Exception $e) {
            $response = ['success' => false, 'item' => null];
            storeException("getCurrencyDepositePaymentMethod",$e->getMessage());
        }

        return $response;
    }
}
