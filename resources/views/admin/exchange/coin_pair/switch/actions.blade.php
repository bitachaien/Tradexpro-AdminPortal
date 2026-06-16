<ul class="d-flex activity-menu">
    <li class="viewuser">
        <a data-toggle="modal" data-target="#pair_edit_{{ $item->id }}"
            title="{{ __('Edit') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-pencil"></i>
        </a>
    </li>
    <li class="viewuser">
        <a href="#delete1WV4d6uF6Ytu18v1Pl_{{ $item->id }}"
            data-toggle="modal" title="{{ __('Delete') }}"
            class="btn btn-danger btn-sm">
            <i class="fa fa-trash"></i>
        </a>
        <div id="delete1WV4d6uF6Ytu18v1Pl_{{ $item->id }}"
            class="modal fade delete" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">{{ __('Delete') }}</h6>
                        <button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('Do you want to delete ?') }}</p>
                    </div>
                    <div class="modal-footer"><button type="button"
                            class="btn btn-default"
                            data-dismiss="modal">{{ __('Close') }}</button>
                        <a
                            class="btn btn-danger"href="{{ route('coinPairsDelete', encrypt($item->id)) }}">{{ __('Confirm') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </li>
    @if ($item->is_chart_updated == STATUS_PENDING)
        <li class="viewuser">
            <a href="#chart1WV4d6uF6Ytu18v1Pl_{{ $item->id }}"
                data-toggle="modal" title="{{ __('Update Chart Data') }}"
                class="btn btn-success btn-sm">
                <i class="fa fa-bar-chart"></i>
            </a>
            <div id="chart1WV4d6uF6Ytu18v1Pl_{{ $item->id }}"
                class="modal fade delete" role="dialog">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title">
                                {{ __('Update Chart Data') }}</h6><button
                                type="button" class="close"
                                data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>{{ __('Do you want to get chart data from api ?') }}
                            </p>
                        </div>
                        <div class="modal-footer"><button type="button"
                                class="btn btn-default"
                                data-dismiss="modal">{{ __('Close') }}</button>
                            <a
                                class="btn btn-danger"href="{{ route('coinPairsChartUpdate', encrypt($item->id)) }}">{{ __('Confirm') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    @endif
    <li class="viewuser">
        <a href="{{ route('coinPairFutureSetting', encrypt($item->id)) }}"
            title="{{ __('Settings') }}" class="btn btn-warning btn-sm">
            <i class="fa fa-cog"></i>
        </a>
    </li>
</ul>
<div id="pair_edit_{{ $item->id }}" class="modal fade"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Update Coin Pair') }}</h4>
                <button type="button" class="close"
                    data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => 'saveCoinPairSettings', 'files' => 'true']) }}
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" name="edit_id"
                            value="{{ encrypt($item->id) }}">
                        <div class="form-group text-left">
                            <label
                                class="form-label">{{ __('Base Coin') }}</label>
                            <select class=" form-control"
                                name="parent_coin_id"
                                style="width: 100%;">
                                <option value="">{{ __('Select') }}
                                </option>
                                @if (isset($coins[0]))
                                    @foreach ($coins as $coin)
                                        <option
                                            @if ($item->parent_coin_id == $coin->id) selected @endif
                                            value="{{ $coin->id }}">
                                            {{ check_default_coin_type($coin->coin_type) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group text-left">
                            <label
                                class="form-label">{{ __('Pair Coin') }}</label>
                            <select class=" form-control"
                                name="child_coin_id" style="width: 100%;">
                                <option value="">{{ __('Select') }}
                                </option>
                                @if (isset($coins[0]))
                                    @foreach ($coins as $coin)
                                        <option
                                            @if ($item->child_coin_id == $coin->id) selected @endif
                                            value="{{ $coin->id }}">
                                            {{ check_default_coin_type($coin->coin_type) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group text-left">
                            <label
                                class="form-label">{{ __('Is this pair listed in bot api ?') }}</label>
                            <select class=" form-control"
                                name="pair_listed_api"
                                style="width: 100%;">
                                <option
                                    @if ($item->is_token == STATUS_ACTIVE) selected @endif
                                    value="2">{{ __('No') }}
                                </option>
                                <option
                                    @if ($item->is_token == STATUS_INACTIVE) selected @endif
                                    value="1">{{ __('Yes') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group text-left">
                            <label
                                class="form-label">{{ __('Digits after Decimal point') }}</label>

                            <select class="form-control"
                                name="pair_decimal" style="width: 100%;">
                                @foreach (range(2, 8) as $v)
                                    <option
                                        @if ($item->pair_decimal == $v) selected @endif
                                        value="{{ $v }}">
                                        {{ $v }}</option>
                                @endforeach
                            </select>
                            <p class="text-secondary sm-text">
                                {{ __('Select the number of digits after decimal point.') }}
                            </p>
                        </div>
                    </div>
                    @if (env('APP_MODE') == 'myDemo')
                        <div class="col-md-6">
                            <div class="form-group text-left">
                                <label
                                    class="form-label">{{ __('Last Price') }}</label>
                                <input type="text" class="form-control"
                                    name="price"
                                    value="{{ $item->price }}">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <button class="btn btn-info" style="width: 100%"
                            type="submit">{{ __('Update') }}</button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
            {{-- <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('Close')}}</button>
        </div> --}}
        </div>
    </div>
</div>
