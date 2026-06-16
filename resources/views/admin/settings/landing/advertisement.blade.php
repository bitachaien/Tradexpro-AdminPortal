<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Landing Page Advertisement Settings')}}</h3>
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
                <div class="form-group">
                    <label for="#">{{__('Advertisement image')}}</label>
                    <div id="file-upload" class="section-width">
                        <input type="file" placeholder="0.00" name="landing_advertisement_image" value="" id="file" ref="file"
                                class="dropify" @if(isset($adm_setting['landing_advertisement_image'])) data-default-file="{{asset(path_image().$adm_setting['landing_advertisement_image'])}}"@endif />
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label for="#">{{__('Advertisement URL')}}</label>
                    <input type="text" placeholder="https://" name="landing_advertisement_url" class="form-control"
                            @if(isset($adm_setting['landing_advertisement_url'])) value="{{ $adm_setting['landing_advertisement_url'] }}"@endif />
                </div>
            </div>
            <div class="col-lg-12">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
