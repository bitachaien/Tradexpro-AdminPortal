<div class="user-management pt-4">
    <div class="row">
        <div class="col-12">
            <div class="profile-info-form">
                <form action="{{route('kycSettings')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mt-20">
                            <div class="form-group row">
                                <div class="col-6">
                                    <label>{{ __('Enable/Disable KYC For') }} </label>
                                    @php
                                        $kyc_type_is = settings('kyc_type_is');
                                    @endphp
                                    <div class="cp-select-area">
                                        <select name="kyc_type_is" id="" class="form-control" title="{{ __('select option') }}" data-live-search="true" data-width="100%" data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                            @foreach (kycTypeList() as $kyc_key=>$kyc_item)
                                                <option value="{{$kyc_key}}" {{isset($kyc_type_is)?($kyc_type_is==$kyc_key?'selected':''):''}} >{{ $kyc_item}}</option>
                                            @endforeach
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