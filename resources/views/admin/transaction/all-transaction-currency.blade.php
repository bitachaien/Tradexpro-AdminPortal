@extends('admin.master',['menu'=>'transaction', 'sub_menu'=>'transaction_all_fiat'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Currency Transaction')}}</li>
                    <li class="active-item">{{__('All Currency History')}}</li>
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
                        <a class=" active  nav-link " data-id="profile" data-toggle="pill" role="tab" data-controls="profile" aria-selected="true" href="#profile">
                            <img src="{{asset('assets/admin/images/sidebar-icons/wallet.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Deposit History')}}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if(isset($tab) && $tab=='edit_profile') active @endif nav-link  " data-id="edit_profile" data-toggle="pill" role="tab" data-controls="edit_profile" aria-selected="true" href="#edit_profile">
                            <img src="{{asset('assets/admin/images/sidebar-icons/coin.svg')}}" class="img-fluid" alt="">
                            <span>{{__('Withdrawal History')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-10">
                <div class="tab-content tab-pt-n" id="tabContent">
                    <div class="tab-pane fade show active " id="profile" role="tabpanel" aria-labelledby="general-setting-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="deposit_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Payment Method')}}</th>
                                        <th>{{__('Coin Type')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Transaction Id')}}</th>
                                        <th>{{__('Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane active  fade @if(isset($tab) && $tab=='edit_profile') @endif" id="edit_profile" role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="withdrawal_table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Bank')}}</th>
                                        <th>{{__('Currency')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Created At')}}</th>
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

            $('#deposit_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('adminTransactionHistoryCurrency')}}',
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
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [
                    {"data": "user"},
                    {"data": "payment"},
                    {"data": "coin_type"},
                    {"data": "amount"},
                    {"data": "transaction_id"},
                    {"data": "created_at"},
                ]
            });

            $('#withdrawal_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('adminWithdrawalHistoryCurrency')}}',
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
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [
                    {"data": "user"},
                    {"data": "bank"},
                    {"data": "currency"},
                    {"data": "amount"},
                    {"data": "fees"},
                    {"data": "status"},
                    {"data": "created_at"},
                ]
            });
        })(jQuery)
    </script>
@endsection
