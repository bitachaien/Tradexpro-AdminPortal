<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Two Factor At Withdrawal')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('SaveTwoFactorData')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Enable Two Factor On Swap')}}</label>
                    <select name="two_factor_swap" class="form-control" >
                        <option @if(isset($settings['two_factor_swap']) && $settings['two_factor_swap'] == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                        <option @if(isset($settings['two_factor_swap']) && $settings['two_factor_swap'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                    </select>
                </div>
            </div>
        </div>
        <input type="hidden" name="tab" value="two_factor_swap">
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
