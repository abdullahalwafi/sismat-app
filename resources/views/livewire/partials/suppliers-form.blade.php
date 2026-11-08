<div class="form-grid">
    <div class="form-field" wire:key="field-suppliers-code">
        <label for="field-code">Kode supplier <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="30">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-suppliers-name">
        <label for="field-name">Nama perusahaan <span class="required">*</span>
        </label>
        <input id="field-name" type="text" wire:model="form.name" required maxlength="100">
        @error('form.name')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-suppliers-contact">
        <label for="field-contact">Nama kontak <span class="required">*</span>
        </label>
        <input id="field-contact" type="text" wire:model="form.contact" required maxlength="100">
        @error('form.contact')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-suppliers-phone">
        <label for="field-phone">Nomor telepon <span class="required">*</span>
        </label>
        <input id="field-phone" type="tel" wire:model="form.phone" required maxlength="20">
        @error('form.phone')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-suppliers-email">
        <label for="field-email">Email <span class="required">*</span>
        </label>
        <input id="field-email" type="email" wire:model="form.email" required maxlength="255">
        @error('form.email')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-suppliers-address">
        <label for="field-address">Alamat <span class="required">*</span>
        </label>
        <textarea id="field-address" wire:model="form.address" rows="3" maxlength="2000" required></textarea>
        @error('form.address')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
