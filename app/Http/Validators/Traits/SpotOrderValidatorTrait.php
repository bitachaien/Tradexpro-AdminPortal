<?php

namespace App\Http\Validators\Traits;

use App\Http\Services\CoinService;
use App\Model\Coin;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

trait SpotOrderValidatorTrait
{
    public function getCoinSetting(CoinService $coinService, ?int $tradeCoinId): ?Coin
    {
        return $coinService->getCoin(['id' => $tradeCoinId])->first();
    }

    public function getMinimumBuyAmount(?Coin $coin)
    {
        if (!empty($coin)) {
            return $coin['minimum_buy_amount'];
        }

        return 0;
    }

    public function getCoinType(?Coin $coin): ?string
    {
        if (!empty($coin)) {
            return $coin['coin_type'];
        }

        return '';
    }

    public function getMinimumSellAmount(?Coin $coin)
    {
        if (!empty($coin)) {
            return $coin['minimum_sell_amount'];
        }

        return 0;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        if ($validator->fails()) {
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
        }
        $json = [
            'status' => false,
            'message' => $errors[0],
        ];

        $response = new JsonResponse($json, 200);

        throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());

    }
}
