@include('email.template-one.header')
<h3>{{__('Hello')}}, {{isset($name) ? $name : ''}}</h3>
<p>{{$email_message}}</p>

{!! $email_message_table !!}

@include('email.template-one.footer')
