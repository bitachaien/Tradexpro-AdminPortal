<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Api Access Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminApiOverviewSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-4 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Api Access Enable')}}</label>
                    <div class="cp-select-area">
                        <select name="api_access_enable" class="form-control" data-width="100%">
                            <option @if(isset($settings['api_access_enable']) && $settings['api_access_enable'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                            <option @if(isset($settings['api_access_enable']) && $settings['api_access_enable'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Trade Api Access Enable')}}</label>
                    <div class="cp-select-area">
                        <select name="api_access_trade_enable" class="form-control" data-width="100%">
                            <option @if(isset($settings['api_access_trade_enable']) && $settings['api_access_trade_enable'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                            <option @if(isset($settings['api_access_trade_enable']) && $settings['api_access_trade_enable'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Withdrawal Api Access Enable')}}</label>
                    <div class="cp-select-area">
                        <select name="api_access_withdraw_enable" class="form-control" data-width="100%">
                            <option @if(isset($settings['api_access_withdraw_enable']) && $settings['api_access_withdraw_enable'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                            <option @if(isset($settings['api_access_withdraw_enable']) && $settings['api_access_withdraw_enable'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-4 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Enable Generate Secret Key')}}</label>
                    <div class="cp-select-area">
                        <select name="generate_secret_key_enable" class="form-control" data-width="100%">
                            <option @if(isset($settings['generate_secret_key_enable']) && $settings['generate_secret_key_enable'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                            <option @if(isset($settings['generate_secret_key_enable']) && $settings['generate_secret_key_enable'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Enable 2FA for Generate Secret')}}</label>
                    <div class="cp-select-area">
                        <select name="generate_secret_2fa_enable" class="form-control" data-width="100%">
                            <option @if(isset($settings['generate_secret_2fa_enable']) && $settings['generate_secret_2fa_enable'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                            <option @if(isset($settings['generate_secret_2fa_enable']) && $settings['generate_secret_2fa_enable'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Enable Whitelist Ip For Api Access')}}</label>
                    <div class="cp-select-area">
                        <select name="api_secret_whitelist_enable" class="form-control" data-width="100%">
                            <option @if(isset($settings['api_secret_whitelist_enable']) && $settings['api_secret_whitelist_enable'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                            <option @if(isset($settings['api_secret_whitelist_enable']) && $settings['api_secret_whitelist_enable'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Rate Limit Per Minute Per Ip')}}</label>
                    <input class="form-control " type="number" min="1"
                           name="rate_limit_ip_per_minute" placeholder=""
                           value="{{ isset($settings['rate_limit_ip_per_minute']) ? $settings['rate_limit_ip_per_minute'] : 1000 }}">
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
