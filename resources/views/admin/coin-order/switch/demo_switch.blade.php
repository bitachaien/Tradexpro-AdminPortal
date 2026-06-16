@php
    $demoTrade = (isset($module) && isset($module['DemoTrade'])) ? true : false ;
@endphp
@if($demoTrade)
<div>
    <label class="switch">
        <input type="checkbox" onclick="changeDemoTradeStatus('{{$coin->coin_type}}')"
            id="notification" name="security" @if($coin->is_demo_trade == STATUS_ACTIVE) checked @endif>
        <span class="slider" for="status"></span>
    </label>
</div>
@endif
