<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Username')}}</div>
                @if(env('APP_MODE') == 'demo')
                    <input type="text" class="form-control" value="{{'disablefordemo'}}">
                @else
                    <input type="text" class="form-control" name="coin_api_user"
                           value="{{$item->coin_api_user ?? old('coin_api_user')}}">
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Password')}}</div>
                @if(env('APP_MODE') == 'demo')
                    <input type="password" class="form-control" value="{{'disablefordemo'}}">
                @else
                    <input type="password" class="form-control" name="coin_api_pass"
                           value="{{$item->coin_api_pass ? decrypt($item->coin_api_pass) : old('coin_api_pass')}}">
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Host')}}</div>
                @if(env('APP_MODE') == 'demo')
                    <input type="text" class="form-control" value="{{'disablefordemo'}}">
                @else
                    <input type="text" class="form-control" name="coin_api_host"
                           value="{{$item->coin_api_host ?? old('coin_api_host')}}">
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Port')}}</div>
                <input type="text" class="form-control" name="coin_api_port"
                       value="{{$item->coin_api_port ?? old('coin_api_port')}}">
            </div>
        </div>
    </div>
</div>

