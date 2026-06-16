<div class="header-bar">
    <div class="table-title">
        <h3>{{__('CryproCompare Api Details')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveOtherApiSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('CryptoCompare api key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input type="text" name="CRYPTOCOMPARE_API_KEY" class="form-control" value="{{$settings['CRYPTOCOMPARE_API_KEY'] ?? ''}}">
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
