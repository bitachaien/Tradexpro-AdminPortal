<div class="row">
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-read">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #ea98a02d;">
                            <img src="{{asset('assets/admin/images/status-icons/team.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Total User')}}</p>
                            <h3>{{$total_user}}</h3>
                            {{-- <a href="{{ route('adminUsers') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                        </div>
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('adminUsers') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-average">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #aecddf3d;">
                            <img src="{{asset('assets/admin/images/status-icons/money.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Total User Coin')}}</p>
                            <h3>{{number_format($total_coin,8)}} BTC</h3>
                            {{-- <a href="{{ route('adminUserCoinList') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                        </div>
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
 
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('adminUserCoinList') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-average">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Total Earning')}}</p>
                            <h3>{{number_format($total_earning,8)}}</h3>
                            {{-- <a href="{{ route('adminEarningReport') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                        </div>
                        
                    </div>

                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('adminEarningReport') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-read">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #aecddf3d;">
                            <img src="{{asset('assets/admin/images/status-icons/active_buy_order.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Active Buy Order')}}</p>
                            <h3>{{$active_buy}}</h3>
                            {{-- <a href="{{ route('adminAllOrdersHistoryBuy') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                        </div>
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
 
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('adminAllOrdersHistoryBuy') }}">{{__("Show More")}}</a>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #aecddf3d;">
                            <img src="{{asset('assets/admin/images/status-icons/active_sell_order.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Active Sell Order')}}</p>
                            <h3>{{$active_sell}}</h3>
                            {{-- <a href="{{ route('adminAllOrdersHistorySell') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                        </div>
                    </div>
                    <div class="dropdown">
                       <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>

                        <div class="dropdown-menu">
                          <a class="dropdown-item" href="{{ route('adminAllTransactionHistory') }}">{{__("Show More")}}</a>
                          {{-- <a href="{{ route('adminAllTransactionHistory') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/total_transaction.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Total Transaction')}}</p>
                            <h3>{{number_format($total_transaction,2)}}</h3>
                            {{-- <a href="{{ route('adminAllTransactionHistory') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                        </div>
                        
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('adminAllTransactionHistory') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/pending_fiat_deposit.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Pending Fiat Deposit')}}</p>
                            <h3>{{ $fiat_pending_deposit ?? 0 }}</h3>
                        </div>
                        
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('currencyDepositList') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/fiat_success_deposit.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Fiat Success Deposit')}}</p>
                            <h3>{{ $fiat_deposit ?? 0 }}</h3>
                        </div>
                        
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('currencyDepositList') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/FiatWithdrawal.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Fiat Withdrawal')}}</p>
                            <h3>{{ $fiat_withdrawal ?? 0 }}</h3>
                        </div>
                        
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('fiatWithdrawList') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/this_week_swap.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('This Week Swap')}}</p>
                            <h3>{{ $swap ?? 0 }}</h3>
                        </div>
                        
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('adminSwapCoinHistory') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/staking_total_Investment.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Staking Total Investment')}}</p>
                            <h3>{{ $staking ?? 0 }}</h3>
                        </div>
                        
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('stakingDashboard') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-orange">
            <div class="card-body">
                <div class="status-card-inner">
                    <div class="d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center" style="min-width: 48px; min-height: 48px; border-radius: 50%; background-color: #718a714d;">
                            <img src="{{asset('assets/admin/images/status-icons/pending_crypto_deposit.svg')}}" class="img-fluid" alt="">
                        </div>
                        <div class="content ml-3">
                            <p>{{__('Pending Crypto Deposit')}}</p>
                            <h3>{{ $crypto_deposit ?? 0 }}</h3>
                        </div>
                        
                    </div>
                    <div class="dropdown">
                        <i class="fa fa-ellipsis-h" aria-hidden="true" data-toggle="dropdown" style="color: #cbcfd7; cursor: pointer"></i>
                         <div class="dropdown-menu">
                           <a class="dropdown-item" href="{{ route('adminPendingDeposit') }}">{{__("Show More")}}</a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
