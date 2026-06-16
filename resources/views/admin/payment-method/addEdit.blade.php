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
                        <form action="{{route('currencyPaymentMethodStore')}}" method="post">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="id" value="{{$item->id}}">
                            @endif
                            <input type="hidden" name="type" value="fiat-deposit">
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="title">{{__('Title')}}</label>
                                        <input type="text" name="title" class="form-control" id="title" placeholder="{{__('Title')}}"
                                               @if(isset($item)) value="{{$item->title}}" @else value="{{old('account_holder_name')}}" @endif>
                                        <span class="text-danger"><strong>{{ $errors->first('title') }}</strong></span>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <label>{{ __('Select Payment Method') }} </label>
                                    <div class="cp-select-area">
                                        <select name="payment_method_id" class="form-control">
                                        @if(isset($payment_methods))
                                            @foreach($payment_methods as $key=>$payment_method)
                                                <option value="{{$key}}"
                                                @if(isset($item)) {{$key == $item->payment_method ? 'selected' :' '}}
                                                @endif >{{$payment_method}} </option>
                                            @endforeach
                                        @endif
                                        </select>
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
