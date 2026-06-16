@extends('admin.master',['menu'=>'wallet'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Wallet Management')}}</li>
                    <li class="active-item">{{__('Send coin to user')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-6">
                <div class="table-area">
                    <div class="">
                        <table id="table" class="table table-borderless custom-table display text-lg-center" width="100%">
                            <thead>
                            <tr>
                                <th class="all">{{__('User Name')}}</th>
                                <th class="all">{{__('User Email')}}</th>
                                <th>{{ __('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-6">
                @if ($is_user_selected && isset($selected_user))
                    <div class="profile-info-form">
                        <div class="p-3 custom-box-shadow mb-4">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <h4 style="color: #cbcfd7">{{__('Selected User:')}}</h4>
                                </div>
                                <div class="col-md-12">
                                    <h4 class="mb-2">
                                        <span class="badge badge-info">{{__('User Name')}} :</span>
                                        <span class="text-white">{{$selected_user->first_name .' '.$selected_user->last_name}}</span>
                                    </h4>
                                    <h4>
                                        <span class="badge badge-info">{{__('User Email')}} :</span>
                                        <span class="text-white">{{$selected_user->email}}</span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 custom-box-shadow">
                            <form action="{{route('adminSendBalanceProcess')}}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mt-20">
                                        <div class="form-group row">
                                            <div class="col-md-6">
                                                <label>{{ __('Amount') }}</label>
                                                <input type="text" style="height: 42px" name="amount" class="form-control" value="{{old('amount')}}">
                                            </div>
                                            <div class="col-md-6">
                                                <label>{{ __('Select User Wallet') }}</label>
                                                <div class="customSelect rounded">
                                                    <select name="wallet_id[]" id="user_select"  class="selectpicker bg-dark w-100" title="{{ __('User Wallet') }}" data-live-search="true" data-actions-box="true" data-selected-text-format="count > 4" multiple>
                                                        @if(isset($wallets[0]))
                                                            @foreach($wallets as $user)
                                                                <option class="" value="{{ $user->id }}">{{ $user->name. ' ('. $user->email .')' }}</option>
                                                            @endforeach
                                                        @else
                                                            <option disabled>{{__('Select A user First from Left Side!')}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-6">
                                                <button type="submit" class="button-primary theme-btn">{{ __('Send') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="p-2 custom-box-shadow d-flex justify-content-center align-items-center" style="min-height: 200px">
                        <h3 class="text-white text-center">{{__('Please, Select User from Left Side to send Coin')}}</h3>
                    </div>
                @endif
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
                ajax: '{{route('adminActiveUserList')}}',
                order: [1, 'desc'],
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
                    {"data": "name"},
                    {"data": "email"},
                    {"data": "actions"}
                ],
                success: function (data) {
                    
                }
            });
        })(jQuery)
    </script>
@endsection
