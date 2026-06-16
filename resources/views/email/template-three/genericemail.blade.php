@include('email.template-three.header')
<h1 class="h3 fw-700" style="padding-top: 0; padding-bottom: 0; font-weight: 700 !important; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
    Dear User,
</h1>
<br>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {!! $email_message !!}
</p>
@include('email.template-three.footer')