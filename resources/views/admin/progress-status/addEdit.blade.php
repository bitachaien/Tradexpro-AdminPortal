@extends('admin.master',['menu'=>'faq', 'sub_menu'=>'faq'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management add-custom-page">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <h3>{{$title}}</h3>
                    </div>
                </div>
                <div class="profile-info-form">
                    <form action="{{route('progressStatusSave')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-xl-6 mb-xl-0 mb-4">
                                <div class="form-group">
                                    <label>{{__('Title')}}</label>
                                    <input type="text" name="title" class="form-control" @if(isset($item)) value="{{$item->title}}" @else value="{{old('title')}}" @endif>
                                </div>
                                <div class="form-group">
                                    <label>{{__('Progress Status Type')}}</label>
                                    <select name="progress_type_id" class="form-control wide" >
                                        @foreach(progressStatusType() as $key => $value)
                                            <option @if(isset($item) && ($item->progress_type_id == $key)) selected
                                                    @elseif((old('progress_type_id') != null) && (old('progress_type_id') == $key)) @endif value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{__('Activation Status')}}</label>
                                    <select name="status" class="form-control wide" >
                                        @foreach(status() as $key => $value)
                                            <option @if(isset($item) && ($item->status == $key)) selected
                                                    @elseif((old('status') != null) && (old('status') == $key)) @endif value="{{ $key }}">{!! $value !!}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-xl-0 mb-4">
                                <div class="form-group">
                                    <label>{{__('Description')}}</label>
                                    <textarea class="form-control textarea" name="description">@if(isset($item)){{$item->description}}@else{{old('description')}}@endif</textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    @if(isset($item))
                                        <input type="hidden" name="edit_id" value="{{$item->id}}">
                                    @endif
                                    <button type="submit" class="button-primary theme-btn">{{__('Save')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection
@section('script')
@endsection
