<div class="form-grid">
    <div class="form-field" wire:key="field-schedules-code">
        <label for="field-code">Kode jadwal <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="50">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-schedules-asset">
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
    <div class="form-field" wire:key="field-schedules-date">
        <label for="field-date">Tanggal jadwal <span class="required">*</span>
        </label>
        <input id="field-date" type="date" wire:model="form.date" required>
        @error('form.date')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-schedules-technician">
        <label for="field-technician">Teknisi <span class="required">*</span>
        </label>
        <select id="field-technician" wire:model.live="form.technician" required>
            <option value="">Pilih teknisi</option>
            @foreach ($this->options('technician') as $value => $label)
                <option value="{{ $value }}" wire:key="option-technician-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.technician')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-schedules-type">
        <label for="field-type">Jenis <span class="required">*</span>
        </label>
        <select id="field-type" wire:model.live="form.type" required>
            <option value="">Pilih jenis</option>
            @foreach ($this->options('type') as $value => $label)
                <option value="{{ $value }}" wire:key="option-type-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.type')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-schedules-status">
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
    <div class="form-field full-width" wire:key="field-schedules-description">
        <label for="field-description">Catatan</label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000"></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
