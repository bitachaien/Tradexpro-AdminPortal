<div class="row">
    <div class="card-body">
    <form action="{{ route('giftCardHeaderSave') }}" method="post" enctype="multipart/form-data">
        @csrf
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Header')}}</label>
                    <input type="text" name="gif_card_page_header" class="form-control" @if(isset($setting)) value="{{ $setting['gif_card_page_header'] ?? "" }}" @else value="{{old('gif_card_page_header')}}" @endif>
                </div>
                <div class="form-group">
                    <label>{{__('Description')}}</label>
                    <input type="text" name="gif_card_page_description" class="form-control" @if(isset($setting)) value="{{ $setting['gif_card_page_description'] ?? "" }}" @else value="{{old('gif_card_page_description')}}" @endif>
                </div>
            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Banner')}}</label>
                    <input type="file" name="gif_card_page_banner" class="dropify" id=""
                    @if(isset($setting)) 
                        data-default-file="{{ isset($setting) ? asset(IMG_PATH.($setting['gif_card_page_banner'] ?? '')) : '' }}"
                    @endif>
                </div>
            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Feture One')}}</label>
                    <input type="text" name="gif_card_page_header_feture_one" class="form-control" @if(isset($setting)) value="{{ $setting['gif_card_page_header_feture_one'] ?? "" }}" @else value="{{old('gif_card_page_header_feture_one')}}" @endif>
                </div>
            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Feture One Icon')}}</label>
                    <input type="file" name="gif_card_page_header_feture_one_icon" class="dropify" id=""
                    @if(isset($setting)) 
                        data-default-file="{{ isset($setting) ? asset(IMG_PATH.($setting['gif_card_page_header_feture_one_icon'] ?? '')) : '' }}"
                    @endif>
                </div>
            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Feture Two')}}</label>
                    <input type="text" name="gif_card_page_header_feture_two" class="form-control" @if(isset($setting)) value="{{ $setting['gif_card_page_header_feture_two'] ?? "" }}" @else value="{{old('gif_card_page_header_feture_two')}}" @endif>
                </div>
            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Feture Two Icon')}}</label>
                    <input type="file" name="gif_card_page_header_feture_two_icon" class="dropify" id=""
                    @if(isset($setting)) 
                        data-default-file="{{ isset($setting) ? asset(IMG_PATH.($setting['gif_card_page_header_feture_two_icon'] ?? '')) : '' }}"
                    @endif>
                </div>
            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Feture Three')}}</label>
                    <input type="text" name="gif_card_page_header_feture_three" class="form-control" @if(isset($setting)) value="{{ $setting['gif_card_page_header_feture_three'] ?? "" }}" @else value="{{old('gif_card_page_header_feture_three')}}" @endif>
                </div>
            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{__('Feture Three Icon')}}</label>
                    <input type="file" name="gif_card_page_header_feture_three_icon" class="dropify" id=""
                    @if(isset($setting)) 
                        data-default-file="{{ isset($setting) ? asset(IMG_PATH.($setting['gif_card_page_header_feture_three_icon'] ?? '')) : '' }}"
                    @endif>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    @if(isset($banner))
                        <input type="hidden" name="uid" value="{{$banner->uid}}">
                    @endif
                    <button type="submit" class="button-primary theme-btn">{{__('Save')}}</button>
                </div>
            </div>
    </form>
    </div>
</div>