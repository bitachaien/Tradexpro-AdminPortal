@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
    @include('admin.gift_card.sidebar.sidebar', ['menu' => 'category'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ __('Create Category') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management user-chart card">
        <div class="row">
            <div class="card-body">
                <form action="{{ route('giftCardCategorySave') }}" method="post">
                    @csrf
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control"
                                        @if (isset($category)) value="{{ $category->name }}" @else value="{{ old('name') }}" @endif>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Status') }}</label>
                                    <div class="cp-select-area">
                                        <select name="status" class="form-control wide">
                                            @foreach (status() as $key => $value)
                                                <option
                                                    @if (isset($category) && $category->status == $key) selected
                                                        @elseif(old('status') != null && old('status') == $key) @endif
                                                    value="{{ $key }}">{!! $value !!}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            @if (isset($category))
                                <input type="hidden" name="uid" value="{{ $category->uid }}">
                            @endif
                            <button type="submit" class="button-primary theme-btn">{{ __('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /User Management -->


@endsection
@section('script')

    <script>
        (function($) {
            "use strict";


        })(jQuery);
    </script>


@endsection
