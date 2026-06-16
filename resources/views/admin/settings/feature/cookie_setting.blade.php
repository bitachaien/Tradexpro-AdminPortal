<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Update cookie Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Enable Cookie')}}</label>
                    <div class="cp-select-area">
                        <select name="cookie_status" class="form-control">
                            <option @if(isset($settings['cookie_status']) && $settings['cookie_status'] == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("No")}}</option>
                            <option @if(isset($settings['cookie_status']) && $settings['cookie_status'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label
                        for="#">{{__('Cookie Header Text')}} </label>
                    <input class="form-control " type="text"
                           name="cookie_header" placeholder=""
                           value="{{isset($settings['cookie_header']) ? $settings['cookie_header'] : ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label
                        for="#">{{__('Cookie Button Text')}} </label>
                    <input class="form-control " type="text"
                           name="cookie_button_text" placeholder=""
                           value="{{isset($settings['cookie_button_text']) ? $settings['cookie_button_text'] : ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Cookie description')}} </label>
                    <textarea class="form-control" name="cookie_text" id="" rows="1">{{$settings['cookie_text'] ?? ''}}</textarea>
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label>{{__('Select page for cookie')}}</label>
                    <div class="cp-select-area">
                        <select name="cookie_page_key" class="form-control">
                            @if(isset($pages[0]))
                                @foreach($pages as $page)
                                    <option @if(isset($settings['cookie_page_key']) && $settings['cookie_page_key'] == $page->key) selected @endif value="{{$page->key}}">{{$page->title}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-20">
                <div class="single-uplode">
                    <div class="uplode-catagory">
                        <span>{{__('Cookie Image')}}</span>
                    </div>
                    <div class="form-group buy_coin_address_input ">
                        <div id="file-upload" class="section-p">
                            <input type="file" placeholder="0.00" name="cookie_image" value=""
                                   id="file" ref="file" class="dropify"
                                   @if(isset($settings['cookie_image']) && (!empty($settings['cookie_image'])))  data-default-file="{{asset(path_image().$settings['cookie_image'])}}" @endif />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
