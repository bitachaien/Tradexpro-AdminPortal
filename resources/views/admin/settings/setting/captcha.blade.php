<div class="header-bar">
    <div class="table-title">
        <h3>{{ __('Capcha Settings') }}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{ route('adminCapchaSettings') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{ __('Choose Captcha Type') }}</label>
                    <div class="cp-select-area">
                        <select name="select_captcha_type" class="form-control">
                            @foreach (captchTypeList() as $captcha_key => $captcha_item)
                                <option value="{{ $captcha_key }}" @if (isset($settings['select_captcha_type']) && $settings['select_captcha_type'] == $captcha_key) selected @endif>
                                    {{ $captcha_item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-white">{{ __('Google Re-captcha Credentials (v2)') }}</h4>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('Google Re-Captcha Site key') }} </label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control " type="text" name="NOCAPTCHA_SITEKEY" placeholder=""
                            value="{{ $settings['NOCAPTCHA_SITEKEY'] ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('Google Re-Captcha Secret') }} </label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control " type="text" name="NOCAPTCHA_SECRET" placeholder=""
                            value="{{ $settings['NOCAPTCHA_SECRET'] ?? '' }}">
                    @endif
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-white">{{ __('GeeTest Captcha Credentials') }}</h4>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('GeeTest Captcha ID') }} </label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control " type="text" name="GEETEST_CAPTCHA_ID" placeholder=""
                            value="{{ $settings['GEETEST_CAPTCHA_ID'] ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('GeeTest Captcha Key') }} </label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control " type="text" name="GEETEST_CAPTCHA_KEY" placeholder=""
                            value="{{ $settings['GEETEST_CAPTCHA_KEY'] ?? '' }}">
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{ __('Update') }}</button>
            </div>
        </div>
    </form>
</div>
