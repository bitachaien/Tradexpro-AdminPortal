@extends('admin.master', ['menu' => 'wallet'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{ __('Wallet Management') }}</li>
                    <li class="active-item">{{ __('User Wallet List') }}</li>
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
                        <form id="withdrawal_form" class="row" action="{{ route('adminWalletListExport') }}"
                            method="get">
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
                                <select name="export_to" class="selectpicker" data-style="form-control" data-width="100%"
                                    title="{{ __('Select a file type') }}">
                                    <option value=".csv">CSV</option>
                                    <option value=".xlsx">XLSX</option>
                                </select>
                            </div>
                            <div class="col-3 form-group">
                                <label for="#">&nbsp;</label>
                                <input class="form-control btn btn-primary" style="background-color:#1d2124" type="submit"
                                    value="{{ __('Export') }}" />
                            </div>
                        </form>
                    </div>
                    <table id="table" class="table table-borderless custom-table display text-lg-center" width="100%">
                        <thead>
                            <tr>
                                <th class="all">{{ __('Wallet Name') }}</th>
                                <th class="all">{{ __('Coin Type') }}</th>
                                <th>{{ __('User Name') }}</th>
                                <th>{{ __('User Email') }}</th>
                                <th>{{ __('Balance') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Action') }}</th>
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
            $('#table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                bLengthChange: true,
                responsive: false,
                paging: true,
                ajax: '{{ route('adminWalletList') }}',
                order: [5, 'desc'],
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
                        "searchable": true
                    },
                    {
                        "data": "coin_type",
                        "searchable": true
                    },
                    {
                        "data": "user_name",
                        "searchable": true
                    },
                    {
                        "data": "email",
                        "searchable": true
                    },
                    {
                        "data": "balance"
                    },
                    {
                        "data": "created_at"
                    },
                    {
                        "data": "actions",
                        "searchable": false
                    }
                ],
                success: function(data) {
                    
                }
            });
        })(jQuery)
    </script>
@endsection
