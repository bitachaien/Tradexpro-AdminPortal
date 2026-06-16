<div>
    <label class="switch">
        <input type="checkbox"
            onclick="return processMarketBot('{{ encrypt($item->id) }}')"
            id="" name=""
            @if ($item->bot_trading == STATUS_ACTIVE) checked @endif>
        <span class="slider" for="status"></span>
    </label>
</div>
