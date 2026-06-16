<?php

namespace Tests\Unit\Repositories;

use App\Http\Repositories\WalletAddressHistoryRepository;
use App\Model\WalletAddressHistory;
use Mockery;
use PHPUnit\Framework\TestCase;

class WalletAddressHistoryRepositoryTest extends TestCase
{
    public function test_get_network_from_wallet_address()
    {
        $walletAddressHistoryMock = Mockery::mock(WalletAddressHistory::class);
        $walletAddressHistoryMock->shouldReceive('join')
            ->once()
            ->with('coins', 'wallet_address_histories.coin_type', '=', 'coins.coin_type')
            ->andReturnSelf();
        
        $walletAddressHistoryMock->shouldReceive('where')
            ->once()
            ->with('wallet_address_histories.address', 'sampleAddress')
            ->andReturnSelf();
        
        $walletAddressHistoryMock->shouldReceive('select')
            ->once()
            ->with('coins.network')
            ->andReturnSelf();
        
        $walletAddressHistoryMock->shouldReceive('first')
            ->once()
            ->andReturn((object) ['network' => 1]);

        
        $repository = new WalletAddressHistoryRepository($walletAddressHistoryMock);
        
        $result = $repository->getNetWorkFromWalletAddress('sampleAddress');
        $this->assertEquals(1, $result);
    }

    public function test_get_network_from_wallet_address_when_address_or_coin_not_found()
    {
        $walletAddressHistoryMock = Mockery::mock(WalletAddressHistory::class);
        $walletAddressHistoryMock->shouldReceive('join')
            ->once()
            ->with('coins', 'wallet_address_histories.coin_type', '=', 'coins.coin_type')
            ->andReturnSelf(); 
        
        $walletAddressHistoryMock->shouldReceive('where')
            ->once()
            ->with('wallet_address_histories.address', 'sampleAddress')
            ->andReturnSelf(); 
        
        $walletAddressHistoryMock->shouldReceive('select')
            ->once()
            ->with('coins.network')
            ->andReturnSelf();
        
        $walletAddressHistoryMock->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $repository = new WalletAddressHistoryRepository($walletAddressHistoryMock);
        
        $result = $repository->getNetWorkFromWalletAddress('sampleAddress');
        $this->assertNull($result);
    }

    protected function tearDown(): void
    {
        Mockery::close(); // Close Mockery to clean up the mocks
    }

}
