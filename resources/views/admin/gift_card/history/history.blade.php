@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('admin.gift_card.sidebar.sidebar',['menu'=>'history'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Gift Card')}}</li>
                    <li class="active-item">{{__('History')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-2">
                <ul class="nav user-management-nav profile-nav mb-3" id="pills-tab" role="tablist">
                    <li>
                        <a class=" active  nav-link " data-id="actived_card" data-toggle="pill" role="tab" data-controls="actived_card" aria-selected="true" href="#actived_card">
                            <span>{{__('Active Card History')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='redeemed_card') active @endif nav-link  " data-id="redeemed_card" data-toggle="pill" role="tab" data-controls="redeemed_card" aria-selected="true" href="#redeemed_card">
                            <span>{{__('Redeemed Card History')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='transferred_card') active @endif nav-link  " data-id="transferred_card" data-toggle="pill" role="tab" data-controls="transferred_card" aria-selected="true" href="#transferred_card">
                            <span>{{__('Transferred Card History')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='tradmin_card') active @endif nav-link  " data-id="tradmin_card" data-toggle="pill" role="tab" data-controls="tradmin_card" aria-selected="true" href="#tradmin_card">
                            <span>{{__('Trading Card History')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='locked_card') active @endif nav-link  " data-id="locked_card" data-toggle="pill" role="tab" data-controls="locked_card" aria-selected="true" href="#locked_card">
                            <span>{{__('Locked Card History')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-10">
                <div class="tab-content tab-pt-n" id="tabContent">
                    <div class="tab-pane fade show active " id="actived_card" role="tabpanel" aria-labelledby="general-setting-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="deposit_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Coin type')}}</th>
                                        <th>{{__('Wallet')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Lock')}}</th>
                                        <th>{{__('Created Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane active fade @if(isset($tab) && $tab=='redeemed_card') @endif" id="redeemed_card" role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="withdrawal_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Coin type')}}</th>
                                        <th>{{__('Wallet')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Lock')}}</th>
                                        <th>{{__('Created Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane active  fade @if(isset($tab) && $tab=='transferred_card') @endif" id="transferred_card" role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="transferred_card_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Coin type')}}</th>
                                        <th>{{__('Wallet')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Lock')}}</th>
                                        <th>{{__('Created Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane active  fade @if(isset($tab) && $tab=='tradmin_card') @endif" id="tradmin_card" role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="tradmin_card_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Coin type')}}</th>
                                        <th>{{__('Wallet')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Lock')}}</th>
                                        <th>{{__('Created Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane active  fade @if(isset($tab) && $tab=='locked_card') @endif" id="locked_card" role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="locked_card_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Coin type')}}</th>
                                        <th>{{__('Wallet')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Lock')}}</th>
                                        <th>{{__('Created Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
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
            function renderGiftCardDataTable(url,eliment){
                $(eliment).DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    responsive: false,
                    ajax: url,
                    order: [7, 'desc'],
                    autoWidth: false,
                    scrollX: true,
                    scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                    language: {
                        paginate: {
                            next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                            previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                        }
                    },
                    columns: [
                        {"data": "coin_type"},
                        {"data": "wallet_type"},
                        {"data": "user_id"},
                        {"data": "amount"},
                        {"data": "fees"},
                        {"data": "status"},
                        {"data": "lock"},
                        {"data": "created_at"}
                    ]
                });
            }
            renderGiftCardDataTable('{{route('giftCardHistory')}}?type=1','#deposit_table');
            renderGiftCardDataTable('{{route('giftCardHistory')}}?type=2','#withdrawal_table');
            renderGiftCardDataTable('{{route('giftCardHistory')}}?type=3','#transferred_card_table');
            renderGiftCardDataTable('{{route('giftCardHistory')}}?type=4','#tradmin_card_table');
            renderGiftCardDataTable('{{route('giftCardHistory')}}?type=5','#locked_card_table');
        })(jQuery)
    </script>
@endsection
