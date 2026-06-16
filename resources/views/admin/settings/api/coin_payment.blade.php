<div class="header-bar">
    <div class="table-title">
        <h3>{{ __('Coin Payment Details') }}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{ route('adminSavePaymentSettings') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('COIN PAYMENT PUBLIC KEY') }}</label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control" type="text" name="COIN_PAYMENT_PUBLIC_KEY" autocomplete="off"
                            placeholder="" value="{{ $settings['COIN_PAYMENT_PUBLIC_KEY'] ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('COIN PAYMENT PRIVATE KEY') }}</label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control" type="text" name="COIN_PAYMENT_PRIVATE_KEY" autocomplete="off"
                            placeholder="" value="{{ $settings['COIN_PAYMENT_PRIVATE_KEY'] ?? '' }}">
                    @endif

                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('COIN PAYMENT IPN MERCHANT ID') }}</label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control" type="text" name="ipn_merchant_id" autocomplete="off"
                            placeholder="" value="{{ $settings['ipn_merchant_id'] ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('COIN PAYMENT IPN SECRET') }}</label>
                    @if (env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{ 'disablefordemo' }}">
                    @else
                        <input class="form-control" type="text" name="ipn_secret" autocomplete="off" placeholder=""
                            value="{{ $settings['ipn_secret'] ?? '' }}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{ __('Withdrawal email verification enable / disable') }}</label>
                    <div class="cp-select-area">
                        <select name="coin_payment_withdrawal_email" class="form-control">
                            <option @if (isset($settings['coin_payment_withdrawal_email']) && $settings['coin_payment_withdrawal_email'] == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}">
                                {{ __('Yes') }}</option>
                            <option @if (isset($settings['coin_payment_withdrawal_email']) && $settings['coin_payment_withdrawal_email'] == STATUS_PENDING) selected @endif value="{{ STATUS_PENDING }}">
                                {{ __('No') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{ __('Update') }}</button>
            </div>
        </div>
    </form>
</div>

<div class="user-management pt-4">
    <div class="row">
        <div class="col-12">
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{ __('CoinPayment Network Records') }}</h3>
                </div>
                <div class="right d-flex align-items-center">
                    <div class="add-btn-new mb-2 mr-1">
                        <button id="sync_fees" class="float-right btn"
                            style="background: #ffba00; color: white">{{ __('Sync form CoinPayment') }}</button>
                    </div>
                </div>
            </div>
            <div class="table-area">

                <table id="withdrawTable" class=" table table-borderless custom-table display text-lg-center"
                    width="100%">
                    <thead>
                        <tr>
                            <th class="all">{{ __('Coin type') }}</th>
                            <th class="desktop">{{ __('BTC rate') }}</th>
                            <th class="desktop">{{ __('Tx rate') }}</th>
                            <th class="desktop">{{ __('Is fiat') }}</th>
                            <th class="desktop">{{ __('status') }}</th>
                            <th class="desktop">{{ __('Last Update') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
