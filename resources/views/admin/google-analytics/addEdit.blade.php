@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'google_analytics'])
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
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('googleAnalyticsIDStore')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="google_analytics_tracking_id">{{__('Google Analytics Tracking Id')}}</label>
                                        @if(env('APP_MODE') == 'demo')
                                            <input class="form-control" value="{{'disablefordemo'}}">
                                        @else
                                        <input type="text" name="google_analytics_tracking_id" class="form-control" id="google_analytics_tracking_id" placeholder="{{__('Update Google Analytics Tracking ID')}}"
                                               @if(isset($data)) value="{{$data}}" @else value="{{old('google_analytics_tracking_id')}}" @endif>
                                        @endif
                                        <span class="text-danger"><strong>{{ $errors->first('google_analytics_tracking_id') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(isset($item)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')

@endsection
