<?php

namespace App\Http\Repositories;

use App\Model\CurrencyDeposit;

class CurrencyDepositRepository extends CommonRepository
{
    function __construct($model)
    {
        parent::__construct($model);
    }

    public function getPendingDepositList()
    {
        return CurrencyDeposit::where('status', 0)->get();
    }

    public function getDepositHistory($userId, $paginate = null, $search = null)
    {
        $lists = CurrencyDeposit::with(['bank'])
            ->where('user_id', $userId)
            ->when(isset($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('coin_amount', 'LIKE', '%' . $search . '%')
                        ->orWhere('currency_amount', 'LIKE', '%' . $search . '%')
                        ->orWhere('transaction_id', 'LIKE', '%' . $search . '%')
                        ->orWhere('rate', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($paginate ?? 200);

        foreach ($lists as $list) {
            $list->coin_type = $list->wallet->coin_type;
        }
        return $lists;
    }
}
