<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Landing Page Settings')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST" action="{{route('adminLandingSettingSave')}}">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="#">{{__('Landing Page Title')}}</label>
                            <input class="form-control" type="text" name="landing_title" @if(isset($adm_setting['landing_title'])) value="{{$adm_setting['landing_title']}}" @endif>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="#">{{__('Landing Feature Title')}}</label>
                            <input class="form-control" type="text" name="landing_feature_title" @if(isset($adm_setting['landing_feature_title'])) value="{{$adm_setting['landing_feature_title']}}" @endif>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="#">{{__('Landing Banner Image')}}</label>
                            <div id="file-upload" class="section-width">
                                <input type="file" placeholder="0.00" name="landing_banner_image" value="" id="file" ref="file"
                                       class="dropify" @if(isset($adm_setting['landing_banner_image'])) data-default-file="{{asset(path_image().$adm_setting['landing_banner_image'])}}"@endif />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="#">{{__('Landing Page Description')}}</label>
                            <textarea class="form-control" rows="5" name="landing_description">@if(isset($adm_setting['landing_description'])){{$adm_setting['landing_description']}} @endif</textarea>
                        </div>
                    </div>
                    
                    
                    <div class="col-md-12">
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
