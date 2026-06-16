@include('email.template-two.header')
<tr>
    <td style="line-height: 24px; font-size: 16px; width: 100%; border-radius: 24px; margin: 0; padding: 40px;" align="left" bgcolor="#ffffff">
    <h3 class="text-center" style="padding-top: 0; padding-bottom: 0; font-weight: 500; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="center">
        Hello, {{isset($user) ? $user->first_name.' '.$user->last_name : ''}}
    </h3>
    <p>
        {{ __('Congratulations and welcome to :company, Now on you are an admin.',['company' => $companyName]) }}
    </p>
    <p>
        {{ __('Login email : :email',['email' => $user->email]) }}
    <br>
        {{ __('Login password : :password',['password' => $password]) }}
    </p>
    <p>
        {{__('Also use the code below to reset your password.')}}
    </p>
    <p style="text-align:center;font-size:30px;font-weight:bold">
        {{$token}}
    </p>
    @if(isset($user))
            <p>{{__('You can change your password with this link')}} : <a href="{{settings('exchange_url').'/reset-password'}}">{{__('Click Here')}}</a></p>
    @endif

@include('email.template-two.footer')
                                
