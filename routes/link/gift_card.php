<?php

    Route::group(['middleware' => 'gift_card'], function (){
        // Gift Card Dashboard
        Route::get('gift-card-dashboard-list', 'GiftCardController@giftCardDashboard')->name("giftCardDashboard");

        // Gift Card Category
        Route::get('gift-card-category-list', 'GiftCardController@giftCardCategoryListPage')->name("giftCardCategoryListPage");
        Route::get('gift-card-category/{uid?}', 'GiftCardController@giftCardCategory')->name("giftCardCategory");
        Route::get('gift-card-category-delete-{uid}', 'GiftCardController@giftCardCategoryDelete')->name("giftCardCategoryDelete");
        Route::post('gift-card-category', 'GiftCardController@giftCardCategorySave')->name("giftCardCategorySave");

        // Gift Card Banner
        Route::get('gift-card-banner-list', 'GiftCardController@giftCardBannerListPage')->name("giftCardBannerListPage");
        Route::get('gift-card-banner/{uid?}', 'GiftCardController@giftCardBanner')->name("giftCardBanner");
        Route::get('gift-card-banner-delete-{uid}', 'GiftCardController@giftCardBannerDelete')->name("giftCardBannerDelete");
        Route::post('gift-card-banner', 'GiftCardController@giftCardBannerSave')->name("giftCardBannerSave");

        // Gift Card Fronted Page Header
        Route::get('gift-card-header', 'GiftCardController@giftCardHeader')->name("giftCardHeader");
        Route::post('gift-card-header', 'GiftCardController@giftCardHeaderSave')->name("giftCardHeaderSave");

        // Gift Card History
        Route::get('gift-card-history', 'GiftCardController@giftCardHistory')->name("giftCardHistory");

        // Learn more
        Route::get('gift-card-learn-more', 'GiftCardController@learnMoreGiftCard')->name('learnMoreGiftCard');
        Route::post('gift-card-learn-more', 'GiftCardController@processLearnMoreGiftCard')->name('proccessLearnMoreGiftCard');
    });
    