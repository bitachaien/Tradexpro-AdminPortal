@extends('admin.master',['menu'=>'trade', 'sub_menu'=>$sub_menu])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
<!-- breadcrumb -->
<div class="custom-breadcrumb">
    <div class="row">
        <div class="col-12">
            <ul>
                <li>{{__('Order')}}</li>
                <li class="active-item">{{ $title }}</li>
            </ul>
        </div>
    </div>
</div>
<!-- /breadcrumb -->

<!-- User Management -->
<div class="user-management pt-4">
    <div class="row">
        <div class="col-12">
            <div class="table-area">
                <div class="">
                    <div class="">
                        <form id="withdrawal_form" class="row" action="{{ route('adminAllTransactionHistoryExport') }}" method="get">
                            @csrf
                            <div class="col-3 form-group">
                                <label for="#">{{__('From Date')}}</label>
                                <input type="hidden" name="type" value="withdrawal" />
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

                    <br>
                    <div class="form-group w-25">
                        <label for="#">{{ __('Filter By') }}</label>
                        <select name="export_to" class="selectpicker" data-style="form-control" data-width="100%" onchange="TransactionReportTableChange(this.value)">
                            @foreach (transaction_filter_by() as $key => $value)
                                <option value="{{ $key }}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <table id="table" class="table table-borderless custom-table display text-center"
                           width="100%">
                        <thead>
                        <tr>
                            <th>{{__('Transaction Id')}}</th>
                            <th class="all">{{__('Buy User')}}</th>
                            <th>{{__('Sell User')}}</th>
                            <th>{{__('Base Coin')}}</th>
                            <th class="all">{{__('Trade Coin')}}</th>
                            <th>{{__('Price')}}</th>
                            <th>{{__('Amount')}}</th>
                            <th class="all">{{__('Total')}}</th>
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
<!-- /User Management -->
@endsection
@section('script')
    <script>
        var TransactionReportTable = null;
        (function($) {
            "use strict";
            TransactionReportTable = $('#table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                retrieve: true,
                bLengthChange: true,
                responsive: false,
                ajax: '{{route('adminAllTransactionHistory')}}',
                order: [8, 'desc'],
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
                    {"data": "transaction_id", "searchable" : true},
                    {"data": "buy_user_email", "searchable" : true},
                    {"data": "sell_user_email", "searchable" : true},
                    {"data": "base_coin", "searchable" : true},
                    {"data": "trade_coin", "searchable" : true},
                    {"data": "price", "searchable" : false},
                    {"data": "amount", "searchable" : false},
                    {"data": "total", "searchable" : false},
                    {"data": "created_at", "searchable" : false},
                    {"data": "type", "visible": false, "searchable" : false},
                ],
            });
        })(jQuery);

        function TransactionReportTableChange(type){
            TransactionReportTable.columns(9).search(type).draw();
        }
    </script>
@endsection
