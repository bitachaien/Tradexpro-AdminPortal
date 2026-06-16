@extends('auth.master',['menu'=>'dashboard'])
@section('title', isset($title) ? $title : '')

@section('content')
    <!-- <div class="user-content-wrapper" style="background-image: @if(!empty(settings('login_logo')))  url('{{asset(path_image().settings()['login_logo'])}}') @else url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}') @endif"> -->
    <div class="user-content-wrapper" style="background-image: url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}')">
        <div class="user-content-inner-wrap">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="user-form">
                        <div class="user-form-inner">
                            <div class="form-top">
                                <h2>{{__('Forgot Password ?')}}</h2>
                                <p>{{__('Please enter the email address to request a password reset.')}}</p>
                            </div>
                            {{Form::open(['route' => 'sendForgotMail', 'files' => true])}}
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="{{__('Your email')}}">
                            </div>
                            <button type="submit" class="btn btn-primary nimmu-user-sibmit-button">{{__('Send')}}</button>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-content-text text-center">
                        <h3>{{__('Welcome To')}} {{ settings('app_title') }}</h3>
                        <a class="auth-logo" href="javascript:;">
                            <img src="{{show_image(1,'logo')}}" class="img-fluid" alt="">
                        </a>
                        <p>{{__('Return to ')}} <a href="{{route('login')}}">{{__('Sign in')}}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection
