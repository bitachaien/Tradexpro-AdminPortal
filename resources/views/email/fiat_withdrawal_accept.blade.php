@include('email.header_new')
<p>Hi {{$name}},</p>
<p>
<p>
   {{__(' Your currency withdrawal is accepted.')}}
</p>
<p>
    {{__('Here is the proof')}}
</p>
<p>
    <img src="{{$slip}}" alt="" width="100">
</p>

@include('email.footer_new')
