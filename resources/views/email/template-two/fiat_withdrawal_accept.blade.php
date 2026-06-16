@include('email.template-two.header')
<tr>
    <td style="line-height: 24px; font-size: 16px; width: 100%; border-radius: 24px; margin: 0; padding: 40px;" align="left" bgcolor="#ffffff">
    <h3 class="text-center" style="padding-top: 0; padding-bottom: 0; font-weight: 500; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="center">
        Hello, {{$name}}
    </h3>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        {{__(' Your currency withdrawal is accepted.')}}
    </p>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        {{__('Here is the proof')}}
    </p>
    <p class="text-center text-muted" style="line-height: 24px; font-size: 16px; color: #718096; width: 100%; margin: 0;" align="center">
        <img src="{{$slip}}" alt="" width="100">
    </p>

@include('email.template-two.footer')