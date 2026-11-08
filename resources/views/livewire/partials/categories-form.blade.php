<div class="form-grid">
    <div class="form-field" wire:key="field-categories-code">
        <label for="field-code">Kode kategori <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="30">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-categories-name">
        <label for="field-name">Nama kategori <span class="required">*</span>
        </label>
        <input id="field-name" type="text" wire:model="form.name" required maxlength="100">
        @error('form.name')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-categories-description">
        <label for="field-description">Deskripsi</label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000"></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
