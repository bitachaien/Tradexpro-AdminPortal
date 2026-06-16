<?php

use Illuminate\Support\Facades\Route;

Route::group(['group' => 'coin_list'], function () {
    Route::get('total-user-coin', 'CoinController@adminUserCoinList')->name('adminUserCoinList');
    Route::get('coin-list', 'CoinController@adminCoinList')->name('adminCoinList');
    Route::get('add-new-coin', 'CoinController@adminAddCoin')->name('adminAddCoin');
    Route::get('coin-edit/{id}', 'CoinController@adminCoinEdit')->name('adminCoinEdit');
    Route::get('coin-settings/{id}', 'CoinController@adminCoinSettings')->name('adminCoinSettings');
    Route::post('check-wallet-address', 'CoinController@check_wallet_address')->name('check_wallet_address');

    Route::group(['group' => 'coin_list', 'middleware' => 'check_demo'], function () {
        Route::get('coin-delete/{id}', 'CoinController@adminCoinDelete')->name('adminCoinDelete');
        Route::get('change-coin-rate', 'CoinController@adminCoinRate')->name('adminCoinRate');
        Route::get('adjust-bitgo-wallet/{id}', 'CoinController@adminAdjustBitgoWallet')->name('adminAdjustBitgoWallet');
        Route::post('save-new-coin', 'CoinController@adminSaveCoin')->name('adminSaveCoin');
        Route::post('save-coin-settings', 'CoinController@adminSaveCoinSetting')->name('adminSaveCoinSetting');
        Route::post('coin-update-process', 'CoinController@adminCoinUpdateProcess')->name('adminCoinSaveProcess');
        Route::post('change-coin-status', 'CoinController@adminCoinStatus')->name('adminCoinStatus');
        Route::get('coin-make-listed-{id}', 'CoinController@coinMakeListed')->name('coinMakeListed');
        Route::get('change-demo-trade-status-{coin_type?}', 'CoinController@demoTradeCoinStatus')->name('demoTradeCoinStatus');

        Route::post('update-wallet-key', 'CoinController@updateWalletKey')->name('updateWalletKey');

        Route::post('view-wallet-key', 'CoinController@viewWalletKey')->name('viewWalletKey');
    });
});


// Wallet management
Route::group(['group' => 'wallet_list'], function () {
    Route::get('wallet-list', 'WalletController@adminWalletList')->name('adminWalletList');
    Route::get('my-wallet-list', 'WalletController@myWalletList')->name('myWalletList');
    Route::get('wallet-address-list', 'WalletController@walletAddressList')->name('walletAddressList');
    Route::get('deduct-wallet-balance-{wallet_id}', 'WalletController@deductWalletBalance')->name('deductWalletBalance');
    Route::post('update-deduct-wallet-balance', 'WalletController@deductWalletBalanceSave')->name('deductWalletBalanceSave');
    Route::get('wallet-list-export', 'WalletController@adminWalletListExport')->name('adminWalletListExport');
});
Route::group(['group' => 'send_wallet'], function () {
    Route::get('send-wallet-balance', 'WalletController@adminSendWallet')->name('adminSendWallet');
    Route::get('active-user-list', 'WalletController@adminActiveUserList')->name('adminActiveUserList');
});
Route::group(['group' => 'send_wallet'], function () {
    Route::get('send-coin-list', 'WalletController@adminWalletSendList')->name('adminWalletSendList');
});
Route::group(['group' => 'swap_coin_history'], function () {
    Route::get('swap-coin-history', 'WalletController@adminSwapCoinHistory')->name('adminSwapCoinHistory');
});

Route::group(['middleware' => 'check_demo', 'group' => 'send_wallet'], function () {
    Route::post('admin-send-balance-process', 'WalletController@adminSendBalanceProcess')->name('adminSendBalanceProcess');
    Route::get('admin-send-balance-delete-{id}', 'WalletController@adminSendBalanceDelete')->name('adminSendBalanceDelete');
});

//Bitgo Webhook
Route::post('bitgo-webhook-save', 'CoinController@webhookSave')->name('webhookSave');
