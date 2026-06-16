@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
    @include('admin.gift_card.sidebar.sidebar', ['menu' => 'banner'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ __('Create Banner') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management user-chart card">
        <div class="row">
            <div class="card-body">
                <form action="{{ route('giftCardBannerSave') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-xl-0 mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Title') }}</label>
                                    <input type="text" name="title" class="form-control"
                                        @if (isset($banner)) value="{{ $banner->title }}" @else value="{{ old('title') }}" @endif>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Sub Title') }}</label>
                                    <input type="text" name="sub_title" class="form-control"
                                        @if (isset($banner)) value="{{ $banner->sub_title }}" @else value="{{ old('sub_title') }}" @endif>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Category') }}</label>
                                    <div class="cp-select-area">
                                        <select name="category_id" class="form-control wide">
                                            <option value="null">{{ __('Select Category') }}</option>
                                            @foreach ($categorys as $category)
                                                <option @if (isset($banner) && $banner->category_id == $category->uid) selected @endif
                                                    value="{{ $category->uid }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Status') }}</label>
                                    <div class="cp-select-area">
                                        <select name="status" class="form-control wide">
                                            @foreach (status() as $key => $value)
                                                <option
                                                    @if (isset($banner) && $banner->status == $key) selected
                                                    @elseif(old('status') != null && old('status') == $key) @endif
                                                    value="{{ $key }}">{!! $value !!}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 mb-xl-0 mb-4">
                        <div class="form-group">
                            <label>{{ __('Banner') }}</label>
                            <input type="file" name="banner" class="dropify" id=""
                                @if (isset($banner) && !empty($banner->banner)) data-default-file="{{ asset(GIFT_CARD_BANNER . $banner->banner) }}" @endif>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            @if (isset($banner))
                                <input type="hidden" name="uid" value="{{ $banner->uid }}">
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
