<div class="form-grid">
    <div class="form-field" wire:key="field-disposals-code">
        <label for="field-code">Kode pengajuan <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="50">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-disposals-asset">
        <label for="field-asset">Aset <span class="required">*</span>
        </label>
        <select id="field-asset" wire:model.live="form.asset" required>
            <option value="">Pilih aset</option>
            @foreach ($this->options('asset') as $value => $label)
                <option value="{{ $value }}" wire:key="option-asset-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.asset')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-disposals-method">
        <label for="field-method">Metode penghapusan <span class="required">*</span>
        </label>
        <select id="field-method" wire:model.live="form.method" required>
            <option value="">Pilih metode penghapusan</option>
            @foreach ($this->options('method') as $value => $label)
                <option value="{{ $value }}" wire:key="option-method-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.method')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-disposals-date">
        <label for="field-date">Tanggal pengajuan <span class="required">*</span>
        </label>
        <input id="field-date" type="date" wire:model="form.date" required>
        @error('form.date')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-disposals-status">
        <label for="field-status">Status persetujuan <span class="required">*</span>
        </label>
        <input id="field-status" value="{{ $form['status'] ?? '' }}" readonly>
        <small>Status diproses melalui tombol persetujuan pada halaman detail.</small>
        @error('form.status')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-disposals-description">
        <label for="field-description">Alasan penghapusan <span class="required">*</span>
        </label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000" required></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
