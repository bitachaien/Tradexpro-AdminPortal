<div class="header-bar">
    <div class="table-title">
        <h3>{{__('PayStack Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSavePaystackApiSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Paystack public key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="text" name="PAYSTACK_KEY" class="form-control" value="{{$settings['PAYSTACK_KEY'] ?? ''}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Paystack Secret key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="text" name="PAYSTACK_SECRET" class="form-control" value="{{$settings['PAYSTACK_SECRET'] ?? ''}}">
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
