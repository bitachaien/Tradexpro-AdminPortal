<?php

use App\Validators\OrderDataValidators\FeesZeroValidator;
use App\Validators\OrderDataValidators\MarketFeesZeroValidator;
use App\Validators\OrderDataValidators\OppositeOrderValidator;
use App\Validators\OrderDataValidators\ToleranceValidator;
use App\Validators\OrderDataValidators\WalletBalanceValidator;

return [

    'validators' => [
        OppositeOrderValidator::class,
        FeesZeroValidator::class,
        MarketFeesZeroValidator::class,
        ToleranceValidator::class,
        WalletBalanceValidator::class,
    ]

];