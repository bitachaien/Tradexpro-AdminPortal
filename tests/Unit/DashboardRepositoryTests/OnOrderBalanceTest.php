<?php

namespace Tests\Unit\DashboardRepositoryTests;

use App\Contracts\Repositories\BuyOrderRepositoryInterface;
use App\Contracts\Repositories\SellOrderRepositoryInterface;
use App\Contracts\Repositories\StopLimitRepositoryInterface;
use App\Http\Repositories\DashboardRepository;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\DB;
use Mockery;

class OnOrderBalanceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetOnOrderBalance()
    {
        
        $coinId = 1;
        $userId = 2;
        $buyOrderRepositoryMock = Mockery::mock(BuyOrderRepositoryInterface::class);
        $sellOrderRepositoryMock = Mockery::mock(SellOrderRepositoryInterface::class);
        $stopLimitRepositoryMock = Mockery::mock(StopLimitRepositoryInterface::class);

        $expectedResult = '70.00000000';

        $buyOrderRepositoryMock->shouldReceive('getOnOrderBalanceByBaseCoinid')
            ->with($coinId, $userId)
            ->andReturn('30');
        $sellOrderRepositoryMock->shouldReceive('getOnOrderBalanceByTradeCoinId')            
            ->with($coinId, $userId)
            ->andReturn('20');
        $stopLimitRepositoryMock->shouldReceive('getOnOrderBalanceByBaseCoinId')
            ->with($coinId, $userId)
            ->andReturn('10');
        $stopLimitRepositoryMock->shouldReceive('getOnOrderBalanceByTradeCoinId')
            ->with($coinId, $userId)
            ->andReturn('10');

        app()->instance(BuyOrderRepositoryInterface::class, $buyOrderRepositoryMock);
        app()->instance(SellOrderRepositoryInterface::class, $sellOrderRepositoryMock);
        app()->instance(StopLimitRepositoryInterface::class, $stopLimitRepositoryMock);

        
        $dashbordRepository = app()->make(DashboardRepository::class);
        $result = $dashbordRepository->getOnOrderBalance($coinId, $userId);

        $this->assertEquals($expectedResult, $result);
    }
}
