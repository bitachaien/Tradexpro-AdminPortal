@include('email.template-two.header')
<tr>
    <td style="line-height: 24px; font-size: 16px; width: 100%; border-radius: 24px; margin: 0; padding: 40px;" align="left" bgcolor="#ffffff">
    <h3 class="text-center" style="padding-top: 0; padding-bottom: 0; font-weight: 500; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="center">
        Hello, {{ $user->first_name.' '.$user->last_name  }}
    </h3>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        {{__('We need to verify your email address. ')}}
    </p>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        {{__('Your ')}} {{__(' email verification code is ')}}
    </p>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        {{$token}}
    </p>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        To verify your email, Go to: <a href="{{allSetting('exchange_url').'/verify-email'}}">Click Here</a>
    </p>

@include('email.template-two.footer')
                                
