<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Enable SMS settings')}}</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="profile-info-form">
            <form action="{{route('adminChooseSmsSettings')}}" method="post">
                @csrf
                <div class="row">
                    <div class="col-12 mt-20">
                        <div class="form-group">
                            <label for="#">{{__('SMS Gateway Type')}}</label>
                            <div class="cp-select-area">
                                <select name="select_sms_type" class="form-control">
                                    @foreach (smsTypeList() as $sms_key=>$sms_name)
                                        <option value="{{$sms_key}}"
                                            @if (isset($settings['select_sms_type']) && $settings['select_sms_type'] == $sms_key)
                                                selected
                                            @endif>{{$sms_name}}</option>
                                    @endforeach
                                </select>
                            </div>
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
    </div>
    <div class="col-md-6">
        <div class="profile-info-form">
            <form action="{{route('adminSendTestSms')}}" method="post">
                @csrf
                <div class="row">
                    <div class="col-12 mt-20">
                        <div class="form-group">
                            <label for="#">{{__('Test SMS Send')}}</label>
                            <input class="form-control" type="text" name="mobile" >
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 col-12 mt-20">
                        <button type="submit" class="button-primary theme-btn">{{__('Send')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="header-bar mt-3">
    <div class="table-title">
        <h3>{{__('Twillo Setup')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveSmsSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Twillo Secret Key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="twillo_secret_key"
                           placeholder="{{__('Secret Key')}}"
                           value="{{$settings['twillo_secret_key']}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Auth Token')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="twillo_auth_token"
                           placeholder="{{__('Auth Token')}}"
                           value="{{$settings['twillo_auth_token']}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('From Number')}}</label>
                    <input class="form-control" type="text" name="twillo_number"
                           placeholder="{{__('Number')}}"
                           value="{{isset($settings['twillo_number']) ? $settings['twillo_number'] : ''}}">
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

<div class="header-bar mt-3">
    <div class="table-title">
        <h3>{{__('Vonage/Nexmo Setup')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminNexmoSmsSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Vonage/Nexmo Secret Key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="nexmo_secret_key"
                           placeholder="{{__('Secret Key')}}"
                           value="{{$settings['nexmo_secret_key']}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Vonage/Nexmo API Key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="nexmo_api_key"
                           placeholder="{{__('Vonage/Nexmo API Key')}}"
                           value="{{$settings['nexmo_api_key']}}">
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

<div class="header-bar mt-3">
    <div class="table-title">
        <h3>{{__("Africa's Talking Setup")}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminAfricaTalkSmsSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Select Apps Mode')}}</label>
                    <select name="africa_talk_app_mode" class="form-control">
                        <option value="sandbox"
                            {{$settings['africa_talk_app_mode'] == 'sandbox'?'selected':''}}>{{__('SandBox')}} </option>
                        @if(env('APP_MODE') != 'demo')
                            <option value="live"
                                {{$settings['africa_talk_app_mode'] == 'live'?'selected':''}}>{{__('Live')}} </option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Apps User Name')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="africa_talk_user_name"
                           placeholder="{{__('Secret Key')}}"
                           value="{{$settings['africa_talk_user_name']}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('API Key')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="africa_talk_api_key"
                           placeholder="{{__('API Key')}}"
                           value="{{$settings['africa_talk_api_key']}}">
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
