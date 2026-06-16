@include('email.template-three.header')
<h1 class="h3 fw-700" style="padding-top: 0; padding-bottom: 0; font-weight: 700 !important; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
    Hello, {{isset($user) ? $user->first_name.' '.$user->last_name : ''}}
</h1>
<br>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{ __('You are receiving this email because we received a password reset request for your account.') }}
    {{__('Please use the code below to reset your password.')}}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{$token}}
</p>
@if(isset($user))
    @if($user->role == USER_ROLE_ADMIN)
        <p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
            {{__('You can change your password with this link')}} : <a href="{{route('resetPasswordPage')}}">{{__('Click Here')}}</a>
        </p>
    @else
        <p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
            {{__('You can change your password with this link')}} : <a href="{{settings('exchange_url').'/reset-password'}}">{{__('Click Here')}}</a>
        </p>
    @endif
@endif
@include('email.template-three.footer')