<?php

namespace App\Services\TradeSettingServices;

use App\Contracts\Repositories\TradeFeeRepositoryInterface;
use App\Dtos\TradeFeeCreationDto;
use App\Dtos\TradeFeeInsertDto;
use App\Dtos\TradeFeeUpdateDto;
use App\Exceptions\CustomException;
use App\TradeFee;

class TradeFeeService
{
    public function __construct(
        private TradeFeeRepositoryInterface $tradeFeeRepository
    ) {}

    public function create(TradeFeeCreationDto $data): TradeFee
    {
        return $this->tradeFeeRepository->create($data);
    }

    public function changeStatus(int $id)
    {
        $this->checkTradeFee($id);
        $this->tradeFeeRepository->changeStatusById($id);
    }

    public function insert(TradeFeeInsertDto $data)
    {
        $totalRecords = $this->tradeFeeRepository->countByUserIdAndCoinPairIds(
            $data->coin_pair_ids,
            $data->user_id ? $data->user_id : null
        ); 

        if($totalRecords > 0) {
            throw new CustomException('Cannot create duplicate trade fee records');
        }

        $chunks = array_chunk(
            array_map(
                function($item) use ($data) {
                    return get_object_vars(
                        new TradeFeeCreationDto(
                            $data->user_id,
                            $item,
                            $data->maker_fee,
                            $data->taker_fee,
                        )
                    );
                }, $data->coin_pair_ids
            ), 
            100
        );

        foreach ($chunks as $chunk) {
            $this->tradeFeeRepository->insert($chunk);
        }
    }

    public function update(int $id, TradeFeeUpdateDto $data)
    {
        $this->checkTradeFee($id);
        $this->tradeFeeRepository->update($id, $data);
    }

    private function checkTradeFee(int $id)
    {
        $tradeFee = $this->tradeFeeRepository->findById($id);

        if(! $tradeFee) {
            throw new CustomException('Trade fee not found');
        }
    }
}
