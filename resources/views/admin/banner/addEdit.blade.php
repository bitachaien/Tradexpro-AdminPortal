@extends('admin.master',['menu'=>'landing_setting', 'sub_menu'=>'banner'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('Landing Banner')}}</li>
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
                    <form id="edit" action="{{route('adminBannerSave')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-xl-4 mb-xl-0 mb-4">
                                <div class="form-group">
                                    <label>{{__('Banner Title')}}</label>
                                    <input type="text" name="title" class="form-control" @if(isset($item)) value="{{$item->title}}" @else value="{{old('title')}}" @endif>
                                </div>
                                <div class="form-group">
                                    <label>{{__('Activation Status')}}</label>
                                    <div class="cp-select-area">
                                        <select name="status" class="form-control wide" >
                                            @foreach(status() as $key => $value)
                                                <option @if(isset($item) && ($item->status == $key)) selected
                                                        @elseif((old('status') != null) && (old('status') == $key)) @endif value="{{ $key }}">{!! $value !!}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="#">{{__('Banner Image')}}</label>
                                            <div id="file-upload" class="section-width">
                                                <input type="file" placeholder="0.00" name="image"
                                                       value="" id="file" ref="file" class="dropify"
                                                       @if(isset($item) && (!empty($item->image))) data-default-file="{{asset(path_image().$item->image)}}" @endif />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8 mb-xl-0 mb-4">
                                <div class="form-group">
                                    <label>{{__('Details')}}</label>
                                    <input type="hidden" id="body" name="body" value="" />
                                    <textarea rows="5" id="editor" class="form-control textarea note-editable" >@if(isset($item)){!! clean($item->description) !!}@else{{old('details')}}@endif</textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    @if(isset($item))
                                        <input type="hidden" name="edit_id" value="{{$item->id}}">
                                    @endif
                                    <button type="submit" class="button-primary theme-btn">{{ $button_title }}</button>
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
    <script>
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

            $("#edit").submit( (event) => {

                var body = $summernote.summernote('code');
                document.getElementById('body').setAttribute('value', body);

            });
        })(jQuery);
    </script>
@endsection
