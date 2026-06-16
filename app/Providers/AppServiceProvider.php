<?php

namespace App\Providers;

use App\Console\Commands\BotOrderCleanerCommand;
use App\Contracts\Repositories\BotCoinPairRepositoryInterface;
use App\Contracts\Repositories\BuyOrderRepositoryInterface;
use App\Contracts\Repositories\CoinSettingRepositoryInterface;
use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Contracts\Repositories\FindableByUserIdAndCoinId;
use App\Contracts\Repositories\MarketOverViewCoinPairRepositoryInterface;
use App\Contracts\Repositories\OrderCoinPairRepositoryInterface;
use App\Contracts\Repositories\SellOrderRepositoryInterface;
use App\Contracts\Repositories\StopLimitRepositoryInterface;
use App\Contracts\Repositories\TradeFeeFinderRepositoryInterface;
use App\Contracts\Repositories\TradeFeeRepositoryInterface;
use App\Contracts\Repositories\TradeTransactionRepositoryInterface;
use App\Contracts\Repositories\WalletAddressHistoryRepositoryInterface;
use App\Http\Repositories\BotCoinPairRepository;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\CoinSettingRepository;
use App\Http\Repositories\Factories\OrderRepositoryFactory;
use App\Http\Repositories\MarketOverviewCoinPairRepository;
use App\Http\Repositories\OrderCoinPairRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\StopLimitRepository;
use App\Http\Repositories\Trade\TradeTransactionRepository;
use App\Http\Repositories\Trade\WalletRepository;
use App\Http\Repositories\TradeFeeFinderRepositoy;
use App\Http\Repositories\TradeFeeRepository;
use App\Http\Repositories\TransactionRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Repositories\WalletAddressHistoryRepository;
use App\Http\Services\Paginator;
use App\Http\Services\TradeServices\FeeCheckerAndRefundService;
use App\Model\Buy;
use App\Model\CoinPair;
use App\Model\CoinSetting;
use App\Model\Sell;
use App\Model\StopLimit;
use App\Model\UserWallet;
use App\Services\TradingBotServices\ProcessedBotOrderRemoverService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            BuyOrderRepositoryInterface::class,
            function (Application $app) {
                return new BuyOrderRepository(Buy::class);
            }
        );

        $this->app->bind(
            SellOrderRepositoryInterface::class,
            function (Application $app) {
                return new SellOrderRepository(Sell::class);
            }
        );

        $this->app->bind(
            StopLimitRepositoryInterface::class,
            function (Application $app) {
                return new StopLimitRepository(new StopLimit);
            }
        );

        $this->app->bind(
            OrderRepositoryFactoryInterface::class,
            OrderRepositoryFactory::class
        );

        $this->app->bind(
            CoinSettingRepositoryInterface::class,
            function (Application $app) {
                return new CoinSettingRepository(new CoinSetting);
            }
        );

        $this->app->bind(
            WalletAddressHistoryRepositoryInterface::class,
            WalletAddressHistoryRepository::class
        );

        $this->app->when(FeeCheckerAndRefundService::class)
            ->needs(FindableByUserIdAndCoinId::class)
            ->give(function () {
                return new WalletRepository(new UserWallet);
            });

        $this->app->bind(UserWalletRepository::class, function () {
            return new UserWalletRepository(new UserWallet);
        });

        $this->app->bind(
            TradeTransactionRepositoryInterface::class,
            TradeTransactionRepository::class
        );

        $this->app->bind(
            BotCoinPairRepositoryInterface::class,
            BotCoinPairRepository::class
        );

        $this->app->when(ProcessedBotOrderRemoverService::class)
            ->needs(TransactionRepository::class)
            ->give(function () {
                return new TransactionRepository(new \App\Model\Transaction);
            });

        $this->app->bind(
            OrderCoinPairRepositoryInterface::class,
            OrderCoinPairRepository::class
        );

        $this->app->bind(
            TradeFeeRepositoryInterface::class,
            TradeFeeRepository::class
        );

        $this->app->bind(CoinPairRepository::class, function () {
            return new CoinPairRepository(new CoinPair);
        });

        $this->app->bind(
            TradeFeeFinderRepositoryInterface::class,
            TradeFeeFinderRepositoy::class
        );

        $this->app->bind(
            MarketOverViewCoinPairRepositoryInterface::class,
            MarketOverviewCoinPairRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('strong_pass', function ($attribute, $value, $parameters, $validator) {
            return is_string($value);
        });

        Passport::routes();

        /* ADD THIS LINES */
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);

        if (function_exists('bcscale')) {
            bcscale(8);
        }
        if (DB::connection()->getDatabaseName()) {
            if (Schema::hasTable('admin_settings')) {
                $adm_setting = allsetting();
                if (!defined('ADMIN_SETTINGS_ARRAY')) {
                    define('ADMIN_SETTINGS_ARRAY', $adm_setting);
                }

                $capcha_site_key = isset($adm_setting['NOCAPTCHA_SITEKEY']) ? $adm_setting['NOCAPTCHA_SITEKEY'] : env('NOCAPTCHA_SITEKEY');
                $capcha_secret_key = isset($adm_setting['NOCAPTCHA_SECRET']) ? $adm_setting['NOCAPTCHA_SECRET'] : env('NOCAPTCHA_SECRET');

                config(['captcha.sitekey' => $capcha_site_key]);
                config(['captcha.secret' => $capcha_secret_key]);
            }
        }
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new Paginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
