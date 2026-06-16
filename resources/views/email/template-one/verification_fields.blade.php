@include('email.template-one.header')
<p>Hi {{(!empty(\App\User::where('email',$email)->first())) ? \App\User::where('email',$email)->first()->first_name.' '.\App\User::where('email',$email)->first()->last_name : ' '}},</p>
<p>

    {!! $cause !!}
</p>
<p>
    <b style="text-color:red">{{__('ID Verification Rejected')}}</b> <br/>
    {{__('Please upload again with appropriate file')}}
</p>

@include('email.template-one.footer')
