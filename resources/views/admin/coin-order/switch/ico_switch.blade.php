@if ($coin->ico_id == 0 || $coin->is_listed == 1)
    <div>
        <label class="switch">
            <input type="checkbox" onclick="return processForm('{{$coin->id}}')"
                id="notification" name="security" @if($coin->status == STATUS_ACTIVE) checked @endif>
            <span class="slider" for="status"></span>
        </label>
    </div>
@else
    <button class="btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="{{__('You can  not change the status of this coin')}}">{{__('Disable')}}</button>
@endif
