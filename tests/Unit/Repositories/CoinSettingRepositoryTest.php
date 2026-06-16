<?php

namespace Tests\Unit\Repositories;

use App\Http\Repositories\CoinSettingRepository;
use App\Model\CoinSetting;
use Mockery;
use PHPUnit\Framework\TestCase;

class CoinSettingRepositoryTest extends TestCase
{
    public function test_get_contract_address_from_wallet_address_returns_correct_address()
    {
        $walletAddress = 'wallet_address';
        $expectedContractAddress = 'contact_address';

        $coinSettingMock = Mockery::mock(CoinSetting::class);

        $coinSettingMock->shouldReceive('join')
            ->once()
            ->with('coins', 'coin_settings.coin_id', '=', 'coins.id')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('join')
            ->once()
            ->with('wallet_address_histories', 'wallet_address_histories.coin_type', '=', 'coins.coin_type')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('where')
            ->once()
            ->with('wallet_address_histories.address', $walletAddress)
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('select')
            ->once()
            ->with('coin_settings.contract_address')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('first')
            ->once()
            ->andReturn((object) ['contract_address' => $expectedContractAddress]);

        $repository = new CoinSettingRepository($coinSettingMock);

        $result = $repository->getContractAddressFromWalletAddress($walletAddress);

        $this->assertEquals($expectedContractAddress, $result);
    }

    public function test_get_contract_address_from_wallet_address_returns_null_if_not_found()
    {
        $walletAddress = 'wallet_address';

        $coinSettingMock = Mockery::mock(CoinSetting::class);

        $coinSettingMock->shouldReceive('join')
            ->once()
            ->with('coins', 'coin_settings.coin_id', '=', 'coins.id')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('join')
            ->once()
            ->with('wallet_address_histories', 'wallet_address_histories.coin_type', '=', 'coins.coin_type')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('where')
            ->once()
            ->with('wallet_address_histories.address', $walletAddress)
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('select')
            ->once()
            ->with('coin_settings.contract_address')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $repository = new CoinSettingRepository($coinSettingMock);

        $result = $repository->getContractAddressFromWalletAddress($walletAddress);

        $this->assertNull($result);
    }

    public function test_network_from_contract_address()
    {
        $contractAddress = 'contract_address';

        $coinSettingMock = Mockery::mock(CoinSetting::class);

        $coinSettingMock->shouldReceive('join')
            ->twice()
            ->with('coins', 'coin_settings.coin_id', '=', 'coins.id')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('where')
            ->twice()
            ->with('coin_settings.contract_address', $contractAddress)
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('select')
            ->twice()
            ->with('coins.network')
            ->andReturnSelf();

        $coinSettingMock->shouldReceive('first')
            ->once()
            ->andReturn((object) ['network' => 4]);

        $repository = new CoinSettingRepository($coinSettingMock);

        $result = $repository->getNetworkFromContractAddress($contractAddress);

        $this->assertEquals(4, $result);

        $coinSettingMock->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $result = $repository->getNetworkFromContractAddress($contractAddress);
        $this->assertNull(null);

    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
