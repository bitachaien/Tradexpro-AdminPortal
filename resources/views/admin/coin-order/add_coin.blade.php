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
                        {{ Form::open(['route' => 'adminSaveCoin', 'files' => true]) }}
                        {{-- <div class="row"> --}}

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <div class="controls">
                                    <div class="form-label">{{ __('Currency Type') }}</div>
                                    <select class="form-control" name="currency_type" id="currency_type">
                                        @foreach (getTradeCurrencyType() as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ old('currency_type') == $key ? 'selected' : '' }}>{{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <div class="controls">
                                    <div class="form-label">{{ __('Coin Full Name') }}</div>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                    <pre class="text-danger">{{ $errors->first('name') }}</pre>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <div class="controls">
                                    <div class="form-label">{{ __('Coin Type') }}</div>
                                    <div class="d-none" id="coin_type_input">
                                        <input id="coin_type_input_" type="text" class="form-control"
                                            value="{{ old('coin_type') }}">
                                    </div>
                                    <div class="" id="coin_type_select">
                                        <select id="coin_type_select_" class="form-control">
                                            <option value="">{{ __('Select') }}</option>
                                            @foreach ($currency as $currency)
                                                <option value="{{ $currency->code }}" data-price="{{ $currency->rate }}"
                                                    {{ old('currency_type') == $key ? 'selected' : '' }}>
                                                    {{ $currency->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <small>{{ __('Please make sure your coin type is right. Never input wrong coin type') }}</small>
                                    <pre class="text-danger">{{ $errors->first('coin_type') }}</pre>
                                </div>
                            </div>
                        </div>

                        <div id="coin_rate_api" class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="form-label">{{ __('Get coin rate from api ?') }}</label>
                                <div class="cp-select-area">
                                    @php
                                        $isGetPriceArray = [STATUS_ACTIVE => 'Yes', STATUS_REJECTED => 'No'];
                                    @endphp
                                    <select name="get_price_api" id="" class="form-control">
                                        @foreach ($isGetPriceArray as $sKey => $sItem)
                                            <option value="{{ $sKey }}"
                                                {{ old('get_price_api') == $sKey ? 'selected' : '' }}>{{ $sItem }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-warning">{{ __('If no , please input the coin price') }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <div class="controls">
                                    <div class="form-label">{{ __('Coin Price (in USD)') }}</div>
                                    <div class="input-group w-85 ">
                                        <input type="text" class="form-control" name="coin_price"
                                            value="{{ old('coin_price') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text px-4"><span
                                                    class="currency text-warning">USD</span></span>
                                        </div>
                                    </div>
                                    <small>{{ __('Coin price in USD. it will update by currency api regularly') }}</small>
                                    <pre class="text-danger">{{ $errors->first('coin_price') }}</pre>
                                </div>
                            </div>
                        </div>

                        <div id="coin_api" class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <div class="controls">
                                    <div class="form-label">{{ __('Coin API') }}</div>
                                    <div class="cp-select-area">
                                        <select name="network" id="" class="form-control">
                                            @foreach (api_settings_new() as $nKey => $nItem)
                                                <option value="{{ $nKey }}"
                                                    {{ old('network') == $nKey ? 'selected' : '' }}>
                                                    {{ $nItem }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <small>{{ __('Please make sure your coin API is right.You never change this API. So be careful') }}</small>
                                    <pre class="text-danger">{{ $errors->first('network') }}</pre>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn theme-btn">{{ $button_title }}</button>
                        </div>
                        {{-- </div> --}}

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection
@section('script')
    <script>
        "use strict";
        let currency = 1;

        function add_coin_ui_change() { // name="coin_type"
            let coin_type_input = document.getElementById("coin_type_input");
            let coin_type_select = document.getElementById("coin_type_select");


            if (currency == 1) {
                $("#coin_type_input_").attr("name", "coin_type");
                $("#coin_type_select_").attr("name", "");

                coin_type_select.classList.add("d-none");
                coin_type_input.classList.remove("d-none");
                $("#coin_api").show()
                $("#coin_rate_api").show()
            }

            if (currency == 2) {
                $("#coin_type_input_").attr("name", "")
                $("#coin_type_select_").attr("name", "coin_type")

                coin_type_select.classList.remove("d-none");
                coin_type_input.classList.add("d-none");

                $("#coin_api").hide();
                $("#coin_rate_api").hide();
            }
        }

        function currency_change(event) {
            currency = event.target.value;
            add_coin_ui_change();
        }
        add_coin_ui_change();
        $("#currency_type").on("change", currency_change);
        $("#coin_type_select_").on("change", (e) => {
            let rate = $("#coin_type_select_").find(':selected').data("price")
            $('input[name="coin_price"]').val((1 / rate).toFixed(8));
        });
    </script>
@endsection
