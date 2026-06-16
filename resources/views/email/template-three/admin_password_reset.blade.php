@include('email.template-three.header')
<h1 class="h3 fw-700" style="padding-top: 0; padding-bottom: 0; font-weight: 700 !important; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
    Hello, {{isset($user) ? $user->first_name.' '.$user->last_name : ''}}
</h1>
<br>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{ __('Congratulations and welcome to :company, Now on you are an admin.',['company' => $companyName]) }}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{ __('Login email : :email',['email' => $user->email]) }}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{ __('Login password : :password',['password' => $password]) }}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{__('Also use the code below to reset your password.')}}
</p>

<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{$token}}
</p>
@if(isset($user))
    <p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
        {{__('Also use the code below to reset your password.')}}
    </p>
@endif
@include('email.template-three.footer')