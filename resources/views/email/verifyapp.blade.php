@include('email.header_new')
<h3>{{__('Hello')}}, {{ $user->first_name.' '.$user->last_name  }}</h3>
<p>
    {{__('We need to verify your email address. ')}}
</p>
<p>   {{__('Your ')}} {{__(' email verification code is ')}} : </p>
<h3>{{$token}}</h3>

<p>To verify your email, Go to: <a href="{{allSetting('exchange_url').'/verify-email'}}">Click Here</a></p>

@include('email.footer_new')

