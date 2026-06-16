@include('email.template-one.header')
<p>
    Your {{allSetting()['app_title']}} email verification code is {{$key}}.
</p>
@include('email.template-one.footer')
