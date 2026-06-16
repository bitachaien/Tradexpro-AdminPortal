<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/coin-payment-notifier', 'Api\WalletNotifier@coinPaymentNotifier')->name('coinPaymentNotifier');
Route::post('bitgo-wallet-webhook','Api\WalletNotifier@bitgoWalletWebhook')->name('bitgoWalletWebhook');

Route::group(['namespace'=>'Api', 'middleware' => 'wallet_notify'], function (){
    Route::post('wallet-notifier','WalletNotifier@walletNotify');
    Route::post('wallet-notifier-confirm','WalletNotifier@notifyConfirm');
});
// For Two factor
Route::group(['namespace'=>'Api', 'middleware' => ['api-user','checkApi']], function (){
    Route::get('two-factor-list','AuthController@twoFactorList')->name("twoFactorListApi");
    Route::match(['GET','POST'],'/google-two-factor','AuthController@twoFactorGoogleSetup')->name("twoFactorGoogleApi");
    Route::post('save-two-factor','AuthController@twoFactorSave')->name("twoFactorSaveApi");
    Route::post('send-two-factor','AuthController@twoFactorSend')->name("twoFactorSendApi");
    Route::post('check-two-factor','AuthController@twoFactorCheck')->name("twoFactorCheckApi");
});

Route::get('maintenance-mode', function() {
    $status = allsetting('maintenance_mode_status') ?? STATUS_PENDING;
    return response()->json(['maintenance_mode_status' => $status ]);
});


Route::group(['middleware' => 'maintenanceMode'], function (){

    Route::group(['namespace'=>'Api\Public', 'prefix' => 'v1/markets', 'middleware' => 'publicSecret'], function () {
        Route::get('price/{pair?}', 'PublicController@getExchangePrice')->name('getExchangeTrade');
        Route::get('orderbook/{pair}', 'PublicController@getExchangeOrderBook')->name('getExchangeOrderBook');
        Route::get('trade/{pair}', 'PublicController@getExchangeTrade')->name('getExchangeTrade');
        Route::get('chart/{pair}', 'PublicController@getExchangeChart')->name('getExchangeChart');
    });

    Route::group(['middleware' => ['checkApi']], function () {
        Route::group(['namespace'=>'Api'], function () {
            // auth
            Route::get('common-settings', 'LandingController@commonSettings');
            Route::post('sign-up', 'AuthController@signUp');
            Route::post('sign-in', 'AuthController@signIn');
            Route::post('social-sign-login', 'AuthController@loginWithSocialPlatform');
            Route::post('verify-email', 'AuthController@verifyEmail');
            Route::post('resend-verify-email-code', 'AuthController@resendVerifyEmailCode');
            Route::post('forgot-password', 'AuthController@forgotPassword');
            Route::post('reset-password', 'AuthController@resetPassword');
            Route::post('g2f-verify', 'AuthController@g2fVerify');
            Route::get('landing', 'LandingController@index');
            Route::get('banner-list/{id?}', 'LandingController@bannerList');
            Route::get('announcement-list/{id?}', 'LandingController@announcementList');
            Route::get('feature-list/{id?}', 'LandingController@featureList');
            Route::get('social-media-list/{id?}', 'LandingController@socialMediaList');
            Route::get('captcha-settings', 'LandingController@captchaSettings');
            Route::get('custom-pages/{type?}', 'LandingController@getCustomPageList');
            Route::get('pages-details/{slug}', 'LandingController@getCustomPageDetails');

            Route::get('common-landing-custom-settings', 'LandingController@common_landing_custom_settings');
            Route::get('faq-list', 'FaqController@faqList');
            Route::get('market-overview-coin-statistic-list', 'LandingController@getMarketOverviewCoinStatisticList');
            Route::get('market-overview-top-coin-list', 'LandingController@getMarketOverviewTopCoinList');
            Route::get('market-overview', 'MarketController@getMarketOverview');

            Route::get('currency-list', 'LandingController@currencyList');
            Route::get('public-site-settings', 'LandingController@publicSiteSettings');
        });
        Route::group(['namespace'=>'Api\User'], function () {
            Route::get('get-exchange-all-orders-app', 'ExchangeController@getExchangeAllOrdersApp')->name('getExchangeAllOrdersApp');
            Route::get('app-get-pair', 'ExchangeController@appExchangeGetAllPair')->name('appExchangeGetAllPair');
            Route::get('app-dashboard/{pair?}', 'ExchangeController@appExchangeDashboard')->name('appExchangeDashboard');
            Route::get('get-exchange-market-trades-app', 'ExchangeController@getExchangeMarketTradesApp')->name('getExchangeMarketTradesApp');
            Route::get('get-exchange-chart-data-app', 'ExchangeController@getExchangeChartDataApp')->name('getExchangeChartDataApp');

            // staking
            Route::group(['group' => 'staking','prefix'=>'staking', 'middleware' => 'staking'], function () {
                Route::get('offer-list', 'StakingOfferController@offerList');
                Route::get('offer-list-details', 'StakingOfferController@offerDetails');

                Route::get('landing-details', 'StakingOfferController@landingDetails');
            });
        });

        Route::group(['namespace'=>'Api', 'middleware' => ['auth:api']], function () {
            //logout
            Route::post('log-out-app','AuthController@logOutApp')->name('logOutApp');
        });


        Route::group(['namespace'=>'Api\User', 'middleware' => ['auth:api','api-user','generateSecret','last_seen']], function () {
            // profile

            Route::get('notifications', 'ProfileController@userNotification');
            Route::post('notification-seen', 'ProfileController@userNotificationSeen');
            Route::get('activity-list', 'ProfileController@activityList');
            Route::post('update-profile', 'ProfileController@updateProfile');
            Route::post('change-password', 'ProfileController@changePassword');
            Route::post('generate-secret-key', 'ProfileController@generateSecretKey');
            Route::post('show-secret-key', 'ProfileController@showSecretKey');

            // kyc
            Route::post('send-phone-verification-sms', 'ProfileController@sendPhoneVerificationSms');
            Route::post('phone-verify', 'ProfileController@phoneVerifyProcess');
            Route::post('upload-nid', 'ProfileController@uploadNid');
            Route::post('upload-passport', 'ProfileController@uploadPassport');
            Route::post('upload-driving-licence', 'ProfileController@uploadDrivingLicence');
            Route::post('upload-voter-card', 'ProfileController@uploadVoterCard');
            Route::get('kyc-details', 'ProfileController@kycDetails');
            Route::get('user-setting', 'ProfileController@userSetting');
            Route::get('language-list', 'ProfileController@languageList');
            Route::post('language-setup', 'ProfileController@languageSetup');
            Route::post('update-currency', 'ProfileController@updateFiatCurrency');
            Route::get('kyc-active-list', 'KycController@kycActiveList');
            Route::get('user-kyc-settings-details', 'ProfileController@userKycSettingsDetails')->name('userKycSettingsDetails');
            Route::post('third-party-kyc-verified', 'ProfileController@thirdPartyKycVerified')->name('thirdPartyKycVerified');

            Route::group(['middleware'=>'check_demo'], function() {
                Route::post('google2fa-setup', 'ProfileController@google2faSetup');
                Route::get('setup-google2fa-login', 'ProfileController@setupGoogle2faLogin');

                Route::post('profile-delete-request', 'ProfileController@profileDeleteRequest');
            });

            // coin
            Route::get('get-coin-list','CoinController@getCoinList');
            Route::get('get-coin-pair-list','CoinController@getCoinPairList');

            Route::group(['middleware' => ['checkSwap']], function () {
                Route::get('swap-coin-details-app', 'WalletController@getCoinSwapDetailsApp')->name('getCoinSwapDetailsApp');
                Route::get('get-rate-app', 'WalletController@getRateApp')->name('getRateApp');
                Route::get('coin-swap-app', 'WalletController@coinSwapApp')->name('coinSwapApp');
                Route::post('swap-coin-app', 'WalletController@swapCoinApp')->name('swapCoinApp');
                Route::get('coin-convert-history-app', 'WalletController@coinSwapHistoryApp')->name('coinSwapHistoryApp');
            });

            Route::get('referral-app', 'ProfileController@myReferralApp')->name('myReferralApp');

            Route::group(['middleware' => ['checkCurrencyDeposit']], function () {
                Route::get('deposit-bank-details/{id}', 'DepositController@depositBankDetails')->name('depositBankDetails');
                Route::get('currency-deposit', 'DepositController@currencyDepositInfo')->name('currencyDepositInfo');
                Route::post('get-currency-deposit-rate', 'DepositController@currencyDepositRate')->name('currencyDepositRate');
                Route::post('currency-deposit-process', 'DepositController@currencyDepositProcess')->name('currencyDepositProcess');
                Route::get('currency-deposit-history', 'DepositController@currencyDepositHistory')->name('currencyDepositHistory');
            });

            Route::post('get-convert-currency-amount', 'DepositController@getCurrencyRate');

            // fiat withdrawal
            Route::get('fiat-withdrawal','FiatWithdrawalController@fiatWithdrawal')->name('fiatWithdrawal');
            Route::post('get-fiat-withdrawal-rate','FiatWithdrawalController@getFiatWithdrawalRate')->name('getFiatWithdrawalRate');
            Route::post('fiat-withdrawal-process','FiatWithdrawalController@fiatWithdrawalProcess')->name('fiatWithdrawalProcess');
            Route::get('fiat-withdrawal-history', 'FiatWithdrawalController@fiatWithdrawHistory')->name('fiatWithdrawHistory');
            Route::post('get-paystack-payment-url', 'PaystackPaymentController@getPaystackPaymentURL');
            Route::post('verification-paystack-payment', 'PaystackPaymentController@verificationPaystackPayment');

            // User Bank
            Route::get('user-bank-list','UserBankController@UserbankGet')->name("UserbankGet");
            Route::post('user-bank-save','UserBankController@UserBankSave')->name("UserBankSave");
            Route::post('user-bank-delete','UserBankController@UserBankDelete')->name("UserBankDelete");

            // staking
            Route::group(['group' => 'staking','prefix'=>'staking', 'middleware' => 'staking'], function () {

                Route::post('get-total-investment-bonus', 'StakingOfferController@getTotalInvestmentBonus');
                Route::post('investment-submit', 'StakingOfferController@submitInvestment');
                Route::post('investment-canceled', 'StakingOfferController@canceledInvestment');
                Route::get('investment-list', 'StakingOfferController@investmentList');
                Route::get('investment-details', 'StakingOfferController@investmentDetails');

                Route::get('earning-list', 'StakingOfferController@earningList');

                Route::get('investment-statistics', 'StakingOfferController@investmentStatistics');
                Route::get('investment-get-payment-list', 'StakingOfferController@investmentGetPaymentList');

            });

            // future trade
            Route::group(['group' => 'future-trade','prefix'=>'future-trade', 'middleware'=>'createFutureWallet'], function () {
                Route::get('common-settings', 'FutureTradeController@commonSettings');

                Route::get('wallet-list', 'FutureTradeController@walletList');
                Route::post('wallet-balance-transfer', 'FutureTradeController@walletBalanceTransfer');
                Route::get('transfer-history', 'FutureTradeController@walletTransferHistory');

                Route::get('coin-pair-list', 'FutureTradeController@coinPairList');
                Route::post('preplace-order-data', 'FutureTradeController@prePlaceOrderData');
                Route::post('placed-buy-order', 'FutureTradeController@placedBuyOrder');
                Route::post('update-profit-loss-long-short-order', 'FutureTradeController@updateProfitLossLongShortOrder');

                Route::post('placed-sell-order', 'FutureTradeController@placedSellOrder');

                Route::get('get-long-short-position-order-list', 'FutureTradeController@getLongShortPositionOrderList');
                Route::get('get-long-short-open-order-list', 'FutureTradeController@getLongShortOpenOrderList');
                Route::get('get-long-short-order-history', 'FutureTradeController@getLongShortOrderHistory');
                Route::get('get-long-short-transaction-history', 'FutureTradeController@getLongShortTransactionHistory');
                Route::get('get-long-short-trade-history', 'FutureTradeController@getLongShortTradeHistory');

                Route::post('close-long-short-order', 'FutureTradeController@closeLongShortOrder');
                Route::post('close-long-short-all-orders', 'FutureTradeController@closeLongShortAllOrders');

                Route::post('get-future-order-calculation', 'FutureTradeController@getFutureTradeOrderCalculation');

                Route::post('canceled-long-short-order', 'FutureTradeController@canceledLongShortOrder');
                Route::post('order-details', 'FutureTradeController@orderDetails');

                //
                Route::get('get-my-all-orders-app', 'FutureTradeController@getFutureTradeOrdersApp');
                Route::get('get-my-trades-app', 'FutureTradeController@getFutureTradeMyExchangeTradesApp');
                Route::post('cancel-open-order-app', 'FutureTradeController@deleteFutureTradeMyOrderApp');
                Route::get('get-tp-sl-details-{uid}', 'FutureTrade\FutureTradeReportController@getTpSlDetails');

                Route::get('test', 'FutureTradeController@test');
            });

        });
        Route::group(['namespace'=>'Api\User','group' => 'gift_card','prefix'=>'gift-card'], function () {
            Route::get('gift-card-main-page', 'GiftCardController@giftCardMainPageData');
            Route::get('gift-cards', 'GiftCardController@giftCards');
        });
        Route::group(['namespace'=>'Api\User','group' => 'gift_card','prefix'=>'gift-card','middleware' => ['auth:api','api-user','last_seen']], function () {
            Route::post('buy-card', 'GiftCardController@buyGiftCard');
            Route::post('update-card', 'GiftCardController@updateGiftCard');
            Route::get('buy-card-page-data', 'GiftCardController@buyGiftCardPageData');
            Route::get('check-card', 'GiftCardController@checkGiftCard');
            Route::get('redeem-card', 'GiftCardController@redeemGiftCard');
            Route::get('my-gift-card-list', 'GiftCardController@giftCardList');
            Route::get('gift-card-wallet-data', 'GiftCardController@buyGiftCardPageWalletData');
            Route::get('add-gift-card', 'GiftCardController@addGiftCard');
            Route::get('gift-card-themes-page', 'GiftCardController@allGiftCardThemePageData');
            Route::get('get-gift-card-themes', 'GiftCardController@getGiftCardTheme');
            Route::get('send-gift-card', 'GiftCardController@sendGiftCard');
            Route::get('my-gift-card-page', 'GiftCardController@myGiftCardPageData');
            Route::get('get-gift-card-learn-more-page', 'GiftCardController@getGiftCardLearnMorePage');
            Route::post('get-redeem-code', 'GiftCardController@getRedeemCode');
        });

        Route::group(['namespace'=>'Api\User'], function () {
            Route::group(['group' => 'future-trade','prefix'=>'future-trade'] , function(){
                Route::get('get-market-pair-data', 'FutureTradeController@getFutureTradeMarketPairData');
                Route::get('get-all-orders-app', 'FutureTradeController@getFutureAllOrdersApp');
                Route::get('app-get-pair', 'FutureTradeController@appFutureTradeGetAllPair');
                Route::get('app-dashboard/{pair?}', 'FutureTradeController@appFutureTradeDashboard');
                Route::get('get-market-trades-app', 'FutureTradeController@getFutureTradeMarketTradesApp');
                Route::get('get-chart-data-app', 'FutureTradeController@getFutureTradeChartDataApp');
                Route::get('get-all-buy-orders-app', 'FutureTradeController@getFutureTradeAllBuyOrdersApp');
                Route::get('get-all-sell-orders-app', 'FutureTradeController@getFutureTradeAllSellOrdersApp');
                Route::get('all-buy-orders-history-app', 'FutureTradeController@getFutureTradeAllOrdersHistoryBuyApp');
                Route::get('all-sell-orders-history-app', 'FutureTradeController@getFutureTradeAllOrdersHistorySellApp');
                Route::get('all-transaction-history-app', 'FutureTradeController@getFutureTradeAllTransactionHistoryApp');
                Route::get('get-exchange-all-orders-app', 'FutureTradeController@getFutureTradeExchangeAllOrdersApp');
                Route::get('get-exchange-market-trades-app', 'FutureTradeController@getFutureTradeExchangeMarketTradesApp');
                Route::get('get-exchange-market-details-app', 'FutureTradeController@getFutureTradeExchangeMarketDetailsApp');
            });

        });

        // Currency trade route
        Route::group(['namespace'=>'Api\User', 'middleware' => ['auth:api','api-user','last_seen']], function () {
            Route::get('wallet-currency-deposit','DepositController@getCurrencyDepositPageData');
            Route::post('wallet-currency-deposit','DepositController@currencyWalletDepositProcess');
            Route::get('wallet-currency-deposit-history','DepositController@currencyWalletDepositHistory');
            Route::get('wallet-currency-withdraw','FiatWithdrawalController@getWalletCurrencyWithdrawalPage');
            Route::post('wallet-currency-withdraw','FiatWithdrawalController@fiatWalletWithdrawalProcess');
            Route::get('wallet-currency-withdraw-history','FiatWithdrawalController@fiatWalletWithdrawalHistory');
        });

        Route::group(['namespace'=>'Api', 'middleware' => ['checkApi']], function () {
            Route::get('latest-blog-list','LandingController@latestBlogList');
        });

        Route::group(['namespace'=>'Api\User', 'middleware' => ['auth:api','api-user','last_seen']], function () {
            Route::get('get-wallet-balance-details', "WalletController@getWalletBalanceDetails");
            Route::get('get-api-settings', "ProfileController@getApiSettings");
            Route::get('get-api-white-list', "ProfileController@getApiWhiteList");
            Route::get('white-list-{id}-{type}-{value}', "ProfileController@changeApiWhiteListStatus");
            Route::get('api-white-list-delete-{id}', "ProfileController@deleteApiWhiteList");
            Route::post('update-api-settings', "ProfileController@updateApiSettings");
            Route::post('add-api-white-list', "ProfileController@addApiWhiteList");
        });

        // api Secret key checker supported list
        Route::group(['namespace'=>'Api\User', 'middleware' => ['apiSecretCheck','auth:api','api-user','publicApiCheck','privateIpRate','last_seen']], function () {
            Route::get('profile', 'ProfileController@profile');

            // wallet
            Route::get('wallet-list','WalletController@walletList');
            Route::get('wallet-total-value','WalletController@walletTotalValue');
            Route::get('wallet-deposit-{id}','WalletController@walletDeposit');
            Route::get('wallet-withdrawal-{id}','WalletController@walletWithdrawal');
            Route::post('wallet-withdrawal-process','WalletController@walletWithdrawalProcess')->middleware('kycVerification:kyc_withdrawal_setting_status');
            Route::post('pre-withdrawal-process','WalletController@preWithdrawalProcess');
            Route::post('get-wallet-network-address','WalletController@getWalletNetworkAddress');

            //Dashboard and reports
            Route::get('get-all-buy-orders-app', 'ExchangeController@getExchangeAllBuyOrdersApp')->name('getExchangeAllBuyOrdersApp');
            Route::get('get-all-sell-orders-app', 'ExchangeController@getExchangeAllSellOrdersApp')->name('getExchangeAllSellOrdersApp');

            Route::get('get-my-all-orders-app', 'ExchangeController@getMyExchangeOrdersApp')->name('getMyExchangeOrdersApp');
            Route::get('get-my-trades-app', 'ExchangeController@getMyExchangeTradesApp')->name('getMyExchangeTradesApp');
            Route::post('cancel-open-order-app', 'ExchangeController@deleteMyOrderApp')->name('deleteMyOrderApp');
            Route::get('all-buy-orders-history-app', 'ReportController@getAllOrdersHistoryBuyApp')->name('getAllOrdersHistoryBuyApp');
            Route::get('all-sell-orders-history-app', 'ReportController@getAllOrdersHistorySellApp')->name('getAllOrdersHistorySellApp');
            Route::get('all-transaction-history-app', 'ReportController@getAllTransactionHistoryApp')->name('getAllTransactionHistoryApp');
            Route::get('get-all-stop-limit-orders-app', 'ReportController@getExchangeAllStopLimitOrdersApp')->name('getExchangeAllStopLimitOrdersApp');
            Route::get('referral-history', 'ReportController@getReferralHistory');

            Route::get('wallet-history-app', 'WalletController@walletHistoryApp')->name('walletHistoryApp');
            Route::get('limit-order-tolerence', "TolerenceController@getTolerence");
           
            Route::post('buy-limit-app', "BuyOrderController@placeBuyLimitOrderApp")->name('placeBuyLimitOrderApp')->middleware('kycVerification:kyc_trade_setting_status');
            Route::post('buy-market-app', "BuyOrderController@placeBuyMarketOrderApp")->name('placeBuyMarketOrderApp')->middleware('kycVerification:kyc_trade_setting_status');;
            Route::post('buy-stop-limit-app', "BuyOrderController@placeBuyStopLimitOrderApp")->name('placeBuyStopLimitOrderApp')->middleware('kycVerification:kyc_trade_setting_status');;
            Route::post('sell-limit-app', "SellOrderController@placeSellLimitOrderApp")->name('placeSellLimitOrderApp')->middleware('kycVerification:kyc_trade_setting_status');;
            Route::post('sell-market-app', "SellOrderController@placeSellMarketOrderApp")->name('placeSellMarketOrderApp')->middleware('kycVerification:kyc_trade_setting_status');;
            Route::post('sell-stop-limit-app', "SellOrderController@placeStopLimitSellOrderApp")->name('placeStopLimitSellOrderApp')->middleware('kycVerification:kyc_trade_setting_status');;


        });
        Route::group(['namespace'=>'Api\User', 'middleware' => ['apiSecretCheck','auth:api','api-user','last_seen']], function () {
            Route::get('get-networks-list','TransactionDepositController@getNetwork');
            Route::get('get-coin-network','TransactionDepositController@getCoinNetwork');
            Route::post('check-coin-transaction','TransactionDepositController@checkCoinTransaction');
        });
    });
});

