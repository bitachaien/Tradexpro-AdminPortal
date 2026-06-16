<div class="row">
    <div class="card-body">
        <form action="{{ route('giftCardHeaderSave') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-12 mb-xl-0 mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Header') }}</label>
                            <input type="text" name="gif_card_main_page_header" class="form-control"
                                @if (isset($setting)) value="{{ $setting['gif_card_main_page_header'] ?? '' }}" @else value="{{ old('gif_card_main_page_header') }}" @endif>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Description') }}</label>
                            <input type="text" name="gif_card_main_page_description" class="form-control"
                                @if (isset($setting)) value="{{ $setting['gif_card_main_page_description'] ?? '' }}" @else value="{{ old('gif_card_main_page_description') }}" @endif>
                        </div>
                    </div>
                </div>


            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{ __('Banner') }}</label>
                    <input type="file" name="gif_card_main_page_banner" class="dropify" id=""
                        @if (isset($setting)) data-default-file="{{ isset($setting) ? asset(IMG_PATH . ($setting['gif_card_main_page_banner'] ?? '')) : '' }}" @endif>
                </div>
            </div>
            <div class="col-12 mb-xl-0 mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Second Header') }}</label>
                            <input type="text" name="gif_card_second_main_page_header" class="form-control"
                                @if (isset($setting)) value="{{ $setting['gif_card_second_main_page_header'] ?? '' }}" @else value="{{ old('gif_card_second_main_page_header') }}" @endif>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Second Description') }}</label>
                            <input type="text" name="gif_card_second_main_page_description" class="form-control"
                                @if (isset($setting)) value="{{ $setting['gif_card_second_main_page_description'] ?? '' }}" @else value="{{ old('gif_card_second_main_page_description') }}" @endif>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Second Banner') }}</label>
                            <input type="file" name="gif_card_second_main_page_banner" class="dropify" id=""
                                @if (isset($setting)) data-default-file="{{ isset($setting) ? asset(IMG_PATH . ($setting['gif_card_second_main_page_banner'] ?? '')) : '' }}" @endif>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12 mb-xl-0 mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Redeem Description') }}</label>
                            <textarea name="gif_card_redeem_description" rows="4" class="form-control">{{ isset($setting['gif_card_redeem_description']) ? $setting['gif_card_redeem_description'] : '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Add Card Description') }}</label>
                            <textarea name="gif_card_add_card_description" rows="4" class="form-control">{{ isset($setting['gif_card_add_card_description']) ? $setting['gif_card_add_card_description'] : '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Check Card Description') }}</label>
                            <textarea name="gif_card_check_card_description" rows="4" class="form-control">{{ isset($setting['gif_card_check_card_description']) ? $setting['gif_card_check_card_description'] : '' }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12">
                <div class="form-group">
                    @if (isset($banner))
                        <input type="hidden" name="uid" value="{{ $banner->uid }}">
                    @endif
                    <button type="submit" class="button-primary theme-btn">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
