<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Referral Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form method="post" action="{{route('adminReferralFeesSettings')}}">
        @csrf
        <div class="row">
            <div class="col-lg-12 col-12  mt-20">
                <div class="form-group">
                    <label
                        class="">{{__('Maximum Affiliation Level : ') }} 3</label>
                </div>
            </div>
            @for($i = 1; $i <=3 ; $i ++)
                <div class="col-lg-6 col-12  mt-20">
                    <div class="form-group">
                        <label for="#">{{ __('Level') }} {{$i}} (%)</label>
                        @php( $slug_name = 'fees_level'.$i)
                        <p class="fees-wrap">
                            <input type="text" class="number_only form-control"
                                   name="{{$slug_name}}"
                                   value="{{ old($slug_name, isset($settings[$slug_name]) ? $settings[$slug_name] : 0) }}">
                            <span>%</span>
                        </p>
                    </div>
                </div>
            @endfor
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
    <!-- Fees Settings end-->
</div>
<div class="header-bar mt-5">
    <div class="table-title">
        <h3>{{__('Trade Referral Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form method="post" action="{{route('adminTradeReferralFeesSettings')}}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <label for="">{{__('Trade Referral Enable/Disable')}}</label>
                <select name="trade_referral_settings" class="form-control">
                    <option value="{{DISABLE}}"
                        {{(isset($settings['trade_referral_settings']) && $settings['trade_referral_settings'] == DISABLE)? 'selected':''}}>{{__('Disable')}}</option>
                    <option value="{{ENABLE}}" 
                        {{(isset($settings['trade_referral_settings']) && $settings['trade_referral_settings'] == ENABLE)? 'selected':''}}>{{__('Enable')}}</option>
                    
                </select>
            </div>
            
            <div class="col-lg-12 col-12 mt-2">
                <div class="form-group">
                    <label
                        class="">{{__('Maximum Affiliation Level : ') }} 3</label>
                </div>
            </div>
            @for($i = 1; $i <=3 ; $i ++)
                <div class="col-lg-6 col-12 ">
                    <div class="form-group">
                        <label for="#">{{ __('Level') }} {{$i}} (%)</label>
                        @php( $slug_name = 'trade_fees_level'.$i)
                        <p class="fees-wrap">
                            <input type="text" class="number_only form-control"
                                   name="{{$slug_name}}"
                                   value="{{ old($slug_name, isset($settings[$slug_name]) ? $settings[$slug_name] : 0) }}">
                            <span>%</span>
                        </p>
                    </div>
                </div>
            @endfor
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
    <!-- Fees Settings end-->
</div>