@php
    $persona_kyc_api_key = settings('PERSONA_KYC_API_KEY');
    $persona_kyc_templated_id = settings('PERSONA_KYC_TEMPLATED_ID');
    $persona_kyc_mode = settings('PERSONA_KYC_MODE');
    $persona_kyc_version = settings('PERSONA_KYC_VERSION');
@endphp
<div class="user-management pt-4">
    <div class="row">
        <div class="col-12">
            <div class="profile-info-form">
                <form action="{{route('kycPersonaSettings')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mt-20">
                            <div class="form-group">
                                <label>{{ __('Persona KYC Api Key') }} </label>
                                @if(env('APP_MODE') == 'demo')
                                    <input class="form-control" value="{{'disablefordemo'}}">
                                @else
                                <input type="text" name="PERSONA_KYC_API_KEY" class="form-control" placeholder="{{__('Persona KYC Api Key')}}"
                                    @if(isset($persona_kyc_api_key)) value="{{$persona_kyc_api_key}}" @else value="{{old('PERSONA_KYC_API_KEY')}}" @endif>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mt-20">
                            <div class="form-group">
                                <label>{{ __('Persona KYC Templated ID') }} </label>
                                @if(env('APP_MODE') == 'demo')
                                    <input class="form-control" value="{{'disablefordemo'}}">
                                @else
                                <input type="text" name="PERSONA_KYC_TEMPLATED_ID" class="form-control" placeholder="{{__('Persona KYC Templated ID')}}"
                                    @if(isset($persona_kyc_templated_id)) value="{{$persona_kyc_templated_id}}" @else value="{{old('PERSONA_KYC_TEMPLATED_ID')}}" @endif>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mt-20">
                            <div class="form-group">
                                <label>{{ __('Persona KYC Mode (sandbox or production)') }} </label>
                                <div class="cp-select-area">
                                    <select name="PERSONA_KYC_MODE" id="" class="form-control" title="{{ __('select option') }}" data-live-search="true" data-width="100%" data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                        <option value="sandbox" {{ isset($persona_kyc_mode) && $persona_kyc_mode == 'sandbox' ? 'selected': ''}} >sandbox</option>
                                        <option value="production" {{ isset($persona_kyc_mode) && $persona_kyc_mode == 'production' ? 'selected': ''}} >production</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-20">
                            <div class="form-group">
                                <label>{{ __('Persona KYC Version') }} </label>
                                @if(env('APP_MODE') == 'demo')
                                    <input class="form-control" value="{{'disablefordemo'}}">
                                @else
                                <input type="text" name="PERSONA_KYC_VERSION" class="form-control" placeholder="{{__('Persona KYC Version')}}"
                                    @if(isset($persona_kyc_version)) value="{{$persona_kyc_version}}" @else value="{{old('PERSONA_KYC_VERSION')}}" @endif>
                                @endif
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