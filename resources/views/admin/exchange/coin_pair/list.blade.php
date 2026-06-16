@extends('admin.master', ['menu' => 'coin', 'sub_menu' => 'coin_pair'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{ __('Coin Pair Settings') }}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
            <div class="col-md-3 text-right">
                <a class="add-btn theme-btn" href="" data-toggle="modal" data-target="#pairModal"><i
                        class="fa fa-plus"></i>{{ __('Add New Pair') }}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management p-4 custom-box-shadow">
        <div class="row">
            <div class="col-12">
                <div class="table-area">
                    <div>
                        <table id="table" class=" table table-borderless custom-table display text-lg-center"
                            width="100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Trade Coin') }}</th>
                                    <th scope="col" class="all">{{ __('Base Coin') }}</th>
                                    <th scope="col">{{ __('Last Price') }}</th>
                                    <th scope="col" class="all">{{ __('Active Status') }}</th>
                                    <th scope="col" class="all">{{ __('Is Default') }}</th>
                                    <th scope="col" class="all">{{ __('Bot Trading') }}</th>
                                    @if ($global_enable_future_trade)
                                        <th scope="col" class="all">{{ __('Future Trading') }}</th>
                                    @endif
                                    <th scope="col" class="">{{ __('Price Available From Api') }}</th>
                                    <th scope="col">{{ __('Created At') }}</th>
                                    <th scope="col" class="all">{{ __('Actions') }}</th>
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
    <!-- Modal -->
    <div id="pairModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ __('Add Coin Pair') }}</h4>
                </div>
                <div class="modal-body">
                    {{ Form::open(['route' => 'saveCoinPairSettings', 'files' => 'true']) }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group text-left">
                                <label class="form-label">{{ __('Base Coin') }}</label>
                                <div class="cp-select-area">
                                    <select class=" form-control" name="parent_coin_id" style="width: 100%;">
                                        <option value="">{{ __('Select') }}</option>
                                        @if (isset($coins[0]))
                                            @foreach ($coins as $coin)
                                                <option value="{{ $coin->id }}">
                                                    {{ check_default_coin_type($coin->coin_type) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group text-left">
                                <label class="form-label">{{ __('Pair Coin') }}</label>
                                <div class="cp-select-area">
                                    <select class=" form-control" name="child_coin_id" style="width: 100%;">
                                        <option value="">{{ __('Select') }}</option>
                                        @if (isset($coins[0]))
                                            @foreach ($coins as $coin)
                                                <option value="{{ $coin->id }}">
                                                    {{ check_default_coin_type($coin->coin_type) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group text-left">
                                <label class="form-label">{{ __('Get last price from api ?') }}</label>
                                <div class="cp-select-area">
                                    <select name="get_price_api" id="" class="form-control">
                                        <option value="1">{{ __('Yes') }}</option>
                                        <option value="2">{{ __('No') }}</option>
                                    </select>
                                </div>
                                <small class="text-warning">{{ __('If no , please input the initial price') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group text-left">
                                <label class="form-label">{{ __('Initial Price') }}</label>
                                <input type="text" class="form-control" name="price">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <button class="btn btn-warning text-white" type="submit">{{ __('Save') }}</button>
                        <button type="button" class="btn btn-dark" data-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                    {{ Form::close() }}
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('#table').DataTable({
            processing: true,
                serverSide: true,
                pageLength: 10,
                retrieve: true,
                bLengthChange: true,
                responsive: false,
                ajax: '{{route('coinPairs')}}',
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
                    {"data": "child_coin_name", "orderable": true},
                    {"data": "parent_coin_name", "orderable": true},
                    {"data": "price", "orderable": true},
                    {"data": "status", "orderable": false,'searchable': false},
                    {"data": "is_default", "orderable": true},
                    {"data": "bot_trading", "orderable": true},
                    @if ($global_enable_future_trade)
                        {"data": "enable_future_trade", "orderable": false},
                    @endif
                    {"data": "is_token", "orderable": false},
                    {"data": "created_at", "orderable": true},
                    {"data": "actions", "orderable": false},
                ],
        });

        function processForm(active_id) {

            $.ajax({
                type: "POST",
                url: "{{ route('changeCoinPairStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function(data) {
                    if (data.success == true) {
                        VanillaToasts.create({
                            text: data.message,
                            backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                            type: 'success',
                            timeout: 5000
                        });
                    } else {
                        VanillaToasts.create({
                            text: data.message,
                            type: 'warning',
                            timeout: 5000
                        });
                    }
                }
            });
        }

        function defaultStatus(active_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('changeCoinPairDefaultStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function(data) {
                    if (data.success == true) {
                        VanillaToasts.create({
                            text: data.message,
                            backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                            type: 'success',
                            timeout: 5000
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        VanillaToasts.create({
                            text: data.message,
                            type: 'warning',
                            timeout: 5000
                        });
                    }
                }
            });
        }

        function processMarketBot(active_id) {

            $.ajax({
                type: "POST",
                url: "{{ route('changeCoinPairBotStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function(data) {
                    if (data.success == true) {
                        VanillaToasts.create({
                            text: data.message,
                            backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                            type: 'success',
                            timeout: 5000
                        });
                    } else {
                        VanillaToasts.create({
                            text: data.message,
                            type: 'warning',
                            timeout: 5000
                        });

                    }
                }
            });
        }

        function futureTradeStatus(active_id) {

            $.ajax({
                type: "POST",
                url: "{{ route('changeFutureTradeStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function(data) {
                    if (data.success == true) {
                        VanillaToasts.create({
                            text: data.message,
                            backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                            type: 'success',
                            timeout: 5000
                        });
                    } else {
                        VanillaToasts.create({
                            text: data.message,
                            type: 'warning',
                            timeout: 5000
                        });

                    }
                }
            });
        }
    </script>
@endsection
