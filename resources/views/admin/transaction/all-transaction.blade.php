@extends('admin.master', ['menu' => 'transaction', 'sub_menu' => 'transaction_all'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{ __('Transaction') }}</li>
                    <li class="active-item">{{ __('All History') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-2">
                <ul class="nav user-management-nav profile-nav mb-3" id="pills-tab" role="tablist">
                    <li>
                        <a class=" active  nav-link " data-id="profile" data-toggle="pill" role="tab"
                            data-controls="profile" aria-selected="true" href="#profile">
                            <img src="{{ asset('assets/admin/images/sidebar-icons/wallet.svg') }}" class="img-fluid"
                                alt="">
                            <span>{{ __('Deposit History') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class=" @if (isset($tab) && $tab == 'edit_profile') active @endif nav-link  " data-id="edit_profile"
                            data-toggle="pill" role="tab" data-controls="edit_profile" aria-selected="true"
                            href="#edit_profile">
                            <img src="{{ asset('assets/admin/images/sidebar-icons/coin.svg') }}" class="img-fluid"
                                alt="">
                            <span>{{ __('Withdrawal History') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-10">
                <div class="tab-content tab-pt-n" id="tabContent">
                    <div class="tab-pane fade show active " id="profile" role="tabpanel"
                        aria-labelledby="general-setting-tab">
                        <div class="table-area">
                            <div class="">
                                <div class="">
                                    <form id="deposit_form" class="row"
                                        action="{{ route('adminTransactionHistoryExport') }}" method="get">
                                        @csrf
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('From Date') }}</label>
                                            <input type="hidden" name="type" value="deposit" />
                                            <input type="date" name="from_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('To Date') }}</label>
                                            <input type="date" name="to_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('Export') }}</label>
                                            <select name="export_to" class="selectpicker" data-width="100%"
                                                data-style="form-control" title="{{ __('Select a file type') }}">
                                                <option value=".csv">CSV</option>
                                                <option value=".xlsx">XLSX</option>
                                            </select>
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">&nbsp;</label>
                                            <input class="form-control btn btn-primary" style="background-color:#1d2124"
                                                type="submit" value="{{ __('Export') }}" />
                                        </div>
                                    </form>
                                </div>
                                <table id="deposit_table" class="table table-borderless custom-table display text-center"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th class="all">{{ __('Type') }}</th>
                                            <th class="all">{{ __('Sender') }}</th>
                                            <th class="all">{{ __('Coin Type') }}</th>
                                            <th>{{ __('Address') }}</th>
                                            <th>{{ __('Receiver') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Fees') }}</th>
                                            <th>{{ __('Transaction Id') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Created Date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane active  fade @if (isset($tab) && $tab == 'edit_profile')  @endif" id="edit_profile"
                        role="tabpanel" aria-labelledby="apisetting-tab">
                        <div class="table-area">
                            <div class="">
                                <div class="">
                                    <form id="withdrawal_form" class="row"
                                        action="{{ route('adminTransactionHistoryExport') }}" method="get">
                                        @csrf
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('From Date') }}</label>
                                            <input type="hidden" name="type" value="withdrawal" />
                                            <input type="date" name="from_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('To Date') }}</label>
                                            <input type="date" name="to_date" class="form-control" />
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">{{ __('Export') }}</label>
                                            <select name="export_to" class="selectpicker" data-style="form-control"
                                                data-width="100%" title="{{ __('Select a file type') }}">
                                                <option value=".csv">CSV</option>
                                                <option value=".xlsx">XLSX</option>
                                            </select>
                                        </div>
                                        <div class="col-3 form-group">
                                            <label for="#">&nbsp;</label>
                                            <input class="form-control btn btn-primary" style="background-color:#1d2124"
                                                type="submit" value="{{ __('Export') }}" />
                                        </div>
                                    </form>
                                </div>
                                <table id="withdrawal_table"
                                    class="table table-borderless custom-table display text-center" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="all">{{ __('Type') }}</th>
                                            <th class="all">{{ __('Sender') }}</th>
                                            <th class="all">{{ __('Coin type') }}</th>
                                            <th>{{ __('Address') }}</th>
                                            <th>{{ __('Receiver') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Fees') }}</th>
                                            <th>{{ __('Transaction Id') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Created Date') }}</th>
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
        </div>
    </div>
    <!-- /User Management -->
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            $('#deposit_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{ route('adminTransactionHistory') }}',
                order: [9, 'desc'],
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
                        "data": "address_type"
                    },
                    {
                        "data": "sender"
                    },
                    {
                        "data": "coin_type"
                    },
                    {
                        "data": "address"
                    },
                    {
                        "data": "receiver"
                    },
                    {
                        "data": "amount"
                    },
                    {
                        "data": "fees"
                    },
                    {
                        "data": "transaction_id"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "created_at"
                    }
                ]
            });

            $('#withdrawal_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: '{{ route('adminWithdrawalHistory') }}',
                order: [9, 'desc'],
                autoWidth: false,
                scrollX: true,
                // scrollCollapse: true,
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
                        "data": "address_type"
                    },
                    {
                        "data": "sender"
                    },
                    {
                        "data": "coin_type"
                    },
                    {
                        "data": "address"
                    },
                    {
                        "data": "receiver"
                    },
                    {
                        "data": "amount"
                    },
                    {
                        "data": "fees"
                    },
                    {
                        "data": "transaction_hash"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "created_at"
                    }
                ]
            });
        })(jQuery)
    </script>
@endsection
