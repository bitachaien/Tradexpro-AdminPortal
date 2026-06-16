<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Coin Api Settings for deposit/withdrawal')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCoinApiSettings')}}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Do you really want to save the option? Note: you can save this option only once, so be careful');">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label class="text-warning">{{__('There are two option for coin api, one is "Personal node"  and other is "coin payment" ')}}</label><br>
                    <label class="text-warning">{{__('if you select "Personal node" , you need to add your node credential at "Personal Node Setting" tab')}}</label><br>
                    <label class="text-warning">{{__('or if you select "coin payment", you need to add your coin payment credential at "Coin payment Setting" tab')}}</label><br>
                    <label class="text-danger">{{__('Note: you can save this option only once, so be careful ')}}</label><br>
                    <label>{{__('Choose an option ')}}</label>
                    <div class="cp-select-area">
                        <select name="coin_api_settings" class="form-control">
                            <option @if(isset($settings['coin_api_settings']) && $settings['coin_api_settings'] == COIN_PAYMENT) selected @endif value="{{COIN_PAYMENT}}">{{__("Coin Payment")}}</option>
                            <option @if(isset($settings['coin_api_settings']) && $settings['coin_api_settings'] == BITCOIN_API) selected @endif value="{{BITCOIN_API}}">{{__("Personal Node")}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Save')}}</button>
            </div>
        </div>
    </form>
</div>
