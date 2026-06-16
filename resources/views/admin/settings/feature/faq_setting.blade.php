<div class="header-bar">
    <div class="table-title">
        <h3>{{__('FAQ setting enable disable')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Enable FAQ at Fiat Deposit')}}</label>
                    <div class="cp-select-area">
                        <select name="currency_deposit_faq_status" class="form-control">
                            <option @if(isset($settings['currency_deposit_faq_status']) && $settings['currency_deposit_faq_status'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                            <option @if(isset($settings['currency_deposit_faq_status']) && $settings['currency_deposit_faq_status'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("No")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Enable FAQ at Withdrawal')}}</label>
                    <div class="cp-select-area">
                        <select name="withdrawal_faq_status" class="form-control">
                            <option @if(isset($settings['withdrawal_faq_status']) && $settings['withdrawal_faq_status'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                            <option @if(isset($settings['withdrawal_faq_status']) && $settings['withdrawal_faq_status'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("No")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Enable FAQ at Coin Deposit')}}</label>
                    <div class="cp-select-area">
                        <select name="coin_deposit_faq_status" class="form-control">
                            <option @if(isset($settings['coin_deposit_faq_status']) && $settings['coin_deposit_faq_status'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                            <option @if(isset($settings['coin_deposit_faq_status']) && $settings['coin_deposit_faq_status'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("No")}}</option>
                        </select>
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
