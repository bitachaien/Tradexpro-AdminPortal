<?php

namespace App\Providers;

use App\Model\CoinPair;
use App\Model\FutureTradeLongShort;
use App\Model\WithdrawHistory;
use App\Model\DepositeTransaction;
use App\Model\Transaction;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Observers\WithdrawHistoryObserver;
use App\Observers\DepositeTransactionObserver;
use App\Observers\TransactionObserver;
use App\Observers\CoinPairObserver;
use App\Observers\FutureTradeLongShortObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\OrderHasPlaced::class => [
            \App\Listeners\StartProcessingOrder::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        DepositeTransaction::observe(DepositeTransactionObserver::class);
        WithdrawHistory::observe(WithdrawHistoryObserver::class);
        Transaction::observe(TransactionObserver::class);
        FutureTradeLongShort::observe(FutureTradeLongShortObserver::class);
        CoinPair::observe(CoinPairObserver::class);
    }
}
