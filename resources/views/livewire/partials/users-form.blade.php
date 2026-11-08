<div class="form-grid">
    <div class="form-field" wire:key="field-users-code">
        <label for="field-code">Email <span class="required">*</span>
        </label>
        <input id="field-code" type="email" wire:model="form.code" required maxlength="255">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-users-name">
        <label for="field-name">Nama pengguna <span class="required">*</span>
        </label>
        <input id="field-name" type="text" wire:model="form.name" required maxlength="100">
        @error('form.name')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-users-role">
        <label for="field-role">Role <span class="required">*</span>
        </label>
        <select id="field-role" wire:model.live="form.role" required>
            <option value="">Pilih role</option>
            @foreach ($this->options('role') as $value => $label)
                <option value="{{ $value }}" wire:key="option-role-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.role')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-users-phone">
        <label for="field-phone">Telepon</label>
        <input id="field-phone" type="tel" wire:model="form.phone" maxlength="20">
        @error('form.phone')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-users-status">
        <label for="field-status">Status akun <span class="required">*</span>
        </label>
        <select id="field-status" wire:model.live="form.status" required>
            <option value="">Pilih status akun</option>
            @foreach ($this->options('status') as $value => $label)
                <option value="{{ $value }}" wire:key="option-status-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.status')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-users-password">
        <label for="field-password">Password baru (kosongkan saat edit)</label>
        <input id="field-password" type="password" wire:model="form.password" maxlength="200">
        @error('form.password')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
