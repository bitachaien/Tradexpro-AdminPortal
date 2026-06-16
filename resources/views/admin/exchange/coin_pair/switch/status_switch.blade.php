<div>
    <label class="switch">
        <input type="checkbox"
            onclick="return processForm('{{ encrypt($item->id) }}')"
            id="notification" name=""
            @if ($item->status == STATUS_ACTIVE) checked @endif>
        <span class="slider" for="status"></span>
    </label>
</div>
