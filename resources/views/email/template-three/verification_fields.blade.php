@include('email.template-three.header')
<h1 class="h3 fw-700" style="padding-top: 0; padding-bottom: 0; font-weight: 700 !important; vertical-align: baseline; font-size: 28px; line-height: 33.6px; margin: 0;" align="left">
    Hello, {{(!empty(\App\User::where('email',$email)->first())) ? \App\User::where('email',$email)->first()->first_name.' '.\App\User::where('email',$email)->first()->last_name : ' '}}
</h1>
<br>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    {!! $cause !!}
</p>
<p class="" style="line-height: 24px; font-size: 16px; width: 100%; margin: 0;" align="left">
    <b style="text-color:red">{{__('ID Verification Rejected')}}</b> <br/>
    {{__('Please upload again with appropriate file')}}
</p>
@include('email.template-three.footer')