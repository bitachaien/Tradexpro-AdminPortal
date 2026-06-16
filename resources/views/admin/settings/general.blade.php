@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'general'])
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
                    @if(isset($tab) && ($tab=='api_config'))
                    @else
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='general') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="general" href="#general" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{__('General')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='capcha') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="capcha" href="#capcha" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{__('Captcha Settings')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='email') active @endif nav-link " id="pills-add-user-tab"
                               data-toggle="pill" data-controls="email" href="#email" role="tab"
                               aria-controls="pills-add-user" aria-selected="true">
                                <span>{{__('Email')}} </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='email_template') active @endif nav-link " id="pills-add-user-tab"
                               data-toggle="pill" data-controls="email_template" href="#email_template" role="tab"
                               aria-controls="pills-add-user" aria-selected="true">
                                <span>{{__('Email Template Setting')}} </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='sms') active @endif nav-link " id="pills-sms-tab"
                               data-toggle="pill" data-controls="sms" href="#sms" role="tab" aria-controls="pills-sms"
                               aria-selected="true">
                                <span>{{__('SMS Settings')}} </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='referral') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="referral"
                               href="#referral"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Referral')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='maintenance_mode') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="maintenance_mode"
                               href="#maintenance_mode"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Maintenance Mode')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='exchange_layout') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="exchange_layout"
                               href="#exchange_layout"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Exchange Layout')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='cron') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="cron"
                               href="#cron"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Cron Setup')}}</span>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='fiat_withdrawal') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="fiat_withdrawal"
                               href="#fiat_withdrawal"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Fiat Withdrawal')}}</span>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='wallet_overview') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="wallet_overview"
                               href="#wallet_overview"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Wallet Overview')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='own_api') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="own_api"
                               href="#own_api"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Api Overview')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='signup_email_verification') active @endif nav-link "
                               id="pills-suspended-user-tab" data-toggle="pill" data-controls="signup_email_verification"
                               href="#signup_email_verification"
                               role="tab" aria-controls="pills-suspended-user" aria-selected="true">
                                <span>{{__('Signup Email Verification')}}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='api_config')  active @endif" id="api_config"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.settings.setting.api_config')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='general')  active @endif" id="general"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.settings.setting.general')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='capcha')  active @endif" id="capcha"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.settings.setting.captcha')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='email') show active @endif" id="email"
                         role="tabpanel" aria-labelledby="pills-add-user-tab">
                        @include('admin.settings.setting.email')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='email_template') show active @endif" id="email_template"
                         role="tabpanel" aria-labelledby="pills-add-user-tab">
                        @include('admin.settings.setting.email_template')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='sms') show active @endif" id="sms" role="tabpanel"
                         aria-labelledby="pills-sms-tab">
                        @include('admin.settings.setting.sms-settings')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='referral') show active @endif" id="referral"
                         role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.referral')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='maintenance_mode') show active @endif" id="maintenance_mode"
                         role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.maintenance-mode')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='exchange_layout') show active @endif" id="exchange_layout"
                         role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.include.exchange_layout')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='cron') show active @endif" id="cron"
                         role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.cron')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='fiat_withdrawal') show active @endif" id="fiat_withdrawal"
                         role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.fiat_withdrawal')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='wallet_overview') show active @endif" id="wallet_overview"
                            role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.wallet_overview')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='own_api') show active @endif" id="own_api"
                            role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.api_overview')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='signup_email_verification') show active @endif" id="signup_email_verification"
                            role="tabpanel" aria-labelledby="pills-suspended-user-tab">
                        @include('admin.settings.setting.signup_email_verification')
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
            $('#exchange_view').on('change', function () {
                let a = $(this).val();
                if (a == 1){
                    document.getElementById('layout1').classList.toggle("d-none");
                    document.getElementById('layout2').classList.toggle("d-block");
                    document.getElementById('layout1').classList.toggle("d-block");
                    document.getElementById('layout2').classList.toggle("d-none");
                } else {
                    document.getElementById('layout1').classList.toggle("d-none");
                    document.getElementById('layout2').classList.toggle("d-block");
                    document.getElementById('layout1').classList.toggle("d-block");
                    document.getElementById('layout2').classList.toggle("d-none");
                }
            });

            $('#choose_email_template').change(function () {
                let template_value = $(this).val();
                if (template_value == 1){
                    $('#template_number_one').removeClass('d-none').addClass('d-block');
                    $('#template_number_two').removeClass('d-block').addClass('d-none');
                    $('#template_number_three').removeClass('d-block').addClass('d-none');
                    $('#template_number_four').removeClass('d-block').addClass('d-none');
                } else if(template_value == 2) {
                    $('#template_number_one').removeClass('d-block').addClass('d-none');
                    $('#template_number_two').removeClass('d-none').addClass('d-block');
                    $('#template_number_three').removeClass('d-block').addClass('d-none');
                    $('#template_number_four').removeClass('d-block').addClass('d-none');
                }else if(template_value == 3)
                {
                    $('#template_number_one').removeClass('d-block').addClass('d-none');
                    $('#template_number_two').removeClass('d-block').addClass('d-none');
                    $('#template_number_three').removeClass('d-none').addClass('d-block');
                    $('#template_number_four').removeClass('d-block').addClass('d-none');
                }else if(template_value == 4)
                {
                    $('#template_number_one').removeClass('d-block').addClass('d-none');
                    $('#template_number_two').removeClass('d-block').addClass('d-none');
                    $('#template_number_three').removeClass('d-block').addClass('d-none');
                    $('#template_number_four').removeClass('d-none').addClass('d-block');
                }
            });


        })(jQuery)
    </script>
@endsection
