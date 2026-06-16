@include('email.template-three.header')
<h1 class="h3 fw-700" style="padding-top: 0; padding-bottom: 0; font-weight: 700 !important; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
    Hello, {{$user_name}}t
</h1>
<br>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    Your currency deposit is rejected. Rejected reason
</p>

<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {!! $rejected_note !!}
</p>
@include('email.template-three.footer')