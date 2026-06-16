<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Cron Setup')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveCronSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Coin Rate Update ( Cron )')}}</label>

                    <div class="cp-select-area">
                        <select name="cron_coin_rate_status" class="form-control" data-width="100%">
                            <option @if(isset($settings['cron_coin_rate_status']) && $settings['cron_coin_rate_status'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                            <option @if(isset($settings['cron_coin_rate_status']) && $settings['cron_coin_rate_status'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Coin Rate Update ( Minutes )')}}</label>
                    <input class="form-control" type="text" name="cron_coin_rate"
                           value="{{$settings['cron_coin_rate'] ?? 10}}">
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('ERC20/BEP20/TRC20 Deposit ( Cron )')}}</label>
                    <div class="cp-select-area">
                        <select name="cron_token_deposit_status" class="form-control" data-width="100%">
                            <option @if(isset($settings['cron_token_deposit_status']) && $settings['cron_token_deposit_status'] == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}">{{ __('ON') }}</option>
                            <option @if(isset($settings['cron_token_deposit_status']) && $settings['cron_token_deposit_status'] == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}">{{ __('OFF') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('ERC20/BEP20/TRC20 Deposit ( Minutes )')}}</label>
                    <input class="form-control" type="text" name="cron_token_deposit"
                           value="{{$settings['cron_token_deposit'] ?? 10}}">
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
