<?php

namespace Database\Seeders;

use App\Model\PermissionFromData;
use App\User;
use Illuminate\Database\Seeder;

class PermissionFromDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('id',1)->first();
        if ($user) {
            $user->update(['super_admin' => STATUS_ACTIVE]);
        }
        $data = [
            //Dashboard
            //['group' => 'dashboard','action' => 'View','for' => 'User Dashboard Page','route' => $this->filtertext('adminDashboard')],

            //User
            ['group' => 'user','action' => 'View','for' => 'User Management Page','route' => $this->filtertext('adminUsers')],
            ['group' => 'user','action' => 'Create','for' => 'User Add','route' => $this->filtertext('admin.UserAddEdit')],
            ['group' => 'user','action' => 'Edit','for' => 'User Edit','route' => $this->filtertext('admin.UserEdit')],
            ['group' => 'user','action' => 'Delete','for' => 'User Delete','route' => $this->filtertext('admin.user.delete')],
            ['group' => 'user','action' => 'Suspend','for' => 'User Suspend','route' => $this->filtertext('admin.user.suspend')],

            //KYC
            ['group' => 'pending_id','action' => 'View','for' => 'Pending KYC Page','route' => $this->filtertext('adminUserIdVerificationPending')],
            ['group' => 'pending_id','action' => 'Info','for' => 'View User KYC Details','route' => $this->filtertext('adminUserDetails')],
            ['group' => 'pending_id','action' => 'Accept','for' => 'Accept User KYC','route' => $this->filtertext('adminUserVerificationActive')],
            ['group' => 'pending_id','action' => 'Reject','for' => 'Reject User KYC','route' => $this->filtertext('varificationReject')],

            //Admin
            ['group' => 'role','action' => 'View','for' => 'Admin List','route' => $this->filtertext('adminList')],
            ['group' => 'role','action' => 'Add/Edit','for' => 'Admin Add/Edit','route' => $this->filtertext('addEditAdmin')],
            ['group' => 'role','action' => 'Delete','for' => 'Admin Delete','route' => $this->filtertext('deleteAdminProfile')],

            //Role
            ['group' => 'role','action' => 'View','for' => 'Admin Role List','route' => $this->filtertext('adminRoleList')],
            ['group' => 'role','action' => 'Add','for' => 'Add Admin Role','route' => $this->filtertext('adminRoleSave')],
            ['group' => 'role','action' => 'Delete','for' => 'Delete Admin Role','route' => $this->filtertext('adminRoleDelete')],
            ['group' => 'role','action' => 'Add/Edit','for' => 'Add/Edit Role Permission','route' => $this->filtertext('adminRolePermissionSave')],

            //Coin
            ['group' => 'coin_list','action' => 'View','for' => 'View Coin List','route' => $this->filtertext('adminCoinList')],
            ['group' => 'coin_list','action' => 'View','for' => 'View Coin Edit Page','route' => $this->filtertext('adminCoinEdit')],
            ['group' => 'coin_list','action' => 'View','for' => 'View Coin Setting Page','route' => $this->filtertext('adminCoinSettings')],
            ['group' => 'coin_list','action' => 'Edit','for' => 'Edit Coin','route' => $this->filtertext('adminCoinSaveProcess')],
            ['group' => 'coin_list','action' => 'Create','for' => 'Create New Coin','route' => $this->filtertext('adminSaveCoin')],
            ['group' => 'coin_list','action' => 'Delete','for' => 'Delete Coin','route' => $this->filtertext('adminCoinDelete')],
            ['group' => 'coin_list','action' => 'Edit','for' => 'Edit Coin Status','route' => $this->filtertext('adminCoinStatus')],
            ['group' => 'coin_list','action' => 'Update','for' => 'Update Coin USD Price','route' => $this->filtertext('adminCoinRate')],

            //Coin Pair
            ['group' => 'coin_pair','action' => 'View','for' => 'View Coin Pair','route' => $this->filtertext('coinPairs')],
            ['group' => 'coin_pair','action' => 'Create','for' => 'Create Coin Pair','route' => $this->filtertext('saveCoinPairSettings')],
            ['group' => 'coin_pair','action' => 'Delete','for' => 'Delete Coin Pair','route' => $this->filtertext('coinPairsDelete')],
            ['group' => 'coin_pair','action' => 'Edit','for' => 'Edit Coin Pair Status','route' => $this->filtertext('changeCoinPairStatus')],
            ['group' => 'coin_pair','action' => 'Edit','for' => 'Edit Coin Pair Bot Status','route' => $this->filtertext('changeCoinPairBotStatus')],
            ['group' => 'coin_pair','action' => 'Update','for' => 'Update Coin Pair Chart','route' => $this->filtertext('coinPairsChartUpdate')],

            //Wallet List
            ['group' => 'wallet_list','action' => 'View','for' => 'View Users Wallets','route' => $this->filtertext('adminWalletList')],

            //Wallet Add Balance
            ['group' => 'send_wallet','action' => 'View','for' => 'View Send Wallet Balance Page','route' => $this->filtertext('adminSendWallet')],
            ['group' => 'send_wallet','action' => 'Send','for' => 'Send Wallet Balance','route' => $this->filtertext('adminSendBalanceProcess')],

            //Wallet Send Balance History
            ['group' => 'send_coin_list','action' => 'View','for' => 'Send Wallet Balance History','route' => $this->filtertext('adminWalletSendList')],

            //Wallet Send Balance History
            ['group' => 'swap_coin_history','action' => 'View','for' => 'Send Wallet Balance History','route' => $this->filtertext('adminSwapCoinHistory')],

            //All Transaction
            ['group' => 'transaction_all','action' => 'View','for' => 'All Transaction History','route' => $this->filtertext('adminTransactionHistory')],

            //Withdrawal Transaction
            ['group' => 'transaction_withdrawal','action' => 'View','for' => 'Withdrawal Transaction History','route' => $this->filtertext('adminPendingWithdrawal')],
            ['group' => 'transaction_withdrawal','action' => 'Accept','for' => 'Withdrawal Transaction Accept','route' => $this->filtertext('adminAcceptPendingWithdrawal')],
            ['group' => 'transaction_withdrawal','action' => 'Reject','for' => 'Withdrawal Transaction Reject','route' => $this->filtertext('adminRejectPendingWithdrawal')],
            ['group' => 'transaction_withdrawal','action' => 'View','for' => 'Withdrawal Referral History','route' => $this->filtertext('adminWithdrawalReferralHistory')],


            //Transaction deposit
            ['group' => 'transaction_deposit','action' => 'View','for' => 'View Deposit Pending History','route' => $this->filtertext('adminPendingDeposit')],
            ['group' => 'transaction_deposit','action' => 'View','for' => 'Accept Pending Deposit','route' => $this->filtertext('adminPendingDepositAcceptProcess')],

            //Check Deposit
            ['group' => 'check_deposit','action' => 'View','for' => 'View Check Deposit Page','route' => $this->filtertext('adminCheckDeposit')],
            ['group' => 'check_deposit','action' => 'Submit','for' => 'Check Deposit','route' => $this->filtertext('submitCheckDeposit')],

            //Trade Report
            ['group' => 'buy_order','action' => 'View','for' => 'View Buy Order History Page','route' => $this->filtertext('adminAllOrdersHistoryBuy')],
            ['group' => 'sell_order','action' => 'View','for' => 'Accept Pending Deposit','route' => $this->filtertext('adminAllOrdersHistorySell')],
            ['group' => 'stop_limit','action' => 'View','for' => 'Accept Pending Deposit','route' => $this->filtertext('adminAllOrdersHistoryStopLimit')],
            ['group' => 'transaction','action' => 'View','for' => 'Accept Pending Deposit','route' => $this->filtertext('adminAllTransactionHistory')],
            ['group' => 'trade_referral','action' => 'View','for' => 'View trade referral History','route' => $this->filtertext('adminAllTradeReferralHistory')],

            //Fiat Deposit
            ['group' => 'pending_deposite_list','action' => 'View','for' => 'Fiat Pending Deposit Page','route' => $this->filtertext('currencyDepositList')],
            ['group' => 'pending_deposite_list','action' => 'Accept','for' => 'Accept Fiat Pending Deposit','route' => $this->filtertext('currencyDepositAccept')],
            ['group' => 'pending_deposite_list','action' => 'Reject','for' => 'Reject Fiat Pending Deposit','route' => $this->filtertext('currencyDepositReject')],

            //Fiat Details Bank
            ['group' => 'bank_list','action' => 'View','for' => 'View Bank List','route' => $this->filtertext('bankList')],
            ['group' => 'bank_list','action' => 'Add/Edit','for' => 'Add/Edit Bank','route' => $this->filtertext('bankStore')],
            ['group' => 'bank_list','action' => 'Edit','for' => 'Edit Bank Status','route' => $this->filtertext('bankStatusChange')],
            ['group' => 'bank_list','action' => 'Delete','for' => 'Delete Bank','route' => $this->filtertext('bankDelete')],

            //Fiat Details Payment Method
            ['group' => 'payment_method_list','action' => 'View','for' => 'View Payment Method List','route' => $this->filtertext('currencyPaymentMethod')],
            ['group' => 'payment_method_list','action' => 'Add/Edit','for' => 'Add/Edit Payment Method','route' => $this->filtertext('currencyPaymentMethodStore')],
            ['group' => 'payment_method_list','action' => 'Edit','for' => 'Edit Payment Method Status','route' => $this->filtertext('currencyPaymentMethodStatus')],
            ['group' => 'payment_method_list','action' => 'Delete','for' => 'Delete Payment Method','route' => $this->filtertext('currencyPaymentMethodDelete')],

            //Fiat Withdrawal
            ['group' => 'fiat_withdraw_list','action' => 'View','for' => 'View Fiat Withdrawal List','route' => $this->filtertext('fiatWithdrawList')],
            ['group' => 'fiat_withdraw_list','action' => 'Accept','for' => 'Accept Fiat Withdrawal','route' => $this->filtertext('fiatWithdrawAccept')],
            ['group' => 'fiat_withdraw_list','action' => 'Reject','for' => 'Reject Fiat Withdrawal','route' => $this->filtertext('fiatWithdrawReject')],

            //Fiat Currency
            ['group' => 'currency_list','action' => 'View','for' => 'View Fiat Currency List','route' => $this->filtertext('adminFiatCurrencyList')],
            ['group' => 'currency_list','action' => 'Add','for' => 'Add Fiat Currency','route' => $this->filtertext('adminFiatCurrencySaveProcess')],
            ['group' => 'currency_list','action' => 'Delete','for' => 'Reject Fiat Currency','route' => $this->filtertext('adminFiatCurrencyDelete')],
            ['group' => 'currency_list','action' => 'Edit','for' => 'Edit Fiat Currency Status','route' => $this->filtertext('adminCurrencyStatus')],

            //Pending token
            ['group' => 'pending_token_deposit','action' => 'View','for' => 'View Pending Token History','route' => $this->filtertext('adminPendingDepositHistory')],
            ['group' => 'pending_token_deposit','action' => 'Accept','for' => 'Accept Pending Token','route' => $this->filtertext('adminPendingDepositAccept')],
            ['group' => 'pending_token_deposit','action' => 'Reject','for' => 'Reject Pending Token','route' => $this->filtertext('adminPendingDepositReject')],

            //Token Gas History
            ['group' => 'token_gas','action' => 'View','for' => 'View Token Gas Send History','route' => $this->filtertext('adminGasSendHistory')],

            //Token Gas History
            ['group' => 'token_receive_history','action' => 'View','for' => 'View Token Receive History','route' => $this->filtertext('adminTokenReceiveHistory')],

            // General Settings
            ['group' => 'general','action' => 'View','for' => 'View and Update General Settings','route' => $this->filtertext('adminSettings')],

            // Feature Settings
            ['group' => 'feature_settings','action' => 'View','for' => 'View and Update Feature Settings','route' => $this->filtertext('adminFeatureSettings')],

            //Theme Setting
            ['group' => 'theme_setting','action' => 'View','for' => 'Theme Setting Page','route' => $this->filtertext('themesSettingsPage')],
            ['group' => 'theme_setting','action' => 'Edit','for' => 'User Navber Edit','route' => $this->filtertext('themeNavebarSettingsSave')],
            ['group' => 'theme_setting','action' => 'Reset','for' => 'Reset Theme Color Settings','route' => $this->filtertext('resetThemeColorSettings')],
            ['group' => 'theme_setting','action' => 'Edit','for' => 'Edit Theme Color Settings','route' => $this->filtertext('addEditThemeSettingsStore')],
            ['group' => 'theme_setting','action' => 'Edit','for' => 'Edit Theme Settings','route' => $this->filtertext('themesSettingSave')],

            // Api Settings
            ['group' => 'api_settings','action' => 'View','for' => 'View and Update Api Settings','route' => $this->filtertext('adminCoinApiSettings')],

            // KYC Settings
            ['group' => 'kyc_settings','action' => 'View','for' => 'View and Update KYC Settings','route' => $this->filtertext('kycList')],

            // KYC Settings
            ['group' => 'google_analytics','action' => 'View','for' => 'View and Update Google Analytics Settings','route' => $this->filtertext('googleAnalyticsAdd')],

            // Lang Settings
            ['group' => 'lang_list','action' => 'View','for' => 'View and Update Google Analytics Settings','route' => $this->filtertext('adminLanguageList')],

            // Country Settings
            ['group' => 'country_list','action' => 'View','for' => 'View and Update Country Settings','route' => $this->filtertext('countryList')],

            // Tow Factor Settings
            ['group' => 'two_factor','action' => 'View','for' => 'View and Update Tow Factor Settings','route' => $this->filtertext('twoFactor')],

            // Tow Factor Settings
            ['group' => 'seo_manager','action' => 'View','for' => 'View and Update SEO Manager Settings','route' => $this->filtertext('seoManagerAdd')],

            // Tow Factor Settings
            ['group' => 'config','action' => 'View','for' => 'View and Update Configeration','route' => $this->filtertext('adminConfiguration')],

            //F.A.Q
            ['group' => 'faq','action' => 'View','for' => 'View FAQ List Page','route' => $this->filtertext('adminFaqList')],
            ['group' => 'faq','action' => 'Add/Edit','for' => 'Add/Edit FAQ','route' => $this->filtertext('adminFaqSave')],
            ['group' => 'faq','action' => 'Delete','for' => 'Delete FAQ','route' => $this->filtertext('adminFaqDelete')],

            // Landing Page Setting
            ['group' => 'landing','action' => 'View','for' => 'View and Update Landing Page','route' => $this->filtertext('adminLandingSetting')],

            // Custom Page
            ['group' => 'custom_pages','action' => 'View','for' => 'View Custom Page List','route' => $this->filtertext('adminCustomPageList')],
            ['group' => 'custom_pages','action' => 'Add/Edit','for' => 'Add/Edit Custom Page','route' => $this->filtertext('adminCustomPageSave')],
            ['group' => 'custom_pages','action' => 'Delete','for' => 'Delete Custom Page','route' => $this->filtertext('adminCustomPageDelete')],

            //Bannar
            ['group' => 'banner','action' => 'View','for' => 'View Landing Banner Page','route' => $this->filtertext('adminBannerList')],
            ['group' => 'banner','action' => 'Add/Edit','for' => 'Add/Edit Landing Banner','route' => $this->filtertext('adminBannerSave')],
            ['group' => 'banner','action' => 'Delete','for' => 'Delete Landing Banner','route' => $this->filtertext('adminBannerDelete')],

            //Landing Feature
            ['group' => 'feature','action' => 'View','for' => 'View Landing Feature List Page','route' => $this->filtertext('adminFeatureList')],
            ['group' => 'feature','action' => 'Add/Edit','for' => 'Add/Edit Landing Feature','route' => $this->filtertext('adminFeatureSave')],
            ['group' => 'feature','action' => 'Delete','for' => 'Delete Landing Feature','route' => $this->filtertext('adminFeatureDelete')],

            //Landing Media
            ['group' => 'media','action' => 'View','for' => 'View Landing Media List Page','route' => $this->filtertext('adminSocialMediaList')],
            ['group' => 'media','action' => 'Add/Edit','for' => 'Add/Edit Landing Media','route' => $this->filtertext('adminSocialMediaSave')],
            ['group' => 'media','action' => 'Delete','for' => 'Delete Landing Media','route' => $this->filtertext('adminFeatureDelete')],

            //Landing Announcement
            ['group' => 'announcement','action' => 'View','for' => 'View Landing Media List Page','route' => $this->filtertext('adminAnnouncementList')],
            ['group' => 'announcement','action' => 'Add/Edit','for' => 'Add/Edit Landing Media','route' => $this->filtertext('adminAnnouncementSave')],
            ['group' => 'announcement','action' => 'Delete','for' => 'Delete Landing Media','route' => $this->filtertext('adminAnnouncementDelete')],

            // Send Notification
            ['group' => 'announcement','action' => 'View','for' => 'View Send Notification Page','route' => $this->filtertext('sendNotification')],
            ['group' => 'announcement','action' => 'Send','for' => 'Send Notification To User','route' => $this->filtertext('sendNotificationProcess')],

            // Send Email
            ['group' => 'email','action' => 'View','for' => 'View Send Email Page','route' => $this->filtertext('sendEmail')],
            ['group' => 'email','action' => 'Send','for' => 'Send Email To User','route' => $this->filtertext('sendNotificationProcess')],

            // Send Notications
            ['group' => 'notify','action' => 'View','for' => 'View Send Notication Page','route' => $this->filtertext('sendNotification')],
            ['group' => 'notify','action' => 'Send','for' => 'Send Notications To User','route' => $this->filtertext('sendNotificationProcess')],

            //Progress Status List
            ['group' => 'progress-status-list','action' => 'View','for' => 'View Progress Status Page','route' => $this->filtertext('progressStatusList')],
            ['group' => 'progress-status-list','action' => 'Add/Edit','for' => 'Add/Edit Progress Status','route' => $this->filtertext('progressStatusSave')],
            ['group' => 'progress-status-list','action' => 'Delete','for' => 'Delete Progress Status','route' => $this->filtertext('progressStatusDelete')],

            //Progress Status List
            ['group' => 'progress-status-settings','action' => 'View','for' => 'View Progress Status Setting Page','route' => $this->filtertext('progressStatusSettings')],
            ['group' => 'progress-status-settings','action' => 'Edit','for' => 'Edit Progress Status Setting','route' => $this->filtertext('progressStatusSettingsUpdate')],

            //Logs
            ['group' => 'log','action' => 'View','for' => 'Admin Logs','route' => $this->filtertext('adminLogs')],

            //staking
            ['group' => 'staking','action' => 'View','for' => 'Staking Dashboard','route' => $this->filtertext('adminLogs')],


            //future trade
            ['group' => 'future-trade','action' => 'View','for' => 'Future Trade Dashboard','route' => $this->filtertext('futureTradeDashboard')],
            ['group' => 'future-trade','action' => 'View','for' => 'Future Trade Wallet List','route' => $this->filtertext('futureTradeWalletList')],
            ['group' => 'future-trade','action' => 'View','for' => 'Future Trade Transfer Balance History','route' => $this->filtertext('futureTradeTransferHistoryList')],

            //module
            ['group' => 'Addon','action' => 'P2P Addon','for' => 'P2P Addon Access','route' => 'P2P_Addon'],
            ['group' => 'Addon','action' => 'ICO Addon','for' => 'ICO Launchpad Addon Access','route' => 'ICO_Addon'],
            ['group' => 'Addon','action' => 'Support System Addon','for' => 'Knowledgebase Support System Addon Access','route' => 'Knowledgebase_Support_Addon'],
            ['group' => 'Addon','action' => 'Demo Trade Addon','for' => 'Demo Trade Addon Access','route' => 'Demo_Trade_Addon'],
            ['group' => 'Addon','action' => 'Blog News Addon','for' => 'Blog News Addon Access','route' => 'Blog_News_Addon'],

        ];
        PermissionFromData::truncate()->insert($data);
    }

    private function filtertext($value)
    {
        return str_replace('.','_',$value);
    }
}
