@include('email.template-three.header')
<h1 class="h3 fw-700" style="padding-top: 0; padding-bottom: 0; font-weight: 700 !important; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
    Hello, {{ $user->first_name.' '.$user->last_name  }}
</h1>
<br>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{__('We need to verify your email address. In order to verify your account please click on the following link or paste the link on address bar of your browser and hit -')}}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    <a style="text-decoration: none;background: #4A9A4D;color: #fff;padding: 5px 10px;border-radius: 3px;" href="{{route('verifyWeb').'?token='.encrypt($key).'email'.encrypt($data->email)}}">{{__('Verify')}}</a>
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{__('Or')}}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    Your {{allSetting()['app_title']}} email verification code is :
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{$token}}
</p>
@include('email.template-three.footer')
