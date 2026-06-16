<?php

use Illuminate\Support\Facades\Route;

// trade
Route::group(['group' => 'coin_pair'], function () {
    Route::get('trade/coin-pairs', 'TradeSettingController@coinPairs')->name('coinPairs');
    Route::get('trade/coin-pairs-chart-update/{id}', 'TradeSettingController@coinPairsChartUpdate')->name('coinPairsChartUpdate');
    Route::get('trade/trade-fees-settings', 'TradeSettingController@tradeFeesSettings')->name('tradeFeesSettings');
    Route::get('trade/future-coin-pair-setting-{id}', 'TradeSettingController@coinPairFutureSetting')->name('coinPairFutureSetting');

    Route::patch('trade/trade-fees-status/{id}', 'TradeFeeController@changeStatus')->name('tradeFee.changeStatus');
    Route::get('trade/trade-fees', 'TradeFeeController@index')->name('tradeFees.index');
    Route::post('trade/trade-fees', 'TradeFeeController@store')->name('tradeFees.store');
    Route::patch('trade/trade-fees/{id}', 'TradeFeeController@update')->name('tradeFees.update');
});

Route::group(['middleware' => 'check_demo','group' => 'coin_pair'], function () {
    Route::get('trade/coin-pairs-delete/{id}', 'TradeSettingController@coinPairsDelete')->name('coinPairsDelete');
    Route::post('trade/save-coin-pair', 'TradeSettingController@saveCoinPairSettings')->name('saveCoinPairSettings');
    Route::post('trade/change-coin-pair-status', 'TradeSettingController@changeCoinPairStatus')->name('changeCoinPairStatus');
    Route::post('trade/change-coin-pair-default-status', 'TradeSettingController@changeCoinPairDefaultStatus')->name('changeCoinPairDefaultStatus');
    Route::post('trade/change-coin-pair-bot-status', 'TradeSettingController@changeCoinPairBotStatus')->name('changeCoinPairBotStatus');
    Route::post('trade/save-trade-fees-settings', 'TradeSettingController@tradeFeesSettingSave')->name('tradeFeesSettingSave');
    Route::post('trade/remove-trade-limit', 'TradeSettingController@removeTradeLimit')->name('removeTradeLimit');
    Route::post('trade/change-status-future-trade', 'TradeSettingController@changeFutureTradeStatus')->name('changeFutureTradeStatus');
    Route::post('trade/future-coin-pair-setting-update', 'TradeSettingController@coinPairFutureSettingUpdate')->name('coinPairFutureSettingUpdate');
});


// trade reports
Route::group([ 'group' => 'buy_order'], function () {
    Route::get('all-buy-orders-history', 'ReportController@adminAllOrdersHistoryBuy')->name('adminAllOrdersHistoryBuy');
    Route::get('all-buy-orders-history-export', 'ReportController@adminAllOrdersHistoryBuyExport')->name('adminAllOrdersHistoryBuyExport');
});
Route::group(['group' => 'sell_order'], function () {
    Route::get('all-sell-orders-history', 'ReportController@adminAllOrdersHistorySell')->name('adminAllOrdersHistorySell');
    Route::get('all-sell-orders-history-export', 'ReportController@adminAllOrdersHistorySellExport')->name('adminAllOrdersHistorySellExport');
});
Route::group([ 'group' => 'stop_limit'], function () {
    Route::get('all-stop-limit-orders-history', 'ReportController@adminAllOrdersHistoryStopLimit')->name('adminAllOrdersHistoryStopLimit');
});
Route::group([ 'group' => 'transaction'], function () {
    Route::get('all-transaction-history', 'ReportController@adminAllTransactionHistory')->name('adminAllTransactionHistory');
    Route::get('all-transaction-history-export', 'ReportController@adminAllTransactionHistoryExport')->name('adminAllTransactionHistoryExport');
});

Route::group([ 'group' => 'trade_referral'], function () {
    Route::get('all-trade-referral-history', 'ReportController@adminAllTradeReferralHistory')->name('adminAllTradeReferralHistory');
});
