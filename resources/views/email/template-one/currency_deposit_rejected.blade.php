@include('email.template-one.header')
<p>Hi {{$user_name}},</p>
<p>
<p>
    Your currency deposit is rejected. Rejected reason
</p>
<p>
    {!! $rejected_note !!}
</p>
@include('email.template-one.footer')