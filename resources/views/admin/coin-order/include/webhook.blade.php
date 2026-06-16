<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Webhook Title')}}</div>
                <input type="text" class="form-control" name="label"
                       @if(isset($item))value="{{$item->bitgo_webhook_label}}" @else value="" @endif>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="allTokentype">{{__('Webhook Type')}}</label>
            <select name="type" id="allTokentype" class="form-control">
                @foreach(webhook_type() as $key => $value)
                    <option @if(isset($item) && $item->bitgo_webhook_type == $key) selected @endif value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Webhook Url')}}</div>
                <input type="text" class="form-control" name="url"
                       @if(isset($item) && (!empty($item->bitgo_webhook_url)))value="{{$item->bitgo_webhook_url}}" @else value="" @endif>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Number of Confirmation')}}</div>
                <input type="text" class="form-control" name="numConfirmations"
                       @if(isset($item))value="{{$item->bitgo_webhook_numConfirmations}}" @else value="1" @endif>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-12 mt-20">
        <div class="form-group">
            <label for="allToken">{{__('All Token')}}</label>
            <select name="allToken" id="allToken" class="form-control">
                <option @if(isset($item) && $item->bitgo_webhook_allToken == DISABLE) selected @endif value="{{DISABLE}}">{{__('False')}}</option>
                <option @if(isset($item) && $item->bitgo_webhook_allToken == ENABLE) selected @endif value="{{ENABLE}}">{{__('True')}}</option>
            </select>
        </div>
    </div>
</div>
