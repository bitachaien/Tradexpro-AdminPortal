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
                        <form action="{{route('stakingLandingSettingsUpdate')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Title')}}</label>
                                        <input type="text" name="staking_landing_title" class="form-control" 
                                            placeholder="{{__('Enter staking landing title')}}"
                                            value="{{ isset($settings['staking_landing_title'])? $settings['staking_landing_title'] : ''}}">
                                        
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label>{{__('Descrription')}}</label>
                                        <textarea name="staking_landing_description" rows="3" class="form-control">{{ isset($settings['staking_landing_description'])? $settings['staking_landing_description'] : ''}}</textarea>
                                        
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="single-uplode">
                                        <div class="uplode-catagory">
                                            <span>{{__('Cover Image')}}</span>
                                        </div>
                                        <div class="form-group buy_coin_address_input ">
                                            <div id="file-upload" class="section-p">
                                                <input type="file" name="staking_landing_cover_image" value=""
                                                       id="file" ref="file" class="dropify"
                                                       @if(isset($settings['staking_landing_cover_image']) && (!empty($settings['staking_landing_cover_image'])))  data-default-file="{{asset(path_image().$settings['staking_landing_cover_image'])}}" @endif />
                                            </div>
                                        </div>
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
