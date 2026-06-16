<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Section Settings')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST"
          action="{{route('adminLandingSectionSettingsSave')}}">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="#">{{__('Landing First Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_first_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_first_section_status']) && $adm_setting['landing_first_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_first_section_status']) && $adm_setting['landing_first_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Landing Second Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_second_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_second_section_status']) && $adm_setting['landing_second_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_second_section_status']) && $adm_setting['landing_second_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Landing Third Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_third_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_third_section_status']) && $adm_setting['landing_third_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_third_section_status']) && $adm_setting['landing_third_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Landing Fourth Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_fourth_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_fourth_section_status']) && $adm_setting['landing_fourth_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_fourth_section_status']) && $adm_setting['landing_fourth_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Landing Fifth Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_fifth_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_fifth_section_status']) && $adm_setting['landing_fifth_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_fifth_section_status']) && $adm_setting['landing_fifth_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Landing Sixth Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_sixth_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_sixth_section_status']) && $adm_setting['landing_sixth_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_sixth_section_status']) && $adm_setting['landing_sixth_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Landing seventh Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_seventh_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_seventh_section_status']) && $adm_setting['landing_seventh_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_seventh_section_status']) && $adm_setting['landing_seventh_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Landing Advertisement Section Status')}}</label>
                            <div class="cp-select-area">
                                <select name="landing_advertisement_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_advertisement_section_status']) && $adm_setting['landing_advertisement_section_status'] == ENABLE) selected @endif value="{{ENABLE}}">{{__("Enable")}}</option>
                                    <option @if(isset($adm_setting['landing_advertisement_section_status']) && $adm_setting['landing_advertisement_section_status'] == DISABLE) selected @endif value="{{DISABLE}}">{{__("Disable")}}</option>
                                </select>
                            </div>
                        </div>

                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
