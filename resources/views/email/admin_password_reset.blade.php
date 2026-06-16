@include('email.header_new')
<h3>{{__('Hello')}}, {{isset($user) ? $user->first_name.' '.$user->last_name : ''}}</h3>
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
@include('email.footer_new')
