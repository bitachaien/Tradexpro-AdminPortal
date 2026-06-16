<?php

namespace App\Http\Controllers\Api\Traits;

use App\Exceptions\OrderException;
use Illuminate\Http\JsonResponse;
use Throwable;

trait OrderTrait
{
    public function getOrderResponse(callable $callback, string $type): JsonResponse
    {
        try {
            $callback();

            return response()->json([
                'status' => true,
                'message' => __($type.' order is placed successfully!'),
                'data' => [],
            ]);
        } catch (OrderException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => [],
            ]);
        }

    }
}
