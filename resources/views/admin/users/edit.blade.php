@extends('admin.master',['menu'=>'users','sub_menu'=>'user'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('User management')}}</li>
                    <li class="active-item">{{__('Edit User')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management profile">
        <div class="row">
            <div class="col-12">
                <div class="profile-info padding-40">
                    <div class="row">
                        <div class="col-xl-4 mb-xl-0 mb-4">
                            <div class="user-info text-center">
                                <div class="avater-img">
                                    <img src="{{show_image($user->id,'user')}}" alt="">
                                </div>
                                <h4>{{$user->first_name.' '.$user->last_name}}</h4>
                                <p>{{$user->email}}</p>
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <form action="{{route('UserProfileUpdate')}}" method="post">
                                @csrf
                                <input type="hidden" name="id" value="{{encrypt($user->id)}}">
                                <div class="form-group">
                                    <label for="lastname">{{__('Nick Name')}}</label>
                                    <input name="nickname" value="{{$user->nickname}}" type="text" class="form-control" placeholder="{{__('Nick name')}}">
                                    @error('nickname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="firstname">{{__('First Name')}}</label>
                                    <input name="first_name" value="{{old('first_name',$user->first_name)}}" type="text" class="form-control" id="firstname" placeholder="{{__('First name')}}">
                                    @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="lastname">{{__('Last Name')}}</label>
                                    <input name="last_name" value="{{old('last_name',$user->last_name)}}" type="text" class="form-control" id="lastname" placeholder="{{__('Last name')}}">
                                    @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="lastname">{{__('Phone Number')}}</label>
                                    <input name="phone" type="text" value="{{old('phone',$user->phone)}}" class="form-control" id="phoneVerify" placeholder="{{__('')}}">
                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="email">{{__('Email')}}</label>
                                    <span class="email-input form-control"> {{ $user->email }} </span>
                                </div>
                                <div class="form-group">
                                    <label for="api_access_allow_user">{{__('Allow this user to access api')}}</label>
                                    <div class="cp-select-area">
                                        <select name="api_access_allow_user" id="" class="form-control">
                                            <option @if($user->api_access_allow_user == STATUS_ACTIVE) selected @endif value="1">{{__('Yes')}}</option>
                                            <option @if($user->api_access_allow_user == STATUS_PENDING) selected @endif value="0">{{__('No')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection

@section('script')
@endsection
