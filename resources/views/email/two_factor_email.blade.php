@include('email.header_new')
<p>
    Your {{allSetting()['app_title']}} email verification code is {{$key}}.
</p>
@include('email.footer_new')
