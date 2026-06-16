<?php

use Illuminate\Support\Facades\Route;

//FAQ
Route::group(['group' => 'faq'], function () {
    Route::get('faq-list', 'SettingsController@adminFaqList')->name('adminFaqList');
    Route::get('faq-add', 'SettingsController@adminFaqAdd')->name('adminFaqAdd');
    Route::get('faq-type-add', 'SettingsController@adminFaqTypeAdd')->name('adminFaqTypeAdd');
    Route::get('faq-edit-{id}', 'SettingsController@adminFaqEdit')->name('adminFaqEdit');
    Route::get('faq-type-edit-{id}', 'SettingsController@adminFaqTypeEdit')->name('adminFaqTypeEdit');

    Route::group(['group' => 'faq','middleware' => 'check_demo'], function () {
        Route::get('faq-delete-{id}', 'SettingsController@adminFaqDelete')->name('adminFaqDelete');
        Route::get('faq-type-delete-{id}', 'SettingsController@adminFaqTypeDelete')->name('adminFaqTypeDelete');
        Route::post('faq-type-save', 'SettingsController@adminFaqTypeSave')->name('adminFaqTypeSave');
        Route::post('faq-save', 'SettingsController@adminFaqSave')->name('adminFaqSave');
    });
});

Route::group(['group' => 'general'], function () {
    Route::get('general-settings', 'SettingsController@adminSettings')->name('adminSettings');

    Route::group(['middleware' => 'check_demo', 'group' => 'general'], function () {
        Route::post('admin-save-common-setting', 'SettingsController@adminSettingsSaveCommon')->name('adminSettingsSaveCommon');
        Route::post('common-settings', 'SettingsController@adminCommonSettings')->name('adminCommonSettings');
        Route::post('recaptcha-settings', 'SettingsController@adminCapchaSettings')->name('adminCapchaSettings');
        Route::post('email-save-settings', 'SettingsController@adminSaveEmailSettings')->name('adminSaveEmailSettings');
        Route::post('email-template-save-settings', 'SettingsController@adminSaveEmailTemplateSettings')->name('adminSaveEmailTemplateSettings');
        Route::post('sms-save-settings', 'SettingsController@adminSaveSmsSettings')->name('adminSaveSmsSettings');
        Route::post('referral-fees-settings', 'SettingsController@adminReferralFeesSettings')->name('adminReferralFeesSettings');
        Route::post('trade-referral-fees-settings', 'SettingsController@adminTradeReferralFeesSettings')->name('adminTradeReferralFeesSettings');
        Route::post('cron-save-settings', 'SettingsController@adminSaveCronSettings')->name('adminSaveCronSettings');
        Route::post('save-wallet-overview-settings', 'SettingsController@adminSaveWalletOverviewSettings')->name('adminSaveWalletOverviewSettings');
        Route::post('fiat-widthdraw-save-settings', 'SettingsController@adminSaveFiatWithdrawalSettings')->name('adminSaveFiatWithdrawalSettings');
        Route::post('admin-exchange-layout-setting', 'SettingsController@adminExchangeLayoutSettings')->name('adminExchangeLayoutSettings');
        Route::post('admin-api-overview-setting', 'SettingsController@adminApiOverviewSettings')->name('adminApiOverviewSettings');

        Route::post('choose-sms-settings-save', 'SMSController@adminChooseSmsSettings')->name('adminChooseSmsSettings');
        Route::post('nexmo-settings-save', 'SMSController@adminNexmoSmsSettingsSave')->name('adminNexmoSmsSettingsSave');
        Route::post('send-test-sms', 'SMSController@adminSendTestSms')->name('adminSendTestSms');
        Route::post('africa-talk-sms-settings-save', 'SMSController@adminAfricaTalkSmsSettingsSave')->name('adminAfricaTalkSmsSettingsSave');
        Route::get('remove-image', 'SettingsController@removeAdminImageSettings')->name('removeAdminImageSettings');
    });
});

Route::group(['group' => 'feature_settings'], function () {
    Route::get('admin-feature-settings', 'SettingsController@adminFeatureSettings')->name('adminFeatureSettings');
    Route::post('admin-cookie-settings-save', 'SettingsController@adminCookieSettingsSave')->name('adminCookieSettingsSave')->middleware('check_demo');
    Route::get('delete-bot-orders', 'SettingsController@deleteBotOrders')->name('adminDeleteBotOrders')->middleware('check_demo');
});

Route::group(['group' => 'api_settings'], function () {
    Route::get('api-settings', 'SettingsController@adminCoinApiSettings')->name('adminCoinApiSettings');
    Route::get('network-fees', 'CoinPaymentNetworkFee@list')->name('networkFees');

    Route::group(['middleware' => 'check_demo', 'group' => 'api_settings'], function () {
        Route::post('save-payment-settings', 'SettingsController@adminSavePaymentSettings')->name('adminSavePaymentSettings');
        Route::post('save-bitgo-settings', 'SettingsController@adminSaveBitgoSettings')->name('adminSaveBitgoSettings');
        Route::post('admin-erc20-api-settings', 'SettingsController@adminSaveERC20ApiSettings')->name('adminSaveERC20ApiSettings');
        Route::post('admin-other-api-settings', 'SettingsController@adminSaveOtherApiSettings')->name('adminSaveOtherApiSettings');
        Route::post('admin-stripe-api-settings', 'SettingsController@adminSaveStripeApiSettings')->name('adminSaveStripeApiSettings');
        Route::post('admin-razorpay-api-settings', 'SettingsController@adminSaveRazorpayApiSettings')->name('adminSaveRazorpayApiSettings');
        Route::get('network-fees-update', 'CoinPaymentNetworkFee@createOrUpdate')->name('networkFeesUpdate');
        Route::post('admin-paystack-api-settings', 'SettingsController@adminSavePaystackApiSettings')->name('adminSavePaystackApiSettings');
        Route::post('currency-exchange-rate-api-update', 'SettingsController@adminSaveCurrencyExchangeApiSettings')->name('adminSaveCurrencyExchangeApiSettings');
    });
});

// custom page
Route::group(['group' => 'custom_pages'], function () {
    Route::get('custom-page-slug-check', 'LandingController@customPageSlugCheck')->name('customPageSlugCheck');
    Route::get('custom-page-list', 'LandingController@adminCustomPageList')->name('adminCustomPageList');
    Route::get('custom-page-add', 'LandingController@adminCustomPageAdd')->name('adminCustomPageAdd');
    Route::get('custom-page-edit/{id}', 'LandingController@adminCustomPageEdit')->name('adminCustomPageEdit');
    Route::get('custom-page-order', 'LandingController@customPageOrder')->name('customPageOrder');
    Route::get('custom-page-delete/{id}', 'LandingController@adminCustomPageDelete')->name('adminCustomPageDelete')->middleware('check_demo');
    Route::post('custom-page-save', 'LandingController@adminCustomPageSave')->name('adminCustomPageSave')->middleware('check_demo');
});

Route::group(['group' => 'config'], function () {
    Route::get('admin-config', 'ConfigController@adminConfiguration')->name('adminConfiguration');
    Route::get('run-admin-command/{type}', 'ConfigController@adminRunCommand')->name('adminRunCommand')->middleware('check_demo');
});

// language
Route::group(['group' => 'lang_list'], function () {
    Route::get('lang-list', 'AdminLangController@adminLanguageList')->name('adminLanguageList');
    Route::get('lang-add', 'AdminLangController@adminLanguageAdd')->name('adminLanguageAdd');
    Route::get('lang-edit-{id}', 'AdminLangController@adminLanguageEdit')->name('adminLanguageEdit');
    Route::post('lang-save', 'AdminLangController@adminLanguageSave')->name('adminLanguageSave')->middleware('check_demo');
    Route::get('lang-delete-{id}', 'AdminLangController@adminLanguageDelete')->name('adminLanguageDelete')->middleware('check_demo');
    Route::get('lang-synchronize', 'AdminLangController@adminLanguageSynchronize')->name('adminLanguageSynchronize')->middleware('check_demo');
    Route::post('lang-status-change', 'AdminLangController@adminLangStatus')->name('adminLangStatus')->middleware('check_demo');
});

//Bank settings
Route::group(['group' => 'bank_list'], function () {
    Route::get('bank-list', 'BankController@bankList')->name('bankList');
    Route::get('bank-add', 'BankController@bankAdd')->name('bankAdd');
    Route::get('bank-edit-{id}', 'BankController@bankEdit')->name('bankEdit');
    Route::post('bank-save', 'BankController@bankStore')->name('bankStore')->middleware('check_demo');
    Route::post('bank-status-change', 'BankController@bankStatusChange')->name('bankStatusChange')->middleware('check_demo');
    Route::get('bank-delete-{id}', 'BankController@bankDelete')->name('bankDelete')->middleware('check_demo');
});


//country
Route::group(['group' => 'country_list'], function () {
    Route::get('country-list', 'CountryController@countryList')->name('countryList');
    Route::post('country-status-change', 'CountryController@countryStatusChange')->name('countryStatusChange')->middleware('check_demo');
});

//kyc settings
Route::group(['group' => 'kyc_settings'], function () {
    Route::get('kyc-list', 'KycController@kycList')->name('kycList');
    Route::post('kyc-status-change', 'KycController@kycStatusChange')->name('kycStatusChange');
    Route::get('kyc-update-image-{id}', 'KycController@kycUpdateImage')->name('kycUpdateImage');
    Route::post('kyc-settings', 'KycController@kycSettings')->name('kycSettings');

    Route::group(['middleware' => 'check_demo', 'group' => 'kyc_settings'], function () {
        Route::post('send_test_mail', 'SettingsController@testMail')->name('testmailsend');
        Route::post('kyc-withdrawal-setting', 'KycController@kycWithdrawalSetting')->name('kycWithdrawalSetting');
        Route::post('kyc-trade-setting', 'KycController@kycTradeSetting')->name('kycTradeSetting');
        Route::post('kyc-store-image', 'KycController@kycStoreImage')->name('kycStoreImage');
        Route::post('kyc-persona-settings', 'KycController@kycPersonaSettings')->name('kycPersonaSettings');

        Route::post('kyc-staking-settings','KycController@kycStakingSetting')->name('kycStakingSetting');
    });
});

//Google analytics
Route::group(['group' => 'google_analytics'], function () {
    Route::get('google-analytics-add', 'AnalyticsController@googleAnalyticsAdd')->name('googleAnalyticsAdd');
    Route::post('google-analytics-id-store', 'AnalyticsController@googleAnalyticsIDStore')->name('googleAnalyticsIDStore')->middleware('check_demo');
});

//SEO manager
Route::group(['group' => 'seo_manager'], function () {
    Route::get('seo-manager-add', 'SeoManagerController@seoManagerAdd')->name('seoManagerAdd');
    Route::post('seo-manager-update', 'SeoManagerController@seoManagerUpdate')->name('seoManagerUpdate')->middleware('check_demo');
});

// Two Factor Setting
Route::group(['group' => 'two_factor'], function () {
    Route::get("two-factor-settings", "TwoFactorController@index")->name("twoFactor");
    Route::post("two-factor-settings", "TwoFactorController@saveTwoFactorList")->name("SaveTwoFactor")->middleware('check_demo');
    Route::post("two-factor-data", "TwoFactorController@saveTwoFactorData")->name("SaveTwoFactorData")->middleware('check_demo');
});


Route::group(['group' => 'theme_setting'], function () {
    Route::get('themes-settings', 'ThemeSettingsController@themesSettingsPage')->name('themesSettingsPage');
    Route::get('theme-settings', 'ThemeSettingsController@addEditThemeSettings')->name('addEditThemeSettings');
    Route::get('reset-theme-color-settings', 'ThemeSettingsController@resetThemeColorSettings')->name('resetThemeColorSettings');
    Route::post('theme-navebar-settings-save', 'ThemeSettingsController@themeNavebarSettingsSave')->name('themeNavebarSettingsSave')->middleware('check_demo');
    Route::post('theme-settings-store', 'ThemeSettingsController@addEditThemeSettingsStore')->name('addEditThemeSettingsStore')->middleware('check_demo');
    Route::post('themes-settings-save', 'ThemeSettingsController@themesSettingSave')->name('themesSettingSave')->middleware('check_demo');
    Route::post('themes-settings-save', 'ThemeSettingsController@themesSettingSave')->name('themesSettingSave')->middleware('check_demo');
    Route::post('save-settings-admin', 'SettingsController@saveAdminSettingsCommon')->name('saveAdminSettingsCommon');
    Route::post('save-theme-settings-admin', 'ThemeSettingsController@saveThemeColors')->name('saveThemeColors');
});

//progress status
Route::group(['group' => 'progress-status-list'], function () {
    Route::get('progress-status-list', 'ProgressStatusController@progressStatusList')->name('progressStatusList');
    Route::get('progress-status-add', 'ProgressStatusController@progressStatusAdd')->name('progressStatusAdd');
    Route::get('progress-status-edit/{id}', 'ProgressStatusController@progressStatusEdit')->name('progressStatusEdit');
    Route::get('progress-status-settings', 'ProgressStatusController@progressStatusSettings')->name('progressStatusSettings')->middleware('check_demo');
    Route::post('progress-status-settings-update', 'ProgressStatusController@progressStatusSettingsUpdate')->name('progressStatusSettingsUpdate')->middleware('check_demo');
    Route::get('progress-status-delete/{id}', 'ProgressStatusController@progressStatusDelete')->name('progressStatusDelete')->middleware('check_demo');
    Route::post('progress-status-save', 'ProgressStatusController@progressStatusSave')->name('progressStatusSave')->middleware('check_demo');
});




// Route::post('order-settings', 'SettingsController@adminOrderSettings')->name('adminOrderSettings');
// Route::get('admin-chat-settings', 'SettingsController@adminChatSettings')->name('adminChatSettings');
// Route::group(['middleware' => 'check_demo'], function () {
//     Route::post('withdrawal-settings', 'SettingsController@adminWithdrawalSettings')->name('adminWithdrawalSettings');
//     Route::post('node-settings', 'SettingsController@adminNodeSettings')->name('adminNodeSettings');
// });

Route::group(['group'=> 'other_settings'], function () {
    Route::get('other-setting', 'OtherSettingController@otherSetting')->name('otherSetting');
    Route::get('delete-address', 'OtherSettingController@deleteWalletAddress')->name('deleteWalletAddress');
    Route::post('check-outside-market-rate', 'OtherSettingController@checkOutsideMarketRate')->name('checkOutsideMarketRate');
    Route::post('delete-coin-pair-chart-data', 'OtherSettingController@deleteCoinPairChartData')->name('deleteCoinPairChartData');
    Route::post('delete-coin-order-data', 'OtherSettingController@deleteCoinPairOrderData')->name('deleteCoinPairOrderData');
    Route::post('update-pair-token', 'OtherSettingController@updatePairWithToken')->name('updatePairWithToken');
});
