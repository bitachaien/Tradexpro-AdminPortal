@extends('admin.master', ['menu' => 'coin', 'sub_menu' => 'coin_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{ __('Coin') }}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div>
                        {{ Form::open(['route' => 'adminCoinSaveProcess', 'files' => true]) }}

                        <input type="hidden" class="form-control" name="currency_type"
                            value="{{ old('currency_type', @$item->currency_type) }}" />

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Coin Full Name') }}</div>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', @$item->name) }}" />
                                        <pre class="text-danger">{{ $errors->first('name') }}</pre>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Coin Type') }}</div>
                                        <input type="text" class="form-control" name="coin_type"
                                            value="{{ old('coin_type', @$item->coin_type) }}" readonly />
                                    </div>
                                </div>
                            </div>

                            @if ($item->currency_type == CURRENCY_TYPE_CRYPTO)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('Coin API') }}</div>
                                            <select class="form-control" name="network" id="">
                                                @foreach (api_settings_new() as $key => $val)
                                                    <option value="{{ $key }}" {{ @$item->network == $key ? 'selected' : '' }}>
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('Coin Price (in USD)') }}</div>
                                            <div class="input-group mb-3 w-85 ">
                                                <input type="text" class="form-control" name="coin_price"
                                                    value="{{ old('coin_price', @$item->coin_price) }}" />
                                                <div class="input-group-append">
                                                    <span class="input-group-text px-4">
                                                        <span class="currency text-warning">USD</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <small>{{ __('Coin price in USD. it will update by currency api regularly') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Minimum Withdrawal') }}</div>
                                        <input type="text" class="form-control" name="minimum_withdrawal"
                                            value="{{ old('minimum_withdrawal', @$item->minimum_withdrawal ?: 0.00000001) }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Maximum Withdrawal') }}</div>
                                        <input type="text" class="form-control" name="maximum_withdrawal"
                                            value="{{ old('maximum_withdrawal', @$item->maximum_withdrawal ?: 99999999) }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Minimum Buy Amount') }}</div>
                                        <input type="text" class="form-control" name="minimum_buy_amount"
                                            value="{{ old('minimum_buy_amount', @$item->minimum_buy_amount ?: 0.0000001) }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Minimum Sell Amount') }}</div>
                                        <input type="text" class="form-control" name="minimum_sell_amount"
                                            value="{{ old('minimum_sell_amount', @$item->minimum_sell_amount ?: 0.0000001) }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-label">{{ __('Coin Icon') }}</div>
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <span class="btn btn-default btn-file">
                                                <input type="file" name="coin_icon">
                                            </span>
                                        </span>
                                        <img width="150px"
                                            src="{{ empty($item->coin_icon) ? '' : show_image_path($item->coin_icon, 'coin/') }}" />
                                    </div>
                                    <img id='img-upload' />
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label" style="font-size: 20px; font-weight: 700">
                                            <b>{{ __('Withdrawal Setting') }}</b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Withdrawal Fees Type') }}</div>
                                        <select name="withdrawal_fees_type" class="form-control">
                                            @foreach (discount_type() as $wKey => $wVal)
                                                <option value="{{ $wKey }}" {{ old('withdrawal_fees_type', @$item->withdrawal_fees_type) == $wKey ? 'selected' : '' }}>
                                                    {{ $wVal }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Withdrawal Fees') }}</div>
                                        <input type="text" class="form-control" name="withdrawal_fees"
                                            value="{{ old('withdrawal_fees', @$item->withdrawal_fees ?: 0.0000001) }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Withdrawal limit for admin approval') }}
                                        </div>
                                        <input type="text" class="form-control" name="max_send_limit"
                                            value="{{ old('max_send_limit', @$item->max_send_limit ?: 0) }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Enable admin approval ') }}</label>
                                    <div class="cp-select-area">
                                        @php
                                            $adminApprovalArray = [STATUS_ACTIVE => 'Yes', STATUS_REJECTED => 'No'];
                                        @endphp
                                        <select name="admin_approval" class="form-control">
                                            @foreach ($adminApprovalArray as $aKey => $aItem)
                                                <option value="{{ $aKey }}" {{ old('admin_approval', @$item->admin_approval) == $aKey ? 'selected' : '' }}>
                                                    {{ $aItem }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label" style="font-size: 20px; font-weight: 700">
                                            <b>{{ __('Status Setting') }}</b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Deposit Status') }}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="is_deposit" {{ @$item->is_deposit == STATUS_ACTIVE ? 'checked' : '' }} />
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Withdrawal Status') }}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="is_withdrawal" {{ @$item->is_withdrawal == STATUS_ACTIVE ? 'checked' : '' }} />
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Trading Status') }}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="trade_status" {{ @$item->trade_status == STATUS_ACTIVE ? 'checked' : '' }} />
                                            <span class="slider"></span>
                                        </label>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Active Status') }}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status" {{ @$item->status == STATUS_ACTIVE ? 'checked' : '' }} />
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @if (@$module['DemoTrade'])
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('Demo Trade Status') }}</div>
                                            <label class="switch">
                                                <input type="checkbox" name="is_demo_trade" {{ @$item->is_demo_trade == STATUS_ACTIVE ? 'checked' : '' }} />
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                @if (isset($item))
                                    <input type="hidden" name="coin_id" value="{{ encrypt($item->id) }}" />
                                @endif
                                <button type="submit" class="btn theme-btn">{{ $button_title }}</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
@endsection