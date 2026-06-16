@include('email.template-three.header')
<h1 class="h3 fw-700" style="padding-top: 0; padding-bottom: 0; font-weight: 700 !important; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
    Hello, {{ $user->first_name.' '.$user->last_name  }}
</h1>
<br>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{__('We need to verify your email address. ')}}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{__('Your ')}} {{__(' email verification code is ')}} :
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{$token}}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    To verify your email, Go to: <a href="{{allSetting('exchange_url').'/verify-email'}}">Click Here</a>
</p>
@include('email.template-three.footer')