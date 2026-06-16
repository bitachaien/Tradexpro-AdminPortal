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
                    <li class="active-item">{{ $title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <form action="{{ route('deductWalletBalanceSave')}}" method="post">
                        @csrf
                        <input type="hidden" name="wallet_id" class="form-control-new" value="{{encrypt($wallet_details->id)}}">
                        <div class="row">
                            <div class="col-md-12 mt-20">
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label>{{ __('Balance') }}</label>
                                        <input type="text" name="balance" class="form-control-new" value="{{$wallet_details->balance}}" readonly>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label>{{ __('Deduct Amount') }}</label>
                                        <input type="text" name="deduct_amount" class="form-control-new" required>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <label>{{ __('Reason') }}</label>
                                        <textarea class="form-control" name="reason" id="" cols="10" rows="2" required></textarea>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-6">
                                        <button type="submit" class="button-primary theme-btn">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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

        })(jQuery)
    </script>
@endsection
