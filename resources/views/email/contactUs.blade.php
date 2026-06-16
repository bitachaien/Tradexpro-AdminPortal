@include('email.header_new')
<p>From : {{$name}},</p>
<p>Subject : {{$subject}}</p>
<p>{{$details}}</p>

@include('email.footer_new')

