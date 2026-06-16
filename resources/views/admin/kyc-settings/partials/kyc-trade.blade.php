<div class="user-management pt-4">
    <div class="row">
        <div class="col-12">
            <div class="profile-info-form">
                <form action="{{route('kycTradeSetting')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mt-20">
                            <div class="form-group row">
                                <div class="col-6">
                                    <label>{{ __('KYC enable for Trade') }} </label>
                                    @php
                                        $kyc_trade_setting_status = settings('kyc_trade_setting_status');
                                        $kyc_trade_setting_list = json_decode(settings('kyc_trade_setting_list'));
                                    @endphp
                                    <div class="cp-select-area">
                                        <select name="kyc_trade_setting_status" id="" class="form-control" title="{{ __('select option') }}" data-live-search="true" data-width="100%" data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                            <option value="1" {{isset($kyc_trade_setting_status)?($kyc_trade_setting_status=='1'?'selected':''):''}} >{{__('Yes')}}</option>
                                            <option value="0" {{isset($kyc_trade_setting_status)?($kyc_trade_setting_status=='0'?'selected':''):''}} >{{__('No')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label>{{ __('KYC list') }} </label>
                                    <div class="customSelect">
                                        <select name="kyc_trade_setting_list[]" id="" class="selectpicker" title="{{ __('KYC list') }}" data-live-search="true" data-width="100%" data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4" multiple>
                                            @if($kyc_active_list)
                                                @foreach($kyc_active_list as $kyc)
                                                    <option value="{{ $kyc->id }}" {{isset($kyc_trade_setting_list)?(in_array($kyc->id,$kyc_trade_setting_list)?'selected':''):''}} >{{ $kyc->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-6">
                                    <button type="submit" class="button-primary theme-btn">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>