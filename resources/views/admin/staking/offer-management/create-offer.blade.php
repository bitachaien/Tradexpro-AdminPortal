@extends('admin.staking.layouts.master',['menu'=>'staking_offer', 'sub_menu'=>'create'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
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
                    <div class="card-body">
                        <form action="{{route('stakingStoreOffer')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Select Coin')}}</label>
                                        <div class="cp-select-area customSelect ">
                                            <select name="coin_type" class="selectpicker" 
                                                title="{{ __('Select Coin') }}" data-live-search="true" data-width="100%"
                                                data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                @if(isset($coin_list))
                                                    @foreach($coin_list as $key=>$coin)
                                                        <option value="{{$coin->coin_type}}"
                                                            {{old('coin_type') == $coin->coin_type?'selected':''}}>
                                                            {{$coin->name}} 
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Period')}} ({{__('Days')}})</label>
                                        <input type="text" name="period" class="form-control" 
                                            placeholder="{{__('Enter period in days')}}"
                                            value="{{old('period')}}">
                                        
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Percentage Amount')}}</label>
                                        <input type="text" name="offer_percentage" class="form-control"
                                            placeholder="{{__('Enter Offer Percentage')}}"
                                            value="{{old('offer_percentage')}}">
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Minimum Investment')}}</label>
                                        <input type="text" name="minimum_investment" class="form-control"
                                            placeholder="{{__('Minimum Investment amount')}}"
                                            value="{{old('minimum_investment')}}">
                                        
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Maximum Investment')}}</label>
                                        <input type="text" name="maximum_investment" class="form-control"
                                            placeholder="{{__('Maximum Investment amount')}}"
                                            value="{{old('maximum_investment')}}">
                                        
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Terms Type')}}</label>
                                        <div class="cp-select-area customSelect ">
                                            <select name="terms_type" class="selectpicker" 
                                                title="{{ __('Select Coin') }}" data-live-search="true" data-width="100%"
                                                data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                
                                                @foreach(getTermsTypeListStaking() as $terms_key=>$terms_value)
                                                    <option value="{{$terms_key}}"
                                                    {{old('terms_type') == $terms_key?'selected':''}}>
                                                        {{$terms_value}} 
                                                    </option>
                                                @endforeach
                                                
                                            </select>
                                        </div>
                                        
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Minimum Maturity Period')}} ({{__('Days')}})</label>
                                        <input type="text" name="minimum_maturity_period" class="form-control"
                                            placeholder="{{__('Minimum Maturity Period in days')}}"
                                            value="{{old('minimum_maturity_period')}}">
                                        
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('User Registration Before')}} ({{__('Days')}}) ({{__('Optional')}})</label>
                                        <input type="text" name="registration_before" class="form-control"
                                            placeholder="{{__('User Registration Before in days')}}"
                                            value="{{old('registration_before')}}">
                                        
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Phone Verification')}} ({{__('Optional')}})</label>
                                        <div class="cp-select-area customSelect ">
                                            <select name="phone_verification" class="selectpicker" 
                                                title="{{ __('Select Status') }}" data-live-search="true" data-width="100%"
                                                data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                <option value="{{STATUS_INACTIVE}}"
                                                    {{old('phone_verification') == STATUS_INACTIVE?'selected':''}}>
                                                    {{__('Inactive')}} 
                                                </option>
                                                <option value="{{STATUS_ACTIVE}}" 
                                                    {{old('phone_verification') == STATUS_ACTIVE?'selected':''}}>
                                                    {{__('Active')}} 
                                                </option>
                                            </select>
                                        </div>
                                        
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('KYC Verification')}} ({{__('Optional')}})</label>
                                        <div class="cp-select-area customSelect ">
                                            <select name="kyc_verification" class="selectpicker" 
                                                title="{{ __('Select Status') }}" data-live-search="true" data-width="100%"
                                                data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                <option value="{{STATUS_INACTIVE}}"
                                                    {{old('kyc_verification') == STATUS_INACTIVE?'selected':''}}>
                                                    {{__('Inactive')}} 
                                                </option>
                                                <option value="{{STATUS_ACTIVE}}" 
                                                    {{old('kyc_verification') == STATUS_ACTIVE?'selected':''}}>
                                                    {{__('Active')}} 
                                                </option>
                                            </select>
                                        </div>
                                        
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('User Minimum Holding Amount')}} ({{__('Days')}}) ({{__('Optional')}})</label>
                                        <input type="text" name="user_minimum_holding_amount" class="form-control"
                                            placeholder="{{__('User Minimum Holding Amount')}}"
                                            value="{{old('user_minimum_holding_amount')}}">
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Status')}}</label>
                                        <div class="cp-select-area customSelect ">
                                            <select name="status" class="selectpicker" 
                                                title="{{ __('Select Status') }}" data-live-search="true" data-width="100%"
                                                data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                <option value="{{STATUS_INACTIVE}}"
                                                    {{old('status') == STATUS_INACTIVE?'selected':''}}>
                                                    {{__('Inactive')}} 
                                                </option>
                                                <option value="{{STATUS_ACTIVE}}" 
                                                    {{old('status') == STATUS_ACTIVE?'selected':''}}>
                                                    {{__('Active')}} 
                                                </option>
                                            </select>
                                        </div>
                                        
                                    </div>
                                </div>

                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Terms And Condition')}}</label>
                                        <textarea rows="6" name="body" id="editor" class="form-control-new textarea note-editable" >{{old('body')}}</textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(isset($item)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
<script>
    //text editor
    (function($) {
        "use strict";
        var $summernote = $('#editor');
            var isCodeView;

            $(() => {
                $summernote.summernote({
                    height: 500,
                    focus: true,
                    codeviewFilter: false,
                    codeviewFilterRegex: /<\/*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|ilayer|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|t(?:itle|extarea)|xml)[^>]*?>/gi,
                });
            });

            $summernote.on('summernote.codeview.toggled', () => {
                isCodeView = $('.note-editor').hasClass('codeview');
            });

        })(jQuery);

</script>
@endsection
