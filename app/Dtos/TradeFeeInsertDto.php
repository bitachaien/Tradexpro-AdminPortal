<?php

namespace App\Dtos;

use App\Http\Requests\TradeFeeCreationRequest;
use PhpParser\Node\Stmt\Static_;

class TradeFeeInsertDto
{
    public function __construct(
        public ?int $user_id,
        public array $coin_pair_ids,
        public float $maker_fee,
        public float $taker_fee,
    ) {}

    public static function fromRequest(TradeFeeCreationRequest $request): TradeFeeInsertDto
    {
        return new TradeFeeInsertDto(
            $request->user_id,
            $request->coin_pair_ids,
            $request->maker_fee,
            $request->taker_fee,
        );
    }
}
