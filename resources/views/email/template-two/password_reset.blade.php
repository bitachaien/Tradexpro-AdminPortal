@include('email.template-two.header')
<tr>
    <td style="line-height: 24px; font-size: 16px; width: 100%; border-radius: 24px; margin: 0; padding: 40px;" align="left" bgcolor="#ffffff">
    <h3 class="text-center" style="padding-top: 0; padding-bottom: 0; font-weight: 500; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="center">
        Hello, {{isset($user) ? $user->first_name.' '.$user->last_name : ''}}
    </h3>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        {{ __('You are receiving this email because we received a password reset request for your account.') }}
    </p>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        {{__('Please use the code below to reset your password.')}}
    </p>
    <p style="text-align:center;font-size:30px;font-weight:bold">
        {{$token ?? ""}}
    </p>
    @if(isset($user))
        @if($user->role == USER_ROLE_ADMIN)
            <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
                {{__('You can change your password with this link')}} :<a href="{{route('resetPasswordPage')}}">{{__('Click Here')}}</a>
            </p>
        @else
            <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
                {{__('You can change your password with this link')}} : <a href="{{settings('exchange_url').'/reset-password'}}">{{__('Click Here')}}</a>
            </p>
        @endif
    @endif

@include('email.template-two.footer')
                                
