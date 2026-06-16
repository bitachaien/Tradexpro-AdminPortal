@extends('admin.future-trade.layouts.master',['menu'=>'user_wallet', 'sub_menu'=>'transfer_history_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$title ?? __('Future Trade User Wallet List')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="table-area">
                    <div class="">
                        <table id="table" class="table table-borderless custom-table display text-lg-center"
                               width="100%">
                            <thead>
                            <tr>
                                <th class="all">{{__('User Name')}}</th>
                                <th class="all">{{__('User Email')}}</th>
                                <th class="all">{{__('Transfer From')}}</th>
                                <th class="all">{{__('Spot Wallet Name')}}</th>
                                <th class="all">{{__('Future Wallet Name')}}</th>
                                <th class="all">{{__('Amount')}}</th>
                                <th>{{__('Date')}}</th>
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
            scrollX: true,
            scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
            ajax: '{{ route('futureTradeTransferHistoryList') }}',
            order: [2, 'desc'],
            autoWidth: false,
            columns: [
                {"data": "user_name"},
                {"data": "user_email"},
                {"data": "transfer_from"},
                {"data": "spot_wallet_name"},
                {"data": "future_wallet_name"},
                {"data": "amount"},
                {"data": "created_at"},
            ],
        });
    })(jQuery);
</script>
@endsection
