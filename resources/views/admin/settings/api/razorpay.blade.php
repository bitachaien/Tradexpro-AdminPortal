<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Stripe Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveRazorpayApiSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Razorpay Key ID')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="text" name="RAZORPAY_KEY_ID" class="form-control" value="{{$settings['RAZORPAY_KEY_ID'] ?? ''}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Razorpay Secret key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="text" name="RAZORPAY_SECRET_KEY" class="form-control" value="{{$settings['RAZORPAY_SECRET_KEY'] ?? ''}}">
                    @endif
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
