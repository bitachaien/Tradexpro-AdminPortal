<div class="row">
    <div class="card-body">
        <form action="{{ route('giftCardHeaderSave') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-12 mb-xl-0 mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Header') }}</label>
                            <input type="text" name="themes_gift_card_page_header" class="form-control"
                                @if (isset($setting)) value="{{ $setting['themes_gift_card_page_header'] ?? '' }}" @else value="{{ old('themes_gift_card_page_header') }}" @endif>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Description') }}</label>
                            <input type="text" name="themes_gift_card_page_description" class="form-control"
                                @if (isset($setting)) value="{{ $setting['themes_gift_card_page_description'] ?? '' }}" @else value="{{ old('themes_gift_card_page_description') }}" @endif>
                        </div>
                    </div>
                </div>


            </div>
            <div class="col-xl-6 mb-xl-0 mb-4">
                <div class="form-group">
                    <label>{{ __('Banner') }}</label>
                    <input type="file" name="themes_gift_card_page_banner" class="dropify" id=""
                        @if (isset($setting)) data-default-file="{{ isset($setting) ? asset(IMG_PATH . ($setting['themes_gift_card_page_banner'] ?? '')) : '' }}" @endif>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <button type="submit" class="button-primary theme-btn">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
