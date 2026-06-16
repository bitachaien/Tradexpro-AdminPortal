@extends('admin.master', ['menu' => 'transaction', 'sub_menu' => $sub_menu])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ $title }}</li>
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
                        <div class="">
                            <form id="Referral_form" class="row" action="{{ route('adminTransactionHistoryExport') }}"
                                method="get">
                                @csrf
                                <div class="col-3 form-group">
                                    <label for="#">{{ __('From Date') }}</label>
                                    <input type="hidden" name="type" value="referral" />
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
                        <table id="table" class="table table-borderless custom-table display text-center"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Transaction Id') }}</th>
                                    <th class="all">{{ __('User') }}</th>
                                    <th>{{ __('Referral By') }}</th>
                                    <th>{{ __('Coin') }}</th>
                                    <th class="all">{{ __('Amount') }}</th>
                                    <th>{{ __('Created At') }}</th>
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
                ajax: '{{ route('adminWithdrawalReferralHistory') }}',
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
                        "data": "transaction_id"
                    },
                    {
                        "data": "reference_user_email"
                    },
                    {
                        "data": "referral_user_email"
                    },
                    {
                        "data": "coin_type"
                    },
                    {
                        "data": "amount"
                    },
                    {
                        "data": "created_at"
                    },
                ],
            });
        })(jQuery);
    </script>
@endsection
