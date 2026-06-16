@extends('admin.master',['menu'=>'transaction', 'sub_menu'=>'transaction_deposit'])
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
                    <li class="active-item">{{__('Pending Deposit')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management wallet-transaction-area">
        <div class="row no-gutters">
            <div class="col-12">
                <div class="table-area">
                    <div class="">
                            <div class="">
                                <form id="deposit_form" class="row" action="{{ route('adminTransactionHistoryExport') }}" method="get">
                                    @csrf
                                    <div class="col-3 form-group">
                                        <label for="#">{{__('From Date')}}</label>
                                        <input type="hidden" name="type" value="pending_deposit" />
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
                                <th>{{__('User')}}</th>
                                <th class="all">{{__('Address')}}</th>
                                <th>{{__('From Address')}}</th>
                                <th>{{__('Coin Type')}}</th>
                                <th>{{__('Coin API')}}</th>
                                <th class="all">{{__('Amount')}}</th>
                                <th >{{__('Transaction Id')}}</th>
                                <th>{{__('Date')}}</th>
                                <th class="all">{{__('Actions')}}</th>
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
    <!-- /User Management -->
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                ajax: '{{route('adminPendingDeposit')}}',
                order: [6, 'desc'],
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
                    {"data": "receiver_wallet_id"},
                    {"data": "address"},
                    {"data": "from_address"},
                    {"data": "coin_type"},
                    {"data": "network_type"},
                    {"data": "amount"},
                    {"data": "transaction_id"},
                    {"data": "created_at"},
                    {"data": "actions"}
                ]
            });
        })(jQuery);
    </script>
@endsection
