@include('email.template-three.header')
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    From : {{$name}},
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    Subject : {{$subject}}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {{$details}}
</p>
@include('email.template-three.footer')