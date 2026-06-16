<?php

use Illuminate\Support\Facades\Route;

// staking
Route::group(['group' => 'staking','prefix'=>'staking'], function () {
    Route::get('dashboard', 'StakingDashboardController@dashboard')->name('stakingDashboard');
    
    Route::get('offer-create', 'StakingOfferController@createOffer')->name('stakingCreateOffer');
    Route::get('offer-list', 'StakingOfferController@offerList')->name('stakingOfferList');
    Route::post('offer-store', 'StakingOfferController@storeOffer')->name('stakingStoreOffer');
    Route::post('offer-status', 'StakingOfferController@offerStatus')->name('stakingOfferStatus');
    Route::get('offer-edit-{uid}', 'StakingOfferController@editOffer')->name('stakingOfferEdit');
    Route::get('delete-offer-{uid}', 'StakingOfferController@deleteOffer')->name('stakingDeleteOffer');

    Route::get('investment-list', 'StakingOfferController@investmentList')->name('stakingInvestmentList');
    Route::get('investment-details-{uid}', 'StakingOfferController@investmentDetails')->name('stakingInvestmentDetails');
    Route::get('give-payment', 'StakingOfferController@givePayment')->name('stakingGivePayment');

    Route::get('payment-history','StakingOfferController@stakingInvestmentPaymentList')->name('stakingInvestmentPaymentList');
    
    Route::get('landing-settings','StakingOfferController@landingSettings')->name('stakingLandingSettings');
    Route::post('landing-settings-update','StakingOfferController@landingSettingsUpdate')->name('stakingLandingSettingsUpdate');
});