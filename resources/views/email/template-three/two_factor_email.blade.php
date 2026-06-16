@include('email.template-three.header')

<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    Your {{allSetting()['app_title']}} email verification code is {{$key}}.
</p>
@include('email.template-three.footer')