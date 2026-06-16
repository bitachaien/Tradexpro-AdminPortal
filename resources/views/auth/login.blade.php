@extends('auth.master',['menu'=>'dashboard'])
@section('title', isset($title) ? $title : __('Admin Login'))

@section('content')
    <!-- <div class="user-content-wrapper" style="background-image: @if(!empty(settings('login_logo')))  url('{{asset(path_image().settings()['login_logo'])}}') @else url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}') @endif"> -->
    <div class="user-content-wrapper" style="background-image: url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}')">
        <div class="user-content-inner-wrap">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="user-form">
                        <div class="user-form-inner">
                            <div class="form-top">
                                <h2>{{__('Sign In')}}</h2>
                                <p>{{__('Please sign in to your account')}}</p>
                            </div>
                            {{Form::open(['route' => 'loginProcess', 'files' => true, 'id'=>'submit-form'] )}}
                            <div class="form-group">
                                <input type="email" value="{{old('email')}}" id="email" name="email"
                                        class="form-control" placeholder="{{__('Your email')}}">
                                @error('email')
                                <p class="invalid-feedback">{{ $message }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" id="password"
                                        class="form-control form-control-password look-pass-a"
                                        placeholder="{{__('Your password')}}">
                                @error('password')
                                <p class="invalid-feedback">{{ $message }} </p>
                                @enderror
                                <span class="eye"><i class="fa fa-eye-slash toggle-password"
                                                        onclick="showHidePassword('old_password')"></i></span>
                            </div>
                            @if(settings('select_captcha_type') == CAPTCHA_TYPE_RECAPTCHA)
                                <div class="form-group">
                                    <label></label>
                                    {!! app('captcha')->display() !!}
                                    @error('g-recaptcha-response')
                                    <p class="invalid-feedback">{{ $message }} </p>
                                    @enderror
                                </div>
                            @endif
                            
                            @if (settings('select_captcha_type') == CAPTCHA_TYPE_GEETESTCAPTCHA)
                                <div id="captcha"></div>
                                <input id="lot_number" type="hidden" name="lot_number" value="">
                                <input id="captcha_output" type="hidden" name="captcha_output" value="">
                                <input id="pass_token" type="hidden" name="pass_token" value="">
                                <input id="gen_time" type="hidden" name="gen_time" value="">
                            @endif

                            <div class="d-flex justify-content-between rememberme align-items-center mb-4">
                                <div class="text-right"><a class="text-theme forgot-password" href="{{route('forgotPassword')}}">{{__('Forgot Password?')}}</a>
                                </div>
                            </div>
                            @if (settings('select_captcha_type') == CAPTCHA_TYPE_GEETESTCAPTCHA)
                                <button type="button" class="btn btn-primary nimmu-user-sibmit-button" id="submit-login">{{__('Sign In')}}</button>
                            @else
                                <button type="submit" class="btn btn-primary nimmu-user-sibmit-button">{{__('Sign In')}}</button>
                            @endif
                            {{Form::close()}}
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
    </script>

{{--toast message--}}
<script src="{{asset('assets/common/toast/vanillatoasts.js')}}"></script>
<script src="{{asset('assets/common/geetest-captcha/geetest.js')}}"></script>

<script>
    
    if('{{settings('select_captcha_type') == CAPTCHA_TYPE_GEETESTCAPTCHA}}')
    {
        $('#submit-login').click( function(){
            
            var email = $('#email').val();
            var password = $('#password').val();

            if(email === '')
            {
                VanillaToasts.create({
                    text: 'Email field is required',
                    type: 'warning',
                    timeout: 10000

                });
            
            }
            if(password === '')
            {
                VanillaToasts.create({
                    text: 'Password field is required',
                    type: 'warning',
                    timeout: 10000

                });
            
            }

            if(email !== '' && password !== '') {
                initGeetest4(
                    {
                        captchaId: "{{ settings('GEETEST_CAPTCHA_ID')}}",
                        product: "bind",
                    },
                    function (captcha) {
                        // call appendTo to insert CAPTCHA into an element of the page, which can be customized by you
                        captcha.appendTo("#captcha");
                        captcha
                        
                        .onSuccess(function () {
                            var result = captcha.getValidate();

                            $('#lot_number').val(result.lot_number);
                            $('#captcha_output').val(result.captcha_output);
                            $('#pass_token').val(result.pass_token);
                            $('#gen_time').val(result.gen_time);

                            $('#submit-form').submit();
                        });
                        
                        captcha.showCaptcha(); //show the CAPTCHA

                        
                    }
                );
            }
            
        });
    }

</script>


@endsection
