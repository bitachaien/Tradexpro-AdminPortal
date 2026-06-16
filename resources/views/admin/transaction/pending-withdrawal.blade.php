@extends('admin.master',['menu'=>'transaction', 'sub_menu'=>'transaction_withdrawal'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Transaction History')}} </li>
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
                                <div class="">
                                    <form id="pending_withdrawal_form" class="row" action="{{ route('adminTransactionHistoryExport') }}" method="get">
                                        @csrf
                                        <div class="col-3 form-group">
                                            <label for="#">{{__('From Date')}}</label>
                                            <input type="hidden" name="type" value="pending_withdrawal_form" />
                                            <input type="date" name="from_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{__('To Date')}}</label>
                                            <input type="date" name="to_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('Export') }}</label>
                                            <select name="export_to" class="selectpicker" data-style="form-control" data-width="100%" title="{{ __('Select a file type') }}">
                                                <option value=".csv">CSV</option>
                                                <option value=".xlsx">XLSX</option>
                                            </select>
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">&nbsp;</label>
                                            <input class="form-control btn btn-primary" style="background-color:#1d2124" type="submit" value="{{ __("Export") }}" />
                                        </div>
                                    </form>
                                </div>
                                <table id="table" class="table table-borderless custom-table display text-left"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Type')}}</th>
                                        <th>{{__('Sender')}}</th>
                                        <th>{{__('Coin Type')}}</th>
                                        <th>{{__('Address')}}</th>
                                        <th>{{__('Receiver')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Memo')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th class="all">{{__('Transaction Id')}}</th>
                                        <th>{{__('Coin API')}}</th>
                                        <th>{{__('Update Date')}}</th>
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
                                <div class="">
                                    <form id="reject_withdrawal_form" class="row" action="{{ route('adminTransactionHistoryExport') }}" method="get">
                                        @csrf
                                        <div class="col-3 form-group">
                                            <label for="#">{{__('From Date')}}</label>
                                            <input type="hidden" name="type" value="reject_withdrawal_form" />
                                            <input type="date" name="from_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{__('To Date')}}</label>
                                            <input type="date" name="to_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('Export') }}</label>
                                            <select name="export_to" class="selectpicker" data-style="form-control" data-width="100%" title="{{ __('Select a file type') }}">
                                                <option value=".csv">CSV</option>
                                                <option value=".xlsx">XLSX</option>
                                            </select>
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">&nbsp;</label>
                                            <input class="form-control btn btn-primary" style="background-color:#1d2124" type="submit" value="{{ __("Export") }}" />
                                        </div>
                                    </form>
                                </div>
                                <table id="reject-withdrawal"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Type')}}</th>
                                        <th class="all">{{__('Sender')}}</th>
                                        <th>{{__('Coin Type')}}</th>
                                        <th>{{__('Address')}}</th>
                                        <th>{{__('Receiver')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Memo')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th class="all">{{__('Transaction Id')}}</th>
                                        <th>{{__('Coin API')}}</th>
                                        <th>{{__('Update Date')}}</th>
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
                                <div class="">
                                    <form id="active_withdrawal_form" class="row" action="{{ route('adminTransactionHistoryExport') }}" method="get">
                                        @csrf
                                        <div class="col-3 form-group">
                                            <label for="#">{{__('From Date')}}</label>
                                            <input type="hidden" name="type" value="active_withdrawal_form" />
                                            <input type="date" name="from_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{__('To Date')}}</label>
                                            <input type="date" name="to_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('Export') }}</label>
                                            <select name="export_to" class="selectpicker" data-style="form-control" data-width="100%" title="{{ __('Select a file type') }}">
                                                <option value=".csv">CSV</option>
                                                <option value=".xlsx">XLSX</option>
                                            </select>
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">&nbsp;</label>
                                            <input class="form-control btn btn-primary" style="background-color:#1d2124" type="submit" value="{{ __("Export") }}" />
                                        </div>
                                    </form>
                                </div>
                                <table id="success-withdrawal"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Type')}}</th>
                                        <th class="all">{{__('Sender')}}</th>
                                        <th>{{__('Coin Type')}}</th>
                                        <th>{{__('Address')}}</th>
                                        <th>{{__('Receiver')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Memo')}}</th>
                                        <th>{{__('Fees')}}</th>
                                        <th class="all">{{__('Transaction Id')}}</th>
                                        <th>{{__('Coin API')}}</th>
                                        <th>{{__('Update Date')}}</th>
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

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('adminPendingWithdrawal')}}',
                order: [10, 'desc'],
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
                    {"data": "address_type"},
                    {"data": "sender"},
                    {"data": "coin_type"},
                    {"data": "address"},
                    {"data": "receiver"},
                    {"data": "amount"},
                    {"data": "memo"},
                    {"data": "fees"},
                    {"data": "transaction_hash"},
                    {"data": "network_type"},
                    {"data": "updated_at"},
                    {"data": "actions"}
                ]
            });


            $('#reject-withdrawal').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('adminRejectedWithdrawal')}}',
                order: [10, 'desc'],
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
                    {"data": "address_type"},
                    {"data": "sender"},
                    {"data": "coin_type"},
                    {"data": "address"},
                    {"data": "receiver"},
                    {"data": "amount"},
                    {"data": "memo"},
                    {"data": "fees"},
                    {"data": "transaction_hash"},
                    {"data": "network_type"},
                    {"data": "updated_at"},
                ]
            });


            $('#success-withdrawal').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{route('adminActiveWithdrawal')}}',
                order: [10, 'desc'],
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
                    {"data": "address_type"},
                    {"data": "sender"},
                    {"data": "coin_type"},
                    {"data": "address"},
                    {"data": "receiver"},
                    {"data": "amount"},
                    {"data": "memo"},
                    {"data": "fees"},
                    {"data": "transaction_hash"},
                    {"data": "network_type"},
                    {"data": "updated_at"},
                ]
            });
        })(jQuery);
    </script>
@endsection
