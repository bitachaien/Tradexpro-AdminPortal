@extends('admin.master',['menu'=>'fiat_withdraw', 'sub_menu'=>'fiat_withdraw_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Fiat Withdrawal')}} </li>
                    <li class="active-item">{{__('Withdrawal History')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management wallet-transaction-area">
        <div class="row no-gutters">
            <div class="col-12 col-lg-2">
                <ul class="nav wallet-transaction user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="pills-deposit-tab" data-toggle="pill" href="#pills-deposit"
                            role="tab" aria-controls="pills-deposit" aria-selected="true">
                            {{__('Pending Withdrawal List')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pills-withdraw-tab" data-toggle="pill" href="#pills-withdraw"
                            role="tab" aria-controls="pills-withdraw" aria-selected="true">
                            {{__('Rejected Withdrawal List')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pills-success-withdraw-tab" data-toggle="pill"
                            href="#pills-success-withdraw" role="tab" aria-controls="pills-success-withdraw"
                            aria-selected="true">
                            {{__('Active Withdrawal List')}}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-deposit" role="tabpanel"
                            aria-labelledby="pills-deposit-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="table-pending" class="table table-borderless custom-table display text-left"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User Name')}}</th>
                                        <th>{{__('Bank')}}</th>
                                        <th>{{__('Currency Amount')}}</th>
                                        <th>{{__('Coin Amount')}}</th>
                                        <th>{{__('Rate')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Date')}} </th>
                                        <th class="all">{{__('Actions')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-withdraw" role="tabpanel"
                            aria-labelledby="pills-withdraw-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="reject-withdrawal"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User Name')}}</th>
                                        <th>{{__('Bank')}}</th>
                                        <th>{{__('Currency Amount')}}</th>
                                        <th>{{__('Coin Amount')}}</th>
                                        <th>{{__('Rate')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Date')}} </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-success-withdraw" role="tabpanel"
                            aria-labelledby="pills-success-withdraw-tab">
                        <div class="table-area">
                            <div class="">
                                <table id="success-withdrawal"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User Name')}}</th>
                                        <th>{{__('Bank')}}</th>
                                        <th>{{__('Currency Amount')}}</th>
                                        <th>{{__('Coin Amount')}}</th>
                                        <th>{{__('Rate')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th>{{__('Image')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Date')}} </th>
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

            $('#table-pending').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('fiatWithdrawPendingList')}}',
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
                    {"data": "user"},
                    {"data": "bank"},
                    {"data": "currency_amount"},
                    {"data": "coin_amount"},
                    {"data": "rate"},
                    {"data": "fees"},
                    {"data": "status"},
                    {"data": "created_at"},
                    {"data": "actions"}
                ]
            });


            $('#reject-withdrawal').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('fiatWithdrawRejectList')}}',
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
                    {"data": "user"},
                    {"data": "bank"},
                    {"data": "currency_amount"},
                    {"data": "coin_amount"},
                    {"data": "rate"},
                    {"data": "fees"},
                    {"data": "status"},
                    {"data": "created_at"}
                ]
            });


            $('#success-withdrawal').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('fiatWithdrawActiveList')}}',
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
                    {"data": "user"},
                    {"data": "bank"},
                    {"data": "currency_amount"},
                    {"data": "coin_amount"},
                    {"data": "rate"},
                    {"data": "fees"},
                    {"data": "image"},
                    {"data": "status"},
                    {"data": "created_at"}
                ]
            });
        })(jQuery);
    </script>
@endsection
