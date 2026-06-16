<?php

namespace App\Dtos;

use App\Http\Requests\TradeFeeUpdateRequest;

class TradeFeeUpdateDto
{
    public function __construct(
        public float $maker_fee,
        public float $taker_fee,
    ) {}

    public static function fromRequest(TradeFeeUpdateRequest $request): TradeFeeUpdateDto
    {
        return new TradeFeeUpdateDto(
            $request->maker_fee,
            $request->taker_fee,
        );
    }
}
