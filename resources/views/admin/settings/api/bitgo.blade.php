<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Bitgo Api Details')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveBitgoSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Bitgo Api Url')}}</label>
                    <input type="text" name="bitgo_api" class="form-control" value="{{$settings['bitgo_api'] ?? ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Bitgo Express Url')}}</label>
                    <input type="text" name="bitgoExpess" class="form-control" value="{{$settings['bitgoExpess'] ?? ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Bitgo Env')}}</label>
                    <input type="text" name="BITGO_ENV" class="form-control" value="{{$settings['BITGO_ENV'] ?? ''}}">
                    <small>{{__('Must be "test" or "live" only')}}</small>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Bitgo Access Token')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="text" name="bitgo_token" class="form-control" value="{{$settings['bitgo_token'] ?? ''}}">
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
