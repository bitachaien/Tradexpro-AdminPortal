<div>
    <label class="switch">
        <input type="checkbox" onclick="return changeStatus('{{ encrypt($item->id) }}')" id="notification"
            name="security" {{ $item->status == STATUS_ACTIVE ? 'checked' : '' }} />
        <span class="slider" for="status"></span>
    </label>
</div>