<div>
    <label class="switch">
        <input type="checkbox"
            onclick="defaultStatus('{{ encrypt($item->id) }}')"
            name="" @if ($item->is_default == STATUS_ACTIVE) checked @endif>
        <span class="slider" for="status"></span>
    </label>
</div>
