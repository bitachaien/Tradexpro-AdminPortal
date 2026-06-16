@extends('auth.master',['menu'=>'dashboard'])
@section('title', isset($title) ? $title : __('Google auth two factor'))

@section('content')
    <!-- <div class="user-content-wrapper" style="background-image: @if(!empty(settings('login_logo')))  url('{{asset(path_image().settings()['login_logo'])}}') @else url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}') @endif"> -->
    <div class="user-content-wrapper" style="background-image: url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}')">
        <div class="user-content-inner-wrap">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="user-form">
                        <div class="user-form-inner">
                            <div class="form-top">
                                <h2>{{__('Two Factor Authentication')}}</h2>
                                <div class="d-flex justify-content-center" id="two_factor_list">
                                    @if(in_array(GOOGLE_AUTH,$two_factor) && Auth::user()->g2f_enabled == ENABLE)
                                    <div class="btn btn-dark hover_2fa ml-1 mr-1" data-type="{{GOOGLE_AUTH}}" onclick="twoFactorSet({{GOOGLE_AUTH}})">Google</div>
                                    @endif
                                    @if(in_array(EMAIL_AUTH,$two_factor) && Auth::user()->email_enabled == ENABLE)
                                    <div class="btn btn-dark hover_2fa ml-1 mr-1" data-type="{{EMAIL_AUTH}}" onclick="twoFactorSet({{EMAIL_AUTH}})">Email</div>
                                    @endif
                                    @if(in_array(PHONE_AUTH,$two_factor) && Auth::user()->phone_enabled == ENABLE)
                                    <div class="btn btn-dark hover_2fa ml-1 mr-1" data-type="{{PHONE_AUTH}}" onclick="twoFactorSet({{PHONE_AUTH}})">Phone</div>
                                    @endif
                                </div>
                                <p id="app_title"></p>
                            {{Form::open(['route' => 'twoFactorVerify', 'files' => true])}}
                            <div class="form-group">
                                <label>{{__('Authentication Code')}}</label>
                                <div id="resend_btn" class="text-right"></div>
                                <input type="text" value="{{old('code')}}" id="exampleInputEmail1" name="code"
                                    class="form-control" placeholder="{{__('code')}}">
                                @error('code')
                                    <p class="invalid-feedback">{{ $message }} </p>
                                @enderror
                                <input type="hidden" name="code_type" value="">
                            </div>


                            <button type="submit" class="btn btn-primary nimmu-user-sibmit-button">{{__('Verify')}}</button>
                            @if(in_array(PHONE_AUTH,$two_factor) && Auth::user()->phone_enabled == ENABLE)
{{--                                <a href="{{ route('verifyPhone') }}" class="btn btn-primary nimmu-user-sibmit-button" style="background-color: #333; margin-bottom: 5px;">{{__('Verify By Phone')}}</a>--}}
                            @endif
                            @if(in_array(EMAIL_AUTH,$two_factor) && Auth::user()->email_enabled == ENABLE)
{{--                                <a href="{{ route('verifyEmail') }}" class="btn btn-primary nimmu-user-sibmit-button" style="background-color: #333;">{{__('Verify By Email')}}</a>--}}
                            @endif
                            {{Form::close()}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-content-text text-center">
                        <h3>{{__('Welcome To')}} {{ settings('app_title') }}</h3>
                        <a class="auth-logo" href="javascript:;">
                            <img src="{{show_image(1,'logo')}}" class="img-fluid" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            $(".toggle-password").on('click', function () {
                $(this).toggleClass("fa-eye-slash fa-eye");
            });

            $(".eye").on('click', function () {
                var $pwd = $(".look-pass-a");
                if ($pwd.attr('type') === 'password') {
                    $pwd.attr('type', 'text');
                } else {
                    $pwd.attr('type', 'password');
                }
            });
        })(jQuery)
        var titles = {
            {{GOOGLE_AUTH}} : "{{__('Open your authentication app and enter the code for')}} {{$setting['app_title']}}",
            {{EMAIL_AUTH}} : "{{__('Check email and enter the code for')}} {{$setting['app_title']}}",
            {{PHONE_AUTH}} : "{{__('Check otp sms and enter the code for')}} {{$setting['app_title']}}",
        };
        function twoFactorSet(type){
            var list = $("#two_factor_list").children();
            var t = null;
            var code_type = $('input[name="code_type"]');
            list.each((a,b)=>{
                let stype = $(b).data("type");
                $(b).css({
                    "color" : "white",
                    "background-color" : "#333333",
                });
                if(type==stype)   t = $(b);
                if(type==stype)   t = $(b);
                if(type==stype)   t = $(b);
            });
            t.css({
                "color" : "black",
                "background-color" : "#FFCE51",
            });
            if({{GOOGLE_AUTH}}==type){
                $("#app_title").text(titles[{{GOOGLE_AUTH}}]);
                code_type.val({{ GOOGLE_AUTH }});
                $('#resend_btn').html('');
            }
            if({{EMAIL_AUTH}}==type){
                $("#app_title").text(titles[{{EMAIL_AUTH}}]);
                code_type.val({{ EMAIL_AUTH }});
                $('#resend_btn').html('<a href="#" class="lh-sm  btn btn-success" onclick="sendEmailSMS({{ EMAIL_AUTH }},this)">{{__('Resend Email')}}</a>');
            }
            if({{PHONE_AUTH}}==type){
                $("#app_title").text(titles[{{PHONE_AUTH}}]);
                code_type.val({{ PHONE_AUTH }});
                $('#resend_btn').html('<a href="#" class="lh-base btn btn-success" onclick="sendEmailSMS({{ PHONE_AUTH }},this)">{{__('Resend SMS')}}</a>');
            }
        }
        function sendEmailSMS(type,t){
            var url = '';
            if({{EMAIL_AUTH}}==type){
                url = '{{ route("verifyEmail") }}'
            }
            if({{PHONE_AUTH}}==type){
                url = '{{ route("verifyPhone") }}'
            }
            $(t).text('{{ __("Sending") }}');
            $.get(url,function(data){
                $(t).text('{{ __("Resend") }}');

                VanillaToasts.create({
                    title: data.message,
                    text: '',
                    timeout: 4000
                });
            });
        }
        var list = $("#two_factor_list").children();
        let firstOne = 1;
        list.each((a,b)=>{
            let type = $(b).data("type");
            if(firstOne==1)twoFactorSet(type);
            firstOne++;
        });
    </script>
@endsection
