@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'kyc_settings'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Settings')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3 col-xl-2">
                <ul class="nav user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='kycSettings') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="kycSettings" href="#kycSettings" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('KYC Settings')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='kycList') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="kycList" href="#kycList" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('KYC List')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='kycWithdrawal') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="kycWithdrawal" href="#kycWithdrawal" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('KYC Withdrawal')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='kycTrade') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="kycTrade" href="#kycTrade" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('KYC Trade')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='kycStaking') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="kycStaking" href="#kycStaking" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('KYC Staking')}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='kycPersonaSettings') active @endif nav-link " id="pills-email-tab"
                           data-toggle="pill" data-controls="kycPersonaSettings" href="#kycPersonaSettings" role="tab"
                           aria-controls="pills-email" aria-selected="true">
                            <span>{{__('KYC(Persona) Credentials Settings')}}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane @if(isset($tab) && $tab=='kycSettings') show active @endif" id="kycSettings"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.kyc-settings.partials.kyc-settings')
                    </div>
                    <div class="tab-pane active  @if(isset($tab) && $tab=='kycList') @endif" id="kycList"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.kyc-settings.partials.kyc-list')
                    </div>
                     <div class="tab-pane @if(isset($tab) && $tab=='kycWithdrawal') show active @endif" id="kycWithdrawal"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.kyc-settings.partials.kyc-withdrawal')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='kycTrade') show active @endif" id="kycTrade"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.kyc-settings.partials.kyc-trade')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='kycStaking') show active @endif" id="kycStaking"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.kyc-settings.partials.kyc-staking')
                    </div>
                    <div class="tab-pane @if(isset($tab) && $tab=='kycPersonaSettings') show active @endif" id="kycPersonaSettings"
                         role="tabpanel" aria-labelledby="pills-email-tab">
                        @include('admin.kyc-settings.partials.kyc-persona-settings')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection

@section('script')
    <script>
        (function ($) {
            "use strict";

            $('.nav-link').on('click', function () {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                var str = '#' + $(this).data('controls');
                $('.tab-pane').removeClass('show active');
                $(str).addClass('show active');
            });
        })(jQuery)

        function statusChange(kycId) {
            $.ajax({
                type: "POST",
                url: "{{ route('kycStatusChange') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'kyc_id': kycId
                },
                success: function (data) {
                    
                }
            });
        }

        $('#table').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering:  true,
            select: false,
            bDestroy: true,
            order: [0, 'asc'],
            responsive: false,
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
                "decimal":        "",
                "emptyTable":     "{{__('No data available in table')}}",
                "info":           "{{__('Showing')}} _START_ to _END_ of _TOTAL_ {{__('entries')}}",
                "infoEmpty":      "{{__('Showing')}} 0 to 0 of 0 {{__('entries')}}",
                "infoFiltered":   "({{__('filtered from')}} _MAX_ {{__('total entries')}})",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "{{__('Show')}} _MENU_ {{__('entries')}}",
                "loadingRecords": "{{__('Loading...')}}",
                "processing":     "",
                "search":         "{{__('Search')}}:",
                "zeroRecords":    "{{__('No matching records found')}}",
                "paginate": {
                    "next":       '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                    "previous":   '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
        });
    </script>
@endsection
