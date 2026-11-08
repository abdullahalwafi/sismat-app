<div class="form-grid">
    <div class="form-field" wire:key="field-movements-code">
        <label for="field-code">Kode mutasi <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="50">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-movements-asset">
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
    <div class="form-field" wire:key="field-movements-origin">
        <label for="field-origin">Lokasi asal <span class="required">*</span>
        </label>
        <select id="field-origin" wire:model.live="form.origin" required>
            <option value="">Pilih lokasi asal</option>
            @foreach ($this->options('origin') as $value => $label)
                <option value="{{ $value }}" wire:key="option-origin-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.origin')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-movements-destination">
        <label for="field-destination">Lokasi tujuan <span class="required">*</span>
        </label>
        <select id="field-destination" wire:model.live="form.destination" required>
            <option value="">Pilih lokasi tujuan</option>
            @foreach ($this->options('destination') as $value => $label)
                <option value="{{ $value }}" wire:key="option-destination-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.destination')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-movements-date">
        <label for="field-date">Tanggal mutasi <span class="required">*</span>
        </label>
        <input id="field-date" type="date" wire:model="form.date" required>
        @error('form.date')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-movements-status">
        <label for="field-status">Status <span class="required">*</span>
        </label>
        <input id="field-status" value="{{ $form['status'] ?? '' }}" readonly>
        <small>Status diproses melalui tombol persetujuan pada halaman detail.</small>
        @error('form.status')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-movements-description">
        <label for="field-description">Alasan perpindahan <span class="required">*</span>
        </label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000" required></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
