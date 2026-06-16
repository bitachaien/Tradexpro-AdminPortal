@extends('admin.future-trade.layouts.master',['menu'=>'user_wallet', 'sub_menu'=>'wallet_list'])
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
                                <th class="all">{{__('Wallet Name')}}</th>
                                <th class="all">{{__('Coin Type')}}</th>
                                <th>{{__('User Name')}}</th>
                                <th>{{__('User Email')}}</th>
                                <th>{{__('Balance')}}</th>
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
            ajax: '{{ route('futureTradeWalletList') }}',
            order: [2, 'desc'],
            autoWidth: false,
            columns: [
                {"data": "wallet_name"},
                {"data": "coin_type"},
                {"data": "user_name"},
                {"data": "user_email"},
                {"data": "balance"},
                {"data": "created_at"},
            ],
        });
    })(jQuery);
</script>
@endsection
