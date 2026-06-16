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


{!! mainMenuRenderer('stakingDashboard',__('Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}

{!! mainMenuRenderer('stakingCreateOffer',__('Create Offer'),$menu ?? '','staking_offer','coin.svg') !!}
{!! mainMenuRenderer('stakingOfferList',__('Offer List'),$menu ?? '','staking_offer_list','deposit.svg') !!}

{!! subMenuRenderer(__('Investment List'),$menu ?? '', 'staking_investment','Transaction-1.svg',[
    ['route' => 'stakingInvestmentList', 'title' => __('All Investment'),'tab' => $sub_menu ?? '', 'tab_compare' => 'staking_investment_status_', 'route_param' => null],
    ['route' => 'stakingInvestmentList', 'title' => __('Running Investment'),'tab' => $sub_menu ?? '', 'tab_compare' => 'staking_investment_status_'.STAKING_INVESTMENT_STATUS_RUNNING, 'route_param' => ['type'=>STAKING_INVESTMENT_STATUS_RUNNING] ],
    ['route' => 'stakingInvestmentList', 'title' => __('Canceled Investment'),'tab' => $sub_menu ?? '', 'tab_compare' => 'staking_investment_status_'.STAKING_INVESTMENT_STATUS_CANCELED, 'route_param' => ['type'=>STAKING_INVESTMENT_STATUS_CANCELED] ],
    ['route' => 'stakingInvestmentList', 'title' => __('Distributable Investment'),'tab' => $sub_menu ?? '', 'tab_compare' => 'staking_investment_status_'.STAKING_INVESTMENT_STATUS_UNPAID, 'route_param' => ['type'=>STAKING_INVESTMENT_STATUS_UNPAID] ],
    ['route' => 'stakingInvestmentList', 'title' => __('Distributed Investment'),'tab' => $sub_menu ?? '', 'tab_compare' => 'staking_investment_status_'.STAKING_INVESTMENT_STATUS_PAID, 'route_param' => ['type'=>STAKING_INVESTMENT_STATUS_PAID] ],
    
]) !!}

{!! mainMenuRenderer('stakingInvestmentPaymentList',__('Investment Payment History'),$menu ?? '','staking_payment_history','trade-report.svg') !!}
{!! mainMenuRenderer('stakingLandingSettings',__('Landing Page Settings'),$menu ?? '','staking_landing_page_settings','settings.svg') !!}



{!! mainMenuRenderer('adminLogs',__('Logs'),$menu ?? '','log','logs.svg') !!}


            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
