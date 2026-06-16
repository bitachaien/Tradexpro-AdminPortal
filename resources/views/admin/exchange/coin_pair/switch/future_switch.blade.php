<div>
    <label class="switch">
        <input type="checkbox"
            onclick="return futureTradeStatus('{{ encrypt($item->id) }}')"
            id="" name=""
            @if ($item->enable_future_trade == STATUS_ACTIVE) checked @endif>
        <span class="slider" for="status"></span>
    </label>
</div>
