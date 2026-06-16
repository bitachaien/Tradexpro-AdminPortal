<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Update Live Chat api')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Enable live chat')}}</label>
                    <div class="cp-select-area">
                        <select name="live_chat_status" class="form-control">
                            <option @if(isset($settings['live_chat_status']) && $settings['live_chat_status'] == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                            <option @if(isset($settings['live_chat_status']) && $settings['live_chat_status'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label
                        for="#">{{__('Chat api key')}} </label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control " type="text"
                           name="live_chat_key" placeholder=""
                           value="{{isset($settings['live_chat_key']) ? $settings['live_chat_key'] : ''}}">
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
