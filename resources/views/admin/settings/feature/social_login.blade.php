<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Social Login enable/disable')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Social Login')}}</label>
                    <div class="cp-select-area">
                        <select name="social_login_enable" class="form-control">
                            <option @if(isset($settings['social_login_enable']) && $settings['social_login_enable'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                            <option @if(isset($settings['social_login_enable']) && $settings['social_login_enable'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Google Login')}}</label>
                    <div class="cp-select-area">
                        <select name="social_login_google_enable" class="form-control">
                            <option @if(isset($settings['social_login_google_enable']) && $settings['social_login_google_enable'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                            <option @if(isset($settings['social_login_google_enable']) && $settings['social_login_google_enable'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Google Client ID')}}</label>
                    <div class="section-width">
                        <input class="form-control" type="text"  name="social_login_google_client_id" value="{{ $settings['social_login_google_client_id'] ?? '' }}" />
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Google Redirect URL')}}</label>
                    <div class="section-width">
                        <input class="form-control" type="text"  name="social_login_google_redirect_url" value="{{ $settings['social_login_google_redirect_url'] ?? '' }}" />
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('FaceBook Login')}}</label>
                    <div class="cp-select-area">
                        <select name="social_login_facebook_enable" class="form-control">
                            <option @if(isset($settings['social_login_facebook_enable']) && $settings['social_login_facebook_enable'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                            <option @if(isset($settings['social_login_facebook_enable']) && $settings['social_login_facebook_enable'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Facebook App ID')}}</label>
                    <div class="section-width">
                        <input class="form-control" type="text"  name="social_login_facebook_app_id" value="{{ $settings['social_login_facebook_app_id'] ?? '' }}"  />
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Apple Login')}}</label>
                    <div class="cp-select-area">
                        <select name="social_login_apple_enable" class="form-control">
                            <option @if(isset($settings['social_login_apple_enable']) && $settings['social_login_apple_enable'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                            <option @if(isset($settings['social_login_apple_enable']) && $settings['social_login_apple_enable'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Web Apple ID')}}</label>
                    <div class="section-width">
                        <input class="form-control" type="text"  name="social_login_apple_id" value="{{ $settings['social_login_apple_id'] ?? '' }}"  />
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('App Apple ID')}}</label>
                    <div class="section-width">
                        <input class="form-control" type="text"  name="social_login_app_apple_id" value="{{ $settings['social_login_app_apple_id'] ?? '' }}"  />
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Apple Redirect URL')}}</label>
                    <div class="section-width">
                        <input class="form-control" type="text"  name="social_login_apple_redirect_url" value="{{ $settings['social_login_apple_redirect_url'] ?? '' }}" />
                    </div>
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
