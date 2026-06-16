<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Signup Email Verification')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSettingsSaveCommon')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label class="text-danger">{{__('If you disable Email verification for Sign Up, people can register with any Email - Valid or Invalid. Please be cautious before disabling this.')}}</label><br>
                    <label>{{__('Choose an option ')}}</label>
                    <div class="cp-select-area">
                        <select name="signup_email_verification" class="form-control">
                            <option @if(isset($settings['signup_email_verification']) && $settings['signup_email_verification'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                            <option @if(isset($settings['signup_email_verification']) && $settings['signup_email_verification'] == STATUS_INACTIVE) selected @endif value="{{STATUS_INACTIVE}}">{{__("Disable")}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Save')}}</button>
            </div>
        </div>
    </form>
</div>
