<div class="form-grid">
    <div class="form-field" wire:key="field-rooms-code">
        <label for="field-code">Kode ruangan <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="30">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-rooms-name">
        <label for="field-name">Nama ruangan <span class="required">*</span>
        </label>
        <input id="field-name" type="text" wire:model="form.name" required maxlength="100">
        @error('form.name')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-rooms-building">
        <label for="field-building">Gedung <span class="required">*</span>
        </label>
        <select id="field-building" wire:model.live="form.building" required>
            <option value="">Pilih gedung</option>
            @foreach ($this->options('building') as $value => $label)
                <option value="{{ $value }}" wire:key="option-building-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.building')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-rooms-floor">
        <label for="field-floor">Lantai <span class="required">*</span>
        </label>
        <input id="field-floor" type="text" wire:model="form.floor" required maxlength="20">
        @error('form.floor')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-rooms-capacity">
        <label for="field-capacity">Kapasitas aset <span class="required">*</span>
        </label>
        <input id="field-capacity" type="number" wire:model="form.capacity" required min="0" max="999999999999" step="1">
        @error('form.capacity')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-rooms-status">
        <label for="field-status">Status <span class="required">*</span>
        </label>
        <select id="field-status" wire:model.live="form.status" required>
            <option value="">Pilih status</option>
            @foreach ($this->options('status') as $value => $label)
                <option value="{{ $value }}" wire:key="option-status-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.status')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-rooms-description">
        <label for="field-description">Deskripsi</label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000"></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-rooms-department">
        <label for="field-department">Unit / departemen</label>
        <input id="field-department" type="text" wire:model="form.department" maxlength="100">
        @error('form.department')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
