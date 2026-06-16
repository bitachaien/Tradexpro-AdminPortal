<div class="sidebar">
    <!-- logo -->
    <div class="logo">
        <a href="{{route('adminDashboard')}}">
            <img src="{{show_image(Auth::user()->id,'logo')}}" class="img-fluid" alt="">
        </a>
    </div><!-- /logo -->

    <!-- sidebar menu -->
    <div class="sidebar-menu">
        <nav>
            <ul id="metismenu">


{!! mainMenuRenderer('adminDashboard',__('Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}


{!! subMenuRenderer(__('User Wallet'),$menu ?? '', 'user_wallet','wallet.svg',[
    ['route' => 'futureTradeWalletList', 'title' => __('Wallet List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'wallet_list', 'route_param' => null],
    ['route' => 'futureTradeTransferHistoryList', 'title' => __('Transfer History'),'tab' => $sub_menu ?? '', 'tab_compare' => 'transfer_history_list', 'route_param' => null],
    
    
]) !!}

{!! subMenuRenderer(__('Order History'),$menu ?? '', 'future_trade_history','wallet.svg',[
    ['route' => 'futureTradePosition', 'title' => __('Position Order List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'position', 'route_param' => null],
    ['route' => 'getFutureTradeOpenOrderHistory', 'title' => __('Open Order List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'open_order', 'route_param' => null],
    ['route' => 'getFutureTradeOrderHistory', 'title' => __('Order List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'order', 'route_param' => null],
    ['route' => 'getFutureTradeHistory', 'title' => __('Trade List'),'tab' => $sub_menu ?? '', 'tab_compare' => 'trade', 'route_param' => null],
]) !!}

{!! mainMenuRenderer('futureTradeType',__('Future Trade History'),$menu ?? '','future_trade_history','Membership.svg') !!}

@php
    $futureTradeTransactionHistoryType = [];
    foreach (futureTradeTransactionHistoryType() as $key => $value) {
        $futureTradeTransactionHistoryType[] = [
            'route' => 'futureTradeTransactionHistory', 
            'title' => $value,
            'tab' => $sub_menu ?? '', 
            'tab_compare' => 'futureTradeTransactionHistory-'.$key, 
            'route_param' => ['type' => $key]
        ];
    }
@endphp

{!! subMenuRenderer(__('Trade Transaction History'),$menu ?? '', 'future_trade_transaction_history','staking.svg', $futureTradeTransactionHistoryType) !!}


{!! mainMenuRenderer('adminLogs',__('Logs'),$menu ?? '','log','logs.svg') !!} 


            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
