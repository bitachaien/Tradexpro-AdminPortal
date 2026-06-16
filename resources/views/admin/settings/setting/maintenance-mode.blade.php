<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Maintenance Mode Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSettingsSaveCommon')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Maintenance Mode Status')}}</label>
                    <div class="cp-select-area">
                        <select name="maintenance_mode_status" class="form-control">
                            <option value="0"
                            @if(isset($settings['maintenance_mode_status']) && $settings['maintenance_mode_status']=='0') selected
                                        @endif>{{__('Off')}}</option>
                            <option value="1"
                            @if(isset($settings['maintenance_mode_status']) && $settings['maintenance_mode_status']=='1') selected
                                        @endif>{{__('On')}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Maintenance Mode Title')}}</label>
                    <input class="form-control" type="text" name="maintenance_mode_title"
                           placeholder="{{__('Maintenance Mode Title')}}"
                           value="{{$settings['maintenance_mode_title']}}">
                </div>
            </div>
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Maintenance Mode Text')}}</label>
                    <textarea name="maintenance_mode_text" class="form-control" rows="4">{{$settings['maintenance_mode_text']}}</textarea>
                </div>
            </div>
            <div class="col-lg-6 mt-20">
                <div class="single-uplode">
                    <div class="uplode-catagory">
                        <span>{{__('Logo')}}</span>
                    </div>
                    <div class="form-group buy_coin_address_input ">
                        <div id="file-upload" class="section-p">
                            <input type="file" placeholder="0.00" name="maintenance_mode_img" value=""
                                   id="file" ref="file" class="dropify"
                                   @if(isset($settings['maintenance_mode_img']) && (!empty($settings['maintenance_mode_img'])))  data-default-file="{{asset(path_image().$settings['maintenance_mode_img'])}}" @endif />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            @if(isset($itech))
                <input type="hidden" name="itech" value="{{$itech}}">
            @endif
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
