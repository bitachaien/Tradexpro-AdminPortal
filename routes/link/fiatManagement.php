<?php

use Illuminate\Support\Facades\Route;

// currency list
Route::group(['group' => 'currency_list'], function () {
    Route::get('currency-list', 'CurrencyController@adminCurrencyList')->name('adminCurrencyList');
    Route::get('currency-add', 'CurrencyController@adminCurrencyAdd')->name('adminCurrencyAdd');
    Route::get('currency-edit-{id}', 'CurrencyController@adminCurrencyEdit')->name('adminCurrencyEdit');
    Route::get('fiat-currency-list', 'CurrencyController@adminFiatCurrencyList')->name('adminFiatCurrencyList');

    Route::group(['middleware' => 'check_demo', 'group' => 'currency_list'], function () {
        Route::get('currency-rate-change', 'CurrencyController@adminCurrencyRate')->name('adminCurrencyRate');
        Route::post('currency-save-process', 'CurrencyController@adminCurrencyAddEdit')->name('adminCurrencyStore');
        Route::post('currency-status-change', 'CurrencyController@adminCurrencyStatus')->name('adminCurrencyStatus');
        Route::post('currency-all', 'CurrencyController@adminAllCurrency')->name('adminAllCurrency');
        Route::get('fiat-currency-delete-{id}', 'CurrencyController@adminFiatCurrencyDelete')->name('adminFiatCurrencyDelete');
        Route::post('fiat-currency-save-process', 'CurrencyController@adminFiatCurrencySaveProcess')->name('adminFiatCurrencySaveProcess');
        Route::post('withdrawal-currency-status-change', 'CurrencyController@adminWithdrawalCurrencyStatus')->name('adminWithdrawalCurrencyStatus');
    });
});

//currency deposit Payment payment method
Route::group(['group' => 'payment_method_list'], function () {
    Route::get('currency-payment-method', 'PaymentMethodController@currencyPaymentMethod')->name('currencyPaymentMethod');
    Route::get('currency-payment-method-add', 'PaymentMethodController@currencyPaymentMethodAdd')->name('currencyPaymentMethodAdd');
    Route::get('currency-payment-method-edit-{id}', 'PaymentMethodController@currencyPaymentMethodEdit')->name('currencyPaymentMethodEdit');

    Route::group(['middleware' => 'check_demo', 'group' => 'payment_method_list'], function () {
        Route::post('currency-payment-method-store', 'PaymentMethodController@currencyPaymentMethodStore')->name('currencyPaymentMethodStore');
        Route::post('currency-payment-method-status', 'PaymentMethodController@currencyPaymentMethodStatus')->name('currencyPaymentMethodStatus');
        Route::get('currency-payment-method-delete-{id}', 'PaymentMethodController@currencyPaymentMethodDelete')->name('currencyPaymentMethodDelete');
    });
});

// currency deposit
Route::group(['group' => 'pending_deposite_list'], function () {
    Route::get('currency-deposit-list', 'CurrencyDepositController@currencyDepositList')->name('currencyDepositList');
    Route::get('currency-deposit-pending-list', 'CurrencyDepositController@currencyDepositPendingList')->name('currencyDepositPendingList');
    Route::get('currency-deposit-accept-list', 'CurrencyDepositController@currencyDepositAcceptList')->name('currencyDepositAcceptList');
    Route::get('currency-deposit-reject-list', 'CurrencyDepositController@currencyDepositRejectList')->name('currencyDepositRejectList');
    Route::get('currency-deposit-accept-{id}', 'CurrencyDepositController@currencyDepositAccept')->name('currencyDepositAccept')->middleware('check_demo');
    Route::post('currency-deposit-reject', 'CurrencyDepositController@currencyDepositReject')->name('currencyDepositReject')->middleware('check_demo');
});

// Fiat Withdraw
Route::group(['group' => 'fiat_withdraw_list'], function () {
    Route::get('fiat-withdraw-list', 'FiatWithdrawController@fiatWithdrawList')->name('fiatWithdrawList');
    Route::post('fiat-withdraw-accept', 'FiatWithdrawController@fiatWithdrawAccept')->name('fiatWithdrawAccept')->middleware('check_demo');
    Route::post('fiat-withdraw-reject', 'FiatWithdrawController@fiatWithdrawReject')->name('fiatWithdrawReject')->middleware('check_demo');
    Route::get('fiat-withdraw-pending-list', 'FiatWithdrawController@fiatWithdrawPendingList')->name('fiatWithdrawPendingList');
    Route::get('fiat-withdraw-reject-list', 'FiatWithdrawController@fiatWithdrawRejectList')->name('fiatWithdrawRejectList');
    Route::get('fiat-withdraw-active-list', 'FiatWithdrawController@fiatWithdrawActiveList')->name('fiatWithdrawActiveList');
    Route::get('withdrawl-paymment-method', 'FiatWithdrawController@getWithdrawlPaymentMethod')->name('getWithdrawlPaymentMethod');
    Route::get('withdrawl-paymment-method-add', 'FiatWithdrawController@getWithdrawlPaymentMethodAdd')->name('getWithdrawlPaymentMethodAdd');
    Route::get('withdrawl-paymment-method-edit-{id}', 'FiatWithdrawController@getWithdrawlPaymentMethodEdit')->name('getWithdrawlPaymentMethodEdit');
});
