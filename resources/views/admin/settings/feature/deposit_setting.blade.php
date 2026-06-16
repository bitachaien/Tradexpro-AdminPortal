<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Fiat Deposit/Withdrawal Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Enable Fiat Deposit')}}</label>
                    <div class="cp-select-area">
                        <select name="currency_deposit_status" class="form-control">
                            <option @if(isset($settings['currency_deposit_status']) && $settings['currency_deposit_status'] == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                            <option @if(isset($settings['currency_deposit_status']) && $settings['currency_deposit_status'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Enable Fiat Deposit 2FA ')}}</label>
                    <div class="cp-select-area">
                        <select name="currency_deposit_2fa_status" class="form-control">
                            <option @if(isset($settings['currency_deposit_2fa_status']) && $settings['currency_deposit_2fa_status'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                            <option @if(isset($settings['currency_deposit_2fa_status']) && $settings['currency_deposit_2fa_status'] == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Fiat Deposit Fees Type')}}</label>
                    <div class="cp-select-area">
                        <select name="fiat_deposit_fees_type" class="form-control" data-width="100%" data-style="btn-dark">
                            <option @if(isset($settings['fiat_deposit_fees_type']) && $settings['fiat_deposit_fees_type'] == Fiat_Withdraw_PERCENT ) selected @endif value="{{ Fiat_Withdraw_PERCENT }}">{{ fiat_widthraw_type(Fiat_Withdraw_PERCENT) }}</option>
                            <option @if(isset($settings['fiat_deposit_fees_type']) && $settings['fiat_deposit_fees_type'] == Fiat_Withdraw_FIXED ) selected @endif value="{{ Fiat_Withdraw_FIXED }}">{{ fiat_widthraw_type(Fiat_Withdraw_FIXED) }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Fiat Deposit Fees')}}</label>

                    <input class="form-control" type="text" name="fiat_deposit_fees_value"
                           placeholder="0.0000"
                           value="{{$settings['fiat_deposit_fees_value'] ?? 0.000}}">

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Fiat Withdrawal Fees Type')}}</label>
                    <div class="cp-select-area">
                        <select name="fiat_withdrawal_type" class="form-control" data-width="100%" data-style="btn-dark">
                            <option @if(isset($settings['fiat_withdrawal_type']) && $settings['fiat_withdrawal_type'] == Fiat_Withdraw_PERCENT ) selected @endif value="{{ Fiat_Withdraw_PERCENT }}">{{ fiat_widthraw_type(Fiat_Withdraw_PERCENT) }}</option>
                            <option @if(isset($settings['fiat_withdrawal_type']) && $settings['fiat_withdrawal_type'] == Fiat_Withdraw_FIXED ) selected @endif value="{{ Fiat_Withdraw_FIXED }}">{{ fiat_widthraw_type(Fiat_Withdraw_FIXED) }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Fiat Withdrawal Fees')}}</label>

                    <input class="form-control" type="text" name="fiat_withdrawal_value"
                           placeholder="0.0000"
                           value="{{$settings['fiat_withdrawal_value'] ?? 0.000}}">

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
