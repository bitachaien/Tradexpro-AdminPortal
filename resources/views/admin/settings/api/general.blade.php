@extends('admin.master', ['menu' => 'setting', 'sub_menu' => 'api_settings'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{ __('Settings') }}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3">
                <ul class="nav user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'payment') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="payment" href="#payment" role="tab"
                            aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('Coin Payment Settings') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'bitgo') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="bitgo" href="#bitgo" role="tab"
                            aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('Bitgo Settings') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'erc20') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="erc20" href="#erc20" role="tab"
                            aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('ERC20/BEP20/TRC20 Settings') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'crypto') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="crypto" href="#crypto" role="tab"
                            aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('CryptoCompare Api Settings') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'stripe') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="stripe" href="#stripe" role="tab"
                            aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('Stripe Settings') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'paystack') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="paystack" href="#paystack" role="tab"
                            aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('PayStack Settings') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'currency_exchange_api') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="currency_exchange_api" href="#currency_exchange_api"
                            role="tab" aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('Currency Exchange API') }}</span>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'razorpay') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="razorpay" href="#razorpay" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Razorpay Settings')}}</span>
                        </a>
                    </li> --}}
                    <li class="nav-item">
                        <a class="@if (isset($tab) && $tab == 'tron_grid') active @endif nav-link " id="pills-email-tab"
                            data-toggle="pill" data-controls="tron_grid" href="#tron_grid" role="tab"
                            aria-controls="pills-email" aria-selected="true">
                            <span>{{ __('TronGrid API') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane @if (isset($tab) && $tab == 'payment') show active @endif" id="payment"
                        role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.coin_payment')
                    </div>
                    <div class="tab-pane @if (isset($tab) && $tab == 'bitgo') show active @endif" id="bitgo"
                        role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.bitgo')
                    </div>
                    <div class="tab-pane @if (isset($tab) && $tab == 'erc20') show active @endif" id="erc20"
                        role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.erc20_settings')
                    </div>
                    <div class="tab-pane @if (isset($tab) && $tab == 'crypto') show active @endif" id="crypto"
                        role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.others')
                    </div>
                    <div class="tab-pane @if (isset($tab) && $tab == 'stripe') show active @endif" id="stripe"
                        role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.stripe')
                    </div>
                    <div class="tab-pane @if (isset($tab) && $tab == 'paystack') show active @endif" id="paystack"
                        role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.paystack')
                    </div>
                    <div class="tab-pane @if (isset($tab) && $tab == 'currency_exchange_api') show active @endif"
                        id="currency_exchange_api" role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.currency_exchange_settings')
                    </div>
                    {{-- <div class="tab-pane @if (isset($tab) && $tab == 'razorpay') show active @endif" id="razorpay"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.razorpay')
                    </div> --}}
                    <div class="tab-pane @if (isset($tab) && $tab == 'tron_grid') show active @endif" id="tron_grid"
                        role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.api.tron_grid')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            $('.nav-link').on('click', function() {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                var str = '#' + $(this).data('controls');
                $('.tab-pane').removeClass('show active');
                $(str).addClass('show active');
            });


            $('#withdrawTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                bLengthChange: true,
                responsive: false,
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                ajax: '{{ route('networkFees') }}',
                order: [5, 'desc'],
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                columns: [{
                        "data": "coin_type",
                        searchable: true
                    },
                    {
                        "data": "rate_btc",
                        searchable: true
                    },
                    {
                        "data": "tx_fee",
                        searchable: true
                    },
                    {
                        "data": "is_fiat",
                        searchable: true
                    },
                    {
                        "data": "status",
                        searchable: true
                    },
                    {
                        "data": "last_update",
                        searchable: true
                    },
                ]
            });


        })(jQuery);

        $(document).on('click', '#sync_fees', function() {
            // swalConfirm("Do you really want to update network list ?").then(function (s) {
            //     if(s.value){
            window.location.href = '{{ route('networkFeesUpdate') }}';
            //     }
            // })
        });
    </script>
@endsection
