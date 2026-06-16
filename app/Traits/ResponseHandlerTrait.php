<?php

namespace App\Traits;

use Exception;
use App\Exceptions\InvalidRequestException;

trait ResponseHandlerTrait
{
    use ResponseFormatTrait;

    public function handlerGeneralResponse(callable $callback)
    {
        try {
            $response = $callback();
            return $this->responseData($response['success'], $response['message'], $response['data']);
        } catch (InvalidRequestException $e) {
            storeLog(processExceptionMsg($e), 'error');
            return $this->responseData(false, $e->getMessage());
        } catch (Exception $e) {
            storeLog(processExceptionMsg($e), 'error');
            return $this->responseData(false);
        }
    }

    public function handlerResponseAndRedirect(callable $callback)
    {
        try {
            $response = $callback();
            $data = $response['data'] ?? [];
            $message = $response['message'] ?? '';
            $status = $response['success'] ? 'success' : 'dismiss';

            if (!empty($data['redirectUrl'])) {
                $redirect = redirect($data['redirectUrl']);
                return $message ? $redirect->with([$status => $message]) : $redirect;
            }
            if (!empty($data['redirectView'])) {
                $view = $data['redirectView'];
                unset($data['redirectView']);
                return $view->with($data);
            }
            return back()->with([$status => $message]);
        } catch (InvalidRequestException $e) {
            storeLog(processExceptionMsg($e), 'error');
            return back()->with(['dismiss' => $e->getMessage()]);
        } catch (Exception $e) {
            storeLog(processExceptionMsg($e), 'error');
            return back()->with(['dismiss' => __('Something went wrong. Please try again.')]);
        }
    }

    public function handlerApiResponse(callable $callback)
    {
        try {
            $response = $callback();
            return $this->responseJsonData($response['success'], $response['message'], $response['data']);
        } catch (InvalidRequestException $e) {
            storeLog(processExceptionMsg($e), 'error');
            return $this->responseJsonData(false, $e->getMessage());
        } catch (Exception $e) {
            storeLog(processExceptionMsg($e), 'error');
            return $this->responseJsonData(false);
        }
    }
}
