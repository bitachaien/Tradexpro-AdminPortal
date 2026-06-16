@extends('admin.master',['menu'=>'progress-status', 'sub_menu'=>'progress-status-settings'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$title}}</li>
                    
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="profile-info-form">
        <form action="{{route('progressStatusSettingsUpdate')}}" method="post">
            @csrf
            <div class="row">
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('Enable Progress Status for Deposit')}}</label>
                        <div class="cp-select-area">
                            <select name="progress_status_for_deposit" class="form-control">
                                <option @if(isset($settings['progress_status_for_deposit']) && $settings['progress_status_for_deposit'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                                <option @if(isset($settings['progress_status_for_deposit']) && $settings['progress_status_for_deposit'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("No")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('Enable Progress Status for Withdrawal')}}</label>
                        <div class="cp-select-area">
                            <select name="progress_status_for_withdrawal" class="form-control">
                                <option @if(isset($settings['progress_status_for_withdrawal']) && $settings['progress_status_for_withdrawal'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                                <option @if(isset($settings['progress_status_for_withdrawal']) && $settings['progress_status_for_withdrawal'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("No")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 col-12 mt-20">
                    <button class="button-primary theme-btn">{{__('Update')}}</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    
@endsection
