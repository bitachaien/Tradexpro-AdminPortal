<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Check coin pair rate from outside market')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('checkOutsideMarketRate')}}" method="post" >
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Coin Pair')}}</label>

                    <div class="customSelect rounded">
                        <select name="coin_pair"  class=" selectpicker bg-dark w-100" data-width="100%" data-live-search="true" data-actions-box="true" data-selected-text-format="count > 4" >
                            <option value="">{{ __("Select pair") }}</option>
                            @if (isset($coin_pairs[0]))
                                @foreach ($coin_pairs as $coin)
                                    <option @if($coin->pair_bin === old('coin_pair')) selected @endif value="{{ $coin->pair_bin.'#'.$coin->id }}">{{ $coin->pair_bin }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <p class="text-danger">{{ __('If we get rate successfully, then bot order from outside is possible, so that you can make it "Bot order possible from outside market"') }}</p>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-4 col-12 mt-20">
                <button type="submit" class="btn btn-danger ">{{__('Check Outside Market Rate')}}</button>
            </div>
        </div>
    </form>
</div>
<hr>
<div class="header-bar mt-5">
    <div class="table-title">
        <h3>{{__('Delete individual coin pair chart data')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('deleteCoinPairChartData')}}" method="post" >
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Coin Pair')}}</label>

                    <div class="customSelect rounded">
                        <select name="pair_id" class=" selectpicker bg-dark w-100" data-width="100%" data-live-search="true" data-actions-box="true" data-selected-text-format="count > 4" >
                            <option value="">{{ __("Select pair") }}</option>
                            @if (isset($coin_pairs[0]))
                                @foreach ($coin_pairs as $coin)
                                    <option @if($coin->id == old('pair_id')) selected @endif value="{{ $coin->id }}">{{ $coin->pair_bin }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <p class="text-danger">{{ __('Alert! . From here you can delete selected coin pair chart data, then from coin pairs section you can again generate chart data') }}</p>
                </div>
                <div class="form-group">
                    <label for="#">{{__('Admin Password')}}</label>
                    <input class="form-control" id="password" type="password" name="password" />
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="btn btn-danger ">{{__('Delete chart data')}}</button>
            </div>
        </div>
    </form>
</div>

<hr>
<div class="header-bar mt-5">
    <div class="table-title">
        <h3>{{__('Delete individual coin pair order & transaction data (Only bot order and bot to bot transactions)')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('deleteCoinPairOrderData')}}" method="post" >
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Coin Pair')}}</label>

                    <div class="customSelect rounded">
                        <select name="pair_id" class=" selectpicker bg-dark w-100" data-width="100%" data-live-search="true" data-actions-box="true" data-selected-text-format="count > 4" >
                            <option value="">{{ __("Select pair") }}</option>
                            @if (isset($coin_pairs[0]))
                                @foreach ($coin_pairs as $coin)
                                    <option @if($coin->id == old('pair_id')) selected @endif value="{{ $coin->id }}">{{ $coin->pair_bin }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <p class="text-danger">{{ __('Alert! . From here you can delete bot orders and bot to bot transactions for selected coin pair. Transaction data between user and bot will remain unaffected.') }}</p>
                </div>
                <div class="form-group">
                    <label for="#">{{__('Admin Password')}}</label>
                    <input class="form-control" id="password" type="password" name="password" />
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="btn btn-danger ">{{__('Delete order data')}}</button>
            </div>
        </div>
    </form>
</div>

<hr>
<div class="header-bar mt-5">
    <div class="table-title">
        <h3>{{__('Update coin pair is token or native ')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('updatePairWithToken')}}" method="post" >
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Coin Pair')}}</label>

                    <div class="customSelect rounded">
                        <select name="pair_id" class=" selectpicker bg-dark w-100" data-width="100%" data-live-search="true" data-actions-box="true" data-selected-text-format="count > 4" >
                            <option value="">{{ __("Select pair") }}</option>
                            @if (isset($coin_pairs[0]))
                                @foreach ($coin_pairs as $coin)
                                    <option @if($coin->id == old('pair_id')) selected @endif value="{{ $coin->id }}">{{ $coin->pair_bin }} ({{ $coin->is_token == STATUS_ACTIVE ? __('Token Pair') : __('Native Pair') }})</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <p class="text-danger">{{ __('Alert! . From here you can change coin pair token or native pair option') }}</p>
                </div>
                <div class="form-group">
                    <label for="#">{{__('Set token or native pair')}}</label>

                    <div class="cp-select-area">
                        <select name="is_token" class="form-control" data-width="100%">
                            <option value="">{{ __("Select") }}</option>
                            <option value="{{STATUS_ACTIVE}}">{{__("Token Pair")}}</option>
                            <option value="{{STATUS_REJECTED}}">{{__("Native Pair")}}</option>
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <label for="#">{{__('Admin Password')}}</label>
                    <input class="form-control" id="password" type="password" name="password" />
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="btn btn-danger ">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
