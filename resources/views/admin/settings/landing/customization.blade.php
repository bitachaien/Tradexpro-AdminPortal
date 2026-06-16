<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Landing Page Customization Settings')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST"
          action="{{route('adminLandingSettingSave')}}">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="#">{{__('Know More Button Title')}}</label>
                            <input type="text" class="form-control" name="know_more_button_title"
                                    @if(isset($adm_setting['know_more_button_title']))value="{{$adm_setting['know_more_button_title']}}" @endif>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Know More Link')}}</label>
                            <input type="text" class="form-control" name="know_more_link"
                                    @if(isset($adm_setting['know_more_link']))value="{{$adm_setting['know_more_link']}}" @endif>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Customization title')}}</label>
                            <input type="text" class="form-control" name="customization_title" @if(isset($adm_setting['customization_title'])) value="{{$adm_setting['customization_title']}}" @endif>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Customization details')}}</label>
                            <textarea class="form-control" rows="5" name="customization_details">@if(isset($adm_setting['customization_details'])){{$adm_setting['customization_details']}} @endif</textarea>
                        </div>

                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
