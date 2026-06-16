@include('email.template-one.header')
<p>From : {{$name}},</p>
<p>Subject : {{$subject}}</p>
<p>{{$details}}</p>
@include('email.template-one.footer')