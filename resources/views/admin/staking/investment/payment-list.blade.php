@extends('admin.staking.layouts.master',['menu'=>'staking_payment_history', 'sub_menu'=>'staking_payment_history'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Order')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
            <div class="col-md-3 text-right">
                @if (isset($unpaid_status) && $unpaid_status == true)
                    <a class="add-btn theme-btn" 
                        href="" data-toggle="modal" 
                        data-target="#givePayment">
                        {{__('Give Payment')}}
                    </a>
                @endif
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
                        <table id="table" class="table table-borderless custom-table display text-lg-center"
                               width="100%">
                            <thead>
                            <tr>
                                <th class="all">{{__('User')}}</th>
                                <th class="all">{{__('Investment ID')}}</th>
                                <th class="all">{{__('Coin Type')}}</th>
                                <th class="all">{{__('Total Investment')}}</th>
                                <th class="all">{{__('Total Bonus')}}</th>
                                <th class="all">{{__('Total Amount')}}</th>
                                <th class="all">{{__('Investment Status')}}</th>
                                <th class="all">{{__('Payment By Auto renew')}}</th>
                                <th class="all">{{__('Created At')}}</th>
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
                ajax: '{{ route('stakingInvestmentPaymentList') }}',
                order: [8, 'desc'],
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                columns: [
                    {"data": "email"},
                    {"data": "investment_id"},
                    {"data": "coin_type"},
                    {"data": "total_investment"},
                    {"data": "total_bonus"},
                    {"data": "total_amount"},
                    {"data": "investment_status"},
                    {"data": "is_auto_renew"},
                    {"data": "created_at"}
                ],
            });
        })(jQuery);
    </script>
@endsection
