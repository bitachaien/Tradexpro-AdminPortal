<?php

namespace App\Http\Repositories;


use App\Model\Bank;
use App\Model\FiatWithdrawal;

class FiatWithdrawalRepository extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }


}
