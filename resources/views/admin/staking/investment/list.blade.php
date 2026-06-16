@extends('admin.staking.layouts.master',['menu'=>'staking_investment', 'sub_menu'=>$sub_menu])
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
                    <li class="active-item">{{ $title }} </li>
                </ul>
            </div>
            <div class="col-md-3 text-right">
                @if (isset($type) && $type == STAKING_INVESTMENT_STATUS_UNPAID && isset($unpaid_status) && $unpaid_status == true)
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
                                <th class="all">{{__('Coin Type')}}</th>
                                <th>{{__('Period')}}</th>
                                <th>{{__('Offer Percentage')}}</th>
                                <th>{{__('Terms Type')}}</th>
                                <th>{{__('Minimum Maturity Period')}}</th>
                                <th>{{__('Total Investment')}}</th>
                                <th>{{__('Daily Earn Bonus')}}</th>
                                <th>{{__('Total Bonus')}}</th>
                                <th>{{__('Auto Renew Status')}}</th>
                                <th class="all">{{__('Status')}}</th>
                                <th>{{__('Auto Renew From')}}</th>
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
    <div id="givePayment" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('Give Payment')}}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="com-md-12">
                            <h3 class="text-white p-2">$type
                                {{__('Are you sure to give payment to the investor')}}?
                            </h3>
                        </div>
                    </div>
                    
                    <div class="modal-footer mt-4">
                        <a class="btn btn-warning text-white" 
                            href="{{route('stakingGivePayment')}}">{{__('Yes')}}</a>
                        <button type="button" class="btn btn-dark" data-dismiss="modal">{{__('No')}}</button>
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
                ajax: '{{ route('stakingInvestmentList',['type'=>$type]) }}',
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
                    {"data": "email"},
                    {"data": "coin_type"},
                    {"data": "period"},
                    {"data": "offer_percentage"},
                    {"data": "terms_type"},
                    {"data": "minimum_maturity_period"},
                    {"data": "investment_amount"},
                    {"data": "earn_daily_bonus"},
                    {"data": "total_bonus"},
                    {"data": "auto_renew_status"},
                    {"data": "status"},
                    {"data": "auto_renew_from"},
                    {"data": "created_at"}
                ],
            });
        })(jQuery);
    </script>
@endsection
