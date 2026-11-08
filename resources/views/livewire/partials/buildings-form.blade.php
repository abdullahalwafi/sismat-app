<div class="form-grid">
    <div class="form-field" wire:key="field-buildings-code">
        <label for="field-code">Kode gedung <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="30">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-buildings-name">
        <label for="field-name">Nama gedung <span class="required">*</span>
        </label>
        <input id="field-name" type="text" wire:model="form.name" required maxlength="100">
        @error('form.name')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-buildings-address">
        <label for="field-address">Lokasi / alamat <span class="required">*</span>
        </label>
        <input id="field-address" type="text" wire:model="form.address" required maxlength="100">
        @error('form.address')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-buildings-floors">
        <label for="field-floors">Jumlah lantai <span class="required">*</span>
        </label>
        <input id="field-floors" type="number" wire:model="form.floors" required min="0" max="999999999999" step="1">
        @error('form.floors')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-buildings-description">
        <label for="field-description">Deskripsi</label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000"></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
