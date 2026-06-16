<?php

use Illuminate\Support\Facades\Route;

// future trade
Route::group(['group' => 'future-trade','prefix'=>'future-trade'], function () {
    Route::get('dashboard', 'FutureTradeController@dashboard')->name('futureTradeDashboard');
    
    Route::get('wallet-list', 'FutureTradeController@walletList')->name('futureTradeWalletList');
    Route::get('transfer-list', 'FutureTradeController@transferHistoryList')->name('futureTradeTransferHistoryList');

    // Future Trade Type    
    Route::get('future-trade-position-history', 'FutureTradeController@getFutureTradePositionHistory')->name('futureTradePosition');
    Route::get('future-trade-open-order-history', 'FutureTradeController@getFutureTradeOpenOrderHistory')->name('getFutureTradeOpenOrderHistory');
    Route::get('future-trade-order-history', 'FutureTradeController@getFutureTradeOrderHistory')->name('getFutureTradeOrderHistory');
    Route::get('future-trade-history', 'FutureTradeController@getFutureTradeList')->name('getFutureTradeHistory');
    Route::get('get-future-trade-{id?}', 'FutureTradeController@getFutureTradeDetails')->name('futureTradeDetails');
    // Future Trade Transaction History
    Route::get('future-trade-transaction-history-{type?}', 'FutureTradeController@getFutureTradeTransactionHistory')->name('futureTradeTransactionHistory');
    Route::get('get-future-trade-transaction-{id?}', 'FutureTradeController@getFutureTradeTransactionDetails')->name('futureTradeTransactionDetails');
});