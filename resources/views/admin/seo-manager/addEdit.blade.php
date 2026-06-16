@extends('admin.master',['menu'=>'setting', 'sub_menu'=>'seo_manager'])
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
                        <form action="{{route('seoManagerUpdate')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-lg-12 mt-20">
                                            <div class="single-uplode">
                                                <div class="uplode-catagory">
                                                    <span>{{__('Upload Image for')}} {{isset($kycDetails)?$kycDetails->name: ''}}</span>
                                                </div>
                                                <div class="form-group buy_coin_address_input ">
                                                    <div id="file-upload" class="section-p">
                                                        <input type="file" placeholder="0.00" name="image" value=""
                                                            id="file" ref="file" class="dropify"
                                                            @if(isset($seo_image) && (!empty($seo_image)))  data-default-file="{{asset(path_image().$seo_image)}}" @endif />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="meta_keywords">{{__('Meta Keywords')}} </label>
                                                <input type="text" data-role="tagsinput" name="meta_keywords" class="form-control"
                                                @if(!empty($seo_meta_keywords)) value="{{$seo_meta_keywords}}" @else value="{{old('meta_keywords')}}" @endif>
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="google_analytics_tracking_id">{{__('Meta Description')}}</label>
                                                <textarea class="form-control" name="meta_description" id="" rows="1">@if(!empty($seo_meta_description)) {{$seo_meta_description}} @else {{old('meta_description')}} @endif</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="google_analytics_tracking_id">{{__('Social Title')}}</label>
                                                <input type="text" name="social_title" class="form-control"
                                                @if(!empty($seo_social_title)) value="{{$seo_social_title}}" @else value="{{old('social_title')}}" @endif>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="google_analytics_tracking_id">{{__('Social Description')}}</label>
                                                <textarea class="form-control" name="social_description" id="" rows="1">@if(!empty($seo_social_description)) {{$seo_social_description}} @else {{old('social_description')}} @endif</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(!empty($seo_meta_keywords)) {{__('Update')}} @else {{__('Save')}} @endif</button>
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
        $(document).ready(function() {

            // $('#tag_input').tagsinput('add', 'some tag');

});

    </script>
@endsection
