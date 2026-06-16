@include('email.template-one.header')
<h3>{{__('Hello')}}, {{ $user->first_name.' '.$user->last_name  }}</h3>
<p>
    {{__('We need to verify your email address. In order to verify your account please click on the following link or paste the link on address bar of your browser and hit -')}}
</p>
<p>
    <a style="text-decoration: none;background: #4A9A4D;color: #fff;padding: 5px 10px;border-radius: 3px;" href="{{route('verifyWeb').'?token='.encrypt($key).'email'.encrypt($data->email)}}">{{__('Verify')}}</a>
</p>
<p>{{__('Or')}}</p>
<p>   Your {{allSetting()['app_title']}} email verification code is : </p>
<h3>{{$token}}</h3>

@include('email.template-one.footer')
