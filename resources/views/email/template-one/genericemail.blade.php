@include('email.template-one.header')
<h4 style="margin: 0;">Dear User,</h4>
<p>{!! $email_message !!}</p>
@include('email.template-one.footer')