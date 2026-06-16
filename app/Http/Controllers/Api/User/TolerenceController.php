<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\CustomException;
use App\Http\Validators\TolerenceValidator;
use App\Services\Order\TolerenceFinderService;
use Throwable;

class TolerenceController
{
    public function __construct(
        private TolerenceFinderService $tolerenceFinderService
    ) {}

    public function getTolerence(TolerenceValidator $request)
    {
        try {
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved tolerance',
                'data' => $this->tolerenceFinderService->findTolerence(
                    $request->base_coin_id, 
                    $request->trade_coin_id
                )
            ]);
        } catch (CustomException $e)
        {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data' => [],
            ]);
        }
    }
}
