@extends('admin.future-trade.layouts.master',['menu'=>'future_trade_history', 'sub_menu'=> 'order'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Future Trade Order History')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management wallet-transaction-area">
        <div class="tab-pane fade show active" id="all_order_tab" role="tabpanel"
                aria-labelledby="all_order">
            <div class="table-area">
                <div class="">
                    <table id="all_table" class="table table-borderless custom-table display text-left"
                            width="100%">
                        <thead>
                        <tr>
                            <th class="all">{{__('User')}}</th>
                            <th class="all">{{__('Time')}}</th>
                            <th class="all">{{__('Symbol')}}</th>
                            <th class="all">{{__('Type')}}</th>
                            <th class="all">{{__('Side')}}</th>
                            <th class="all">{{__('Price')}}</th>
                            <th class="all">{{__('Amount')}}</th>
                            <th class="all">{{__('Triger Condition')}}</th>
                            
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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

                $("#all_table").DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    responsive: false,
                    //ajax: url,
                    // order: [7, 'desc'],
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
                        {'data': 'user_id'},
                        {'data': 'created_at'},
                        {'data': 'symbol'},
                        {'data': 'type'},
                        {'data': 'side'},
                        {'data': 'price'},
                        {'data': 'amount'},
                        {'data': 'triger_condition'},
                    ]
                });
        })(jQuery);
    </script>
@endsection
