<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Fiat Withdrawal')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveFiatWithdrawalSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Fees Type')}}</label>
                    @if(env('APP_MODE') == 'demo')
                    <div class="cp-select-area">
                        <select class="form-control" data-width="100%" data-style="btn-dark">
                            <option @if(isset($settings['fiat_withdrawal_type']) && $settings['fiat_withdrawal_type'] == Fiat_Withdraw_PERCENT ) selected @endif value="{{ Fiat_Withdraw_PERCENT }}">{{ fiat_widthraw_type(Fiat_Withdraw_PERCENT) }}</option>
                            <option @if(isset($settings['fiat_withdrawal_type']) && $settings['fiat_withdrawal_type'] == Fiat_Withdraw_FIXED ) selected @endif value="{{ Fiat_Withdraw_FIXED }}">{{ fiat_widthraw_type(Fiat_Withdraw_FIXED) }}</option>
                        </select>
                    </div>
                    @else
                    <div class="cp-select-area">
                        <select name="fiat_withdrawal_type" class="form-control" data-width="100%" data-style="btn-dark">
                            <option @if(isset($settings['fiat_withdrawal_type']) && $settings['fiat_withdrawal_type'] == Fiat_Withdraw_PERCENT ) selected @endif value="{{ Fiat_Withdraw_PERCENT }}">{{ fiat_widthraw_type(Fiat_Withdraw_PERCENT) }}</option>
                            <option @if(isset($settings['fiat_withdrawal_type']) && $settings['fiat_withdrawal_type'] == Fiat_Withdraw_FIXED ) selected @endif value="{{ Fiat_Withdraw_FIXED }}">{{ fiat_widthraw_type(Fiat_Withdraw_FIXED) }}</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Fees')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                    <input class="form-control" type="text" name="fiat_withdrawal_value"
                           placeholder="0.0000"
                           value="{{$settings['fiat_withdrawal_value'] ?? 0.000}}">
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
