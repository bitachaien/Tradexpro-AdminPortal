@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'feature_settings'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Settings')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3 col-xl-2">
                <ul class="nav user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='cookie') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="payment" href="#payment" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Cookie Settings')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='live_chat') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="bitgo" href="#bitgo" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Live Chat Settings')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='swap') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="erc20" href="#erc20" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Swap Settings')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='currency_deposit') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="currency_deposit" href="#currency_deposit" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Fiat Deposit Withdrawal')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='faq_setting') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="faq_setting" href="#faq_setting" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('FAQ Settings')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='bot_setting') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="bot_setting" href="#bot_setting" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Trading Bot')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='gift_card') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="gift_card" href="#gift_card" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Gift Card')}}</span>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='future_trade') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="future_trade" href="#future_trade" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Future Trade')}}</span>
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='staking') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="staking" href="#staking" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Staking')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='social_lagin') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="social_lagin" href="#social_lagin" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('Social Login')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane @if(isset($tab) && $tab=='cookie') show active @endif" id="payment"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.cookie_setting')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='live_chat') show active @endif" id="bitgo"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.chat_api_setting')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='swap') show active @endif" id="erc20"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.swap_setting')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='currency_deposit') show active @endif" id="currency_deposit"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.deposit_setting')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='faq_setting') show active @endif" id="faq_setting"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.faq_setting')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='bot_setting') show active @endif" id="bot_setting"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.bot_setting')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='gift_card') show active @endif" id="gift_card"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.gift_card')
                    </div>
                    <!-- <div class="tab-pane @if(isset($tab) && $tab=='future_trade') show active @endif" id="future_trade"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.future_trade')
                    </div> -->
                    <div class="tab-pane @if(isset($tab) && $tab=='staking') show active @endif" id="staking"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.staking')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='social_lagin') show active @endif" id="social_lagin"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.settings.feature.social_login')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')

    <script>
        (function ($) {
            "use strict";

            $('.nav-link').on('click', function () {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                var str = '#' + $(this).data('controls');
                $('.tab-pane').removeClass('show active');
                $(str).addClass('show active');
            });

            $('#deleteBotOrders').on('click', function(){
                Swal.fire({
                title: '{{__("Are you sure?")}}',
                text: "{{__("You will not be able to revert this!")}}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons:true
                }).then((result) => {

                    if(result.isConfirmed)
                    {
                        $.ajax({
                            type: "GET",
                            url: "{{ route('adminDeleteBotOrders') }}",
                            success: function (data) {

                                if(data.success)
                                {
                                    VanillaToasts.create({
                                        text: data.message,
                                        backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                                        type: 'success',
                                        timeout: 5000
                                    });
                                }else{
                                    VanillaToasts.create({
                                        text: data.message,
                                        type: 'warning',
                                        timeout: 5000
                                    });
                                }
                            }
                        });
                    }
                })
            })
        })(jQuery)
    </script>
@endsection
