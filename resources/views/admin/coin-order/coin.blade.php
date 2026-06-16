@extends('admin.master', ['menu' => 'coin', 'sub_menu' => 'coin_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{ __('Coin') }}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    @php
        $demoTrade = isset($module) && isset($module['DemoTrade']) ? true : false;
    @endphp
    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <!-- <h3>{{ $title }}</h3> -->
                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn-new mb-2 mr-1">
                            <a href="{{ route('adminCoinRate') }}">{{ __('Update Coin Rate') }}</a>
                        </div>
                        <div class="add-btn-new mb-2 ml-2">
                            <a href="{{ route('adminAddCoin') }}">{{ __('Add New Coin') }}</a>
                        </div>
                    </div>
                </div>
                <div class="table-area">
                    <div class="">
                        <table id="table" class=" table table-borderless custom-table display text-lg-center"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Coin Name') }}</th>
                                    <th>{{ __('Currency Type') }}</th>
                                    <th class="all">{{ __('Coin Type') }}</th>
                                    <th>{{ __('Coin API') }}</th>
                                    <th>{{ __('Coin Price') }}</th>
                                    <th class="all">{{ __('Status') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    @if ($demoTrade)
                                        <th class="all">{{ __('Demo Trade') }}</th>
                                    @endif
                                    <th class="all text-left">{{ __('Actions') }}</th>
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
                pageLength: 10,
                retrieve: true,
                bLengthChange: true,
                responsive: false,
                ajax: '{{ route('adminCoinList') }}',
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
                columns: [{
                        "data": "name",
                        "orderable": true,
                        'searchable': true
                    },
                    {
                        "data": "currency_type",
                        "orderable": true
                    },
                    {
                        "data": "coin_type",
                        "orderable": true
                    },
                    {
                        "data": "network",
                        "orderable": false,
                        'searchable': false
                    },
                    {
                        "data": "coin_price",
                        "orderable": true
                    },
                    {
                        "data": "status",
                        "orderable": false
                    },
                    {
                        "data": "created_at",
                        "orderable": true
                    },
                    @if ($demoTrade)
                        {
                            "data": "is_demo_trade",
                            "orderable": false
                        },
                    @endif {
                        "data": "actions",
                        "orderable": false
                    },
                ],
            });
        })(jQuery);

        function processForm(active_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('adminCoinStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function(data) {

                }
            });
        }
        @if ($demoTrade)
            function changeDemoTradeStatus($coin) {
                let url = '{{ route('demoTradeCoinStatus') }}' + $coin;
                $.get(url, (data) => {
                    if (data?.success) {
                        VanillaToasts.create({
                            text: data?.message,
                            type: 'success',
                            timeout: 40000
                        });
                        return;
                    }
                    VanillaToasts.create({
                        text: data?.message,
                        type: 'warning',
                        timeout: 40000
                    });
                });
            }
        @endif
    </script>
@endsection
