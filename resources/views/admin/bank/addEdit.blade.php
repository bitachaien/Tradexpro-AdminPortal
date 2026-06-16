@extends('admin.master',['menu'=>'currency_deposit', 'sub_menu'=>'currency_list'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Fiat Deposit')}}</li>
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
                        <form action="{{route('bankStore')}}" method="post">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="id" value="{{$item->id}}">
                            @endif
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Account Holder Name')}}</label>
                                        <input type="text" name="account_holder_name" class="form-control" id="account_holder_name" placeholder="{{__('Account Holder Name')}}"
                                               @if(isset($item)) value="{{$item->account_holder_name}}" @else value="{{old('account_holder_name')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('account_holder_address') }}</strong></span>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_address">{{__('Account Holder Address')}}</label>
                                        <input type="text" name="account_holder_address" class="form-control" id="account_holder_address" placeholder="{{__('Account Holder Address')}}"
                                               @if(isset($item)) value="{{$item->account_holder_address}}" @else value="{{old('account_holder_address')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('account_holder_address') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="bank_name">{{__('Bank Name')}}</label>
                                        <input type="text" name="bank_name" class="form-control" id="bank_name" placeholder="{{__('Bank Name')}}"
                                               @if(isset($item)) value="{{$item->bank_name}}" @else value="{{old('bank_name')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('bank_name') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="bank_address">{{__('Bank Address')}}</label>
                                        <input type="text" name="bank_address" class="form-control" id="bank_address" placeholder="{{__('Bank Address')}}"
                                               @if(isset($item)) value="{{$item->bank_address}}" @else value="{{old('bank_address')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('bank_address') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <label for="country">{{__('Country')}}</label>
                                    <div class="cp-select-area customSelect ">
                                        <select name="country_code" id="country_code" class="selectpicker" title="{{ __('Select Country') }}" data-live-search="true" data-width="100%"
                                            data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                        @if(isset($countries))
                                            @foreach($countries as $key=>$country)
                                                <option value="{{$country->key}}"
                                                @if(isset($item)) {{$country->key == $item->country ? 'selected' :' '}}
                                                @endif >{{$country->value}} </option>
                                            @endforeach
                                        @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="swift_code">{{__('Swift Code')}}</label>
                                        <input type="text" name="swift_code" class="form-control" id="swift_code" placeholder="{{__('Swift Code')}}"
                                               @if(isset($item)) value="{{$item->swift_code}}" @else value="{{old('swift_code')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('swift_code') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="iban">{{__('Iban')}}</label>
                                        <input type="text" name="iban" class="form-control" id="iban" placeholder="{{__('Iban')}}"
                                               @if(isset($item)) value="{{$item->iban}}" @else value="{{old('iban')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('iban') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="note">{{__('Note')}}</label>
                                        <input type="text" name="note" class="form-control" id="note" placeholder="{{__('Note')}}"
                                               @if(isset($item)) value="{{$item->note}}" @else value="{{old('note')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('note') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label class="switch" style="width: 150px;height: 42px;">
                                            <input {{ isset($item) && $item->status ? 'checked' : ''}} type="checkbox" name="status">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                                @if(isset($item))
                                    <input type="hidden" name="id" value="{{$item->id}}">
                                @endif
                                <div class="col-md-12">
                                    @if(isset($item))
                                        <input type="hidden" name="edit_id" value="{{$item->id}}">
                                    @endif
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

@endsection
