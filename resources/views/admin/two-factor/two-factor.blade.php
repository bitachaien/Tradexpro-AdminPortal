@extends('admin.master', ['menu' => 'setting', 'sub_menu' => 'two_factor'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{ __('Settings') }}</li>
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
                    @if (isset($tab) && $tab == 'api_config')
                    @else
                        <li class="nav-item">
                            <a class="@if (isset($tab) && $tab == 'two_factor_list') active @endif nav-link " id="pills-user-tab"
                                data-toggle="pill" data-controls="two_factor_list" href="#two_factor_list" role="tab"
                                aria-controls="pills-user" aria-selected="true">
                                <span>{{ __('Two Factor List') }}</span>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="@if (isset($tab) && $tab == 'two_factor_login') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="two_factor_login" href="#two_factor_login" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{__('Two Factor Login')}}</span>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="@if (isset($tab) && $tab == 'two_factor_withdraw') active @endif nav-link " id="pills-user-tab"
                                data-toggle="pill" data-controls="two_factor_withdraw" href="#two_factor_withdraw"
                                role="tab" aria-controls="pills-user" aria-selected="true">
                                <span>{{ __('Two Factor Withdraw') }}</span>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="@if (isset($tab) && $tab == 'two_factor_swap') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="two_factor_swap" href="#two_factor_swap" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{__('Two Factor On Swap')}}</span>
                            </a>
                        </li> --}}
                    @endif
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if (isset($tab) && $tab == 'two_factor_list') active @endif" id="two_factor_list"
                        role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.two-factor.include.list')
                    </div>
                    <div class="tab-pane show @if (isset($tab) && $tab == 'two_factor_login') active @endif" id="two_factor_login"
                        role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.two-factor.include.login')
                    </div>
                    <div class="tab-pane show @if (isset($tab) && $tab == 'two_factor_withdraw') active @endif" id="two_factor_withdraw"
                        role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.two-factor.include.withdraw')
                    </div>
                    <div class="tab-pane show @if (isset($tab) && $tab == 'two_factor_swap') active @endif" id="two_factor_swap"
                        role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('admin.two-factor.include.swap')
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

            $('.nav-link').on('click', function() {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                var str = '#' + $(this).data('controls');
                $('.tab-pane').removeClass('show active');
                $(str).addClass('show active');
            });
        })(jQuery)

        function statusChange(twoFactorId) {
            $.ajax({
                type: "POST",
                url: "{{ route('SaveTwoFactor') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': twoFactorId
                },
                success: function(data) {
                    
                }
            });
        }

        $('#table').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering: true,
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
                "decimal": "",
                "emptyTable": "{{ __('No data available in table') }}",
                "info": "{{ __('Showing') }} _START_ to _END_ of _TOTAL_ {{ __('entries') }}",
                "infoEmpty": "{{ __('Showing') }} 0 to 0 of 0 {{ __('entries') }}",
                "infoFiltered": "({{ __('filtered from') }} _MAX_ {{ __('total entries') }})",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "{{ __('Show') }} _MENU_ {{ __('entries') }}",
                "loadingRecords": "{{ __('Loading...') }}",
                "processing": "",
                "search": "{{ __('Search') }}:",
                "zeroRecords": "{{ __('No matching records found') }}",
                "paginate": {
                    "next": '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                    "previous": '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
        });
    </script>
@endsection
