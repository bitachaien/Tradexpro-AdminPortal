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
                            <form id="withdrawal_form" class="row" action="{{ route('adminAllOrdersHistorySellExport') }}" method="get">
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
                        <table id="table" class="table table-borderless custom-table display text-lg-center"
                               width="100%">
                            <thead>
                            <tr>
                                <th class="all">{{__('User')}}</th>
                                <th class="all">{{__('Base Coin')}}</th>
                                <th>{{__('Trade Coin')}}</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Processed')}}</th>
                                <th>{{__('Remaining')}}</th>
                                <th class="all">{{__('Status')}}</th>
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
        (function($) {
            "use strict";
            $('#table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{ route('adminAllOrdersHistorySell') }}',
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
                    {"data": "email", 'name': 'users.email'},
                    {"data": "base_coin", 'name': 'base_coin_table.coin_type'},
                    {"data": "trade_coin", 'name': 'trade_coin_table.coin_type'},
                    {"data": "price"},
                    {"data": "amount"},
                    {"data": "processed", "searchable": false},
                    {"data": "remaining", "searchable": false},
                    {"data": "status", "searchable": false},
                    {"data": "created_at"}
                ],
            });
        })(jQuery);
    </script>
@endsection
