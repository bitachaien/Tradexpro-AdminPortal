@include('email.header_new')
<p>Hi {{$user_name}},</p>
<p>
<p>
    Your currency deposit is rejected. Rejected reason
</p>
<p>
    {!! $rejected_note !!}
</p>

@include('email.footer_new')