@extends('admin.master', ['menu' => 'setting', 'sub_menu' => 'trade_fees_settings'])
@section('title', isset($title) ? $title : __('Trade Fees Settings'))
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li>{{__('Trade')}}</li>
                    <li class="active-item">{{ __('Trade Fees Settings') }}</li>
                </ul>
            </div>

            <div class="col-3">
                <div class="text-right">
                    <a class="add-btn theme-btn" href="" data-toggle="modal" data-target="#tradeFeeModal">
                        <i class="fa fa-plus"></i>{{ __('Add New') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class="table table-borderless custom-table display" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('User') }}</th>
                                    <th scope="col">{{ __('Child Coin') }}</th>
                                    <th scope="col">{{ __('Parent Coin') }}</th>
                                    <th scope="col">{{ __('Maker Fee') }}</th>
                                    <th scope="col">{{ __('Taker Fee') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Created At') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="tradeFeeModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Add New Fees') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tradeFees.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group text-left">
                                    <label class="form-label">{{ __('User Name') }}</label>
                                    <div class="cp-select-area">
                                        <select class="form-control" name="user_id"
                                            title="{{ __('Select user') }}" data-live-search="true" data-actions-box="true">
                                            <option value="">{{ __('Select User') }}</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">
                                                    {{ $user->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Coin Pair') }}</label>
                                <div class="customSelect rounded">
                                    <select name="coin_pair_ids[]" class="selectpicker bg-dark w-100"
                                        title="{{ __('Select pairs') }}" data-live-search="true" data-actions-box="true"
                                        multiple="multiple">
                                        @foreach ($coinPairs as $pKey => $pair)
                                            <option value="{{ $pair->id }}">
                                                {{ $pair->child_coin->coin_type . '/' . $pair->parent_coin->coin_type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group text-left">
                                    <label class="form-label">{{ __('Maker Fee (%)') }}</label>
                                    <input type="number" step="0.001" class="form-control" name="maker_fee" min="0"
                                        max="100" placeholder="0.1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group text-left">
                                    <label class="form-label">{{ __('Taker Fee (%)') }}</label>
                                    <input type="number" step="0.001" class="form-control" name="taker_fee" min="0"
                                        max="100" placeholder="0.1">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer mt-4">
                            <button class="btn btn-warning text-white" type="submit">{{ __('Save') }}</button>
                            <button type="button" class="btn btn-dark" data-dismiss="modal">{{ __('Close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        (function ($) {
            "use strict";


            $('#table').DataTable({
                serverSide: true,
                pageLength: 10,
                retrieve: true,
                bLengthChange: true,
                responsive: false,
                ajax: '{{ route('tradeFees.index') }}',
                columns: [{
                    data: "user_email",
                    name: "user_email",
                },
                {
                    data: "child_coin",
                    name: "child_coin",
                },
                {
                    data: "parent_coin",
                    name: "parent_coin",
                },
                {
                    data: "maker_fee",
                    name: "maker_fee",
                },
                {
                    data: "taker_fee",
                    name: "taker_fee",
                },
                {
                    data: "status",
                    name: "status",
                    searchable: false,
                },
                {
                    data: "created_at",
                    name: "created_at",
                },

                {
                    data: "actions",
                    name: "actions",
                    searchable: false,
                }],
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
            });

        })(jQuery)

        function changeStatus(item) {
            const id = item;

            $.ajax({
                type: "PATCH",
                url: "{{ route('tradeFee.changeStatus', ':id') }}".replace(':id', id),
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (data) {
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
                },
                error: function (data) {
                    console.log('inside error');
                }
            });
        }

    </script>
@endsection