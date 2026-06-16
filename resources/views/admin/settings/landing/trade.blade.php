<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Landing Page Trade Related Settings')}}</h3>
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
                            <label for="#">{{__('Market Trade Heading')}}</label>
                            <input type="text" class="form-control" name="market_trend_title"
                                   @if(isset($adm_setting['market_trend_title']))value="{{$adm_setting['market_trend_title']}}" @endif>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="#">{{__('Trade Anywhere Heading')}}</label>
                            <input type="text" class="form-control" name="trade_anywhere_title"
                                   @if(isset($adm_setting['trade_anywhere_title']))value="{{$adm_setting['trade_anywhere_title']}}" @endif>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="#">{{__('Secure Trade Heading')}}</label>
                            <input type="text" class="form-control" name="secure_trade_title"
                                   @if(isset($adm_setting['secure_trade_title']))value="{{$adm_setting['secure_trade_title']}}" @endif>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="#">{{__('Trade anywhere left image')}}</label>
                                    <div id="file-upload" class="section-width">
                                        <input type="file" placeholder="0.00" name="trade_anywhere_left_img" value="" id="file" ref="file"
                                               class="dropify" @if(isset($adm_setting['trade_anywhere_left_img'])) data-default-file="{{asset(path_image().$adm_setting['trade_anywhere_left_img'])}}"@endif />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="#">{{__('Secure trade left image')}}</label>
                                    <div id="file-upload" class="section-width">
                                        <input type="file" placeholder="0.00" name="secure_trade_left_img" value="" id="file" ref="file"
                                               class="dropify" @if(isset($adm_setting['secure_trade_left_img'])) data-default-file="{{asset(path_image().$adm_setting['secure_trade_left_img'])}}"@endif />
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <button class="button-primary theme-btn">{{__('Update')}}</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
