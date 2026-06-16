@include('email.header_new')
<h3>{{__('Hello')}}, {{isset($user) ? $user->first_name.' '.$user->last_name : ''}}</h3>
<p>
    {{ __('You are receiving this email because we received a password reset request for your account.') }}
    {{__('Please use the code below to reset your password.')}}
</p>
<p style="text-align:center;font-size:30px;font-weight:bold">
    {{$token}}
</p>
@if(isset($user))
    @if($user->role == USER_ROLE_ADMIN)
        <p>{{__('You can change your password with this link')}} : <a href="{{route('resetPasswordPage')}}">{{__('Click Here')}}</a></p>
    @else
        <p>{{__('You can change your password with this link')}} : <a href="{{settings('exchange_url').'/reset-password'}}">{{__('Click Here')}}</a></p>
    @endif
@endif
@include('email.footer_new')
