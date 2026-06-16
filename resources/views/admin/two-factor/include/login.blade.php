<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Two Factor At Login')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('SaveTwoFactorData')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Enable Two Factor At User Login')}}</label>
                    <select name="two_factor_user" class="form-control" >
                        <option @if(isset($settings['two_factor_user']) && $settings['two_factor_user'] == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                        <option @if(isset($settings['two_factor_user']) && $settings['two_factor_user'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                    </select>
                </div>
            </div>
            @if(false)
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Enable Two Factor At Admin Login')}}</label>
                    <select name="two_factor_admin" class="form-control">
                        <option @if(isset($settings['two_factor_admin']) && $settings['two_factor_admin'] == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                        <option @if(isset($settings['two_factor_admin']) && $settings['two_factor_admin'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                    </select>
                </div>
            </div>
            @endif
        </div>
        <input type="hidden" name="tab" value="two_factor_login">
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
