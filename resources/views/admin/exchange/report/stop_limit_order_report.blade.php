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
                        <table id="table" class="table table-borderless custom-table display text-center"
                               width="100%">
                            <thead>
                            <tr>
                                <th class="all">{{__('User')}}</th>
                                <th class="all">{{__('Base Coin')}}</th>
                                <th>{{__('Trade Coin')}}</th>
                                <th class="all">{{__('Price')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Order Type')}}</th>
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
                ajax: '{{ route('adminAllOrdersHistoryStopLimit') }}',
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
                    {"data": "email"},
                    {"data": "base_coin"},
                    {"data": "trade_coin"},
                    {"data": "price"},
                    {"data": "amount"},
                    {"data": "order_type"},
                    {"data": "created_at"}
                ],
            });
        })(jQuery);
    </script>
@endsection
