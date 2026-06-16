@include('email.header_new')
<h3>{{__('Hello')}}, {{isset($name) ? $name : ''}}</h3>
<p>{{$email_message}}</p>

{!! $email_message_table !!}

@include('email.footer_new')
