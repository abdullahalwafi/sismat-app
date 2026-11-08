<div class="form-grid">
    <div class="form-field" wire:key="field-assets-code">
        <label for="field-code">Kode aset <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="50">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-name">
        <label for="field-name">Nama aset <span class="required">*</span>
        </label>
        <input id="field-name" type="text" wire:model="form.name" required maxlength="100">
        @error('form.name')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-category">
        <label for="field-category">Kategori <span class="required">*</span>
        </label>
        <select id="field-category" wire:model.live="form.category" required>
            <option value="">Pilih kategori</option>
            @foreach ($this->options('category') as $value => $label)
                <option value="{{ $value }}" wire:key="option-category-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.category')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-brand">
        <label for="field-brand">Merek <span class="required">*</span>
        </label>
        <input id="field-brand" type="text" wire:model="form.brand" required maxlength="100">
        @error('form.brand')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-model">
        <label for="field-model">Model <span class="required">*</span>
        </label>
        <input id="field-model" type="text" wire:model="form.model" required maxlength="100">
        @error('form.model')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-serial_number">
        <label for="field-serial_number">Serial number <span class="required">*</span>
        </label>
        <input id="field-serial_number" type="text" wire:model="form.serial_number" required maxlength="100">
        @error('form.serial_number')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-building">
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
    <div class="form-field" wire:key="field-assets-room">
        <label for="field-room">Ruangan <span class="required">*</span>
        </label>
        <select id="field-room" wire:model.live="form.room" required>
            <option value="">Pilih ruangan</option>
            @foreach ($this->options('room') as $value => $label)
                <option value="{{ $value }}" wire:key="option-room-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.room')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-acquired_at">
        <label for="field-acquired_at">Tanggal perolehan <span class="required">*</span>
        </label>
        <input id="field-acquired_at" type="date" wire:model="form.acquired_at" required>
        @error('form.acquired_at')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-price">
        <label for="field-price">Harga perolehan (Rp) <span class="required">*</span>
        </label>
        <input id="field-price" type="number" wire:model="form.price" required min="0" max="999999999999" step="1">
        @error('form.price')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-condition">
        <label for="field-condition">Kondisi <span class="required">*</span>
        </label>
        <select id="field-condition" wire:model.live="form.condition" required>
            <option value="">Pilih kondisi</option>
            @foreach ($this->options('condition') as $value => $label)
                <option value="{{ $value }}" wire:key="option-condition-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.condition')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-status">
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
    <div class="form-field full-width" wire:key="field-assets-description">
        <label for="field-description">Deskripsi</label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000"></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-assets-photo">
        <label for="field-photo">Foto aset</label>
        <div x-data="localPreview('photo')" class="upload-area" wire:key="preview-assets-{{ $recordId ?? 'new' }}">
            <i aria-hidden="true" class="ri-upload-cloud-2-line upload-icon">
            </i>
            <strong>Pilih foto aset</strong>
            <p>JPG, PNG, WebP · maksimal 2 MB</p>
            <input id="field-photo" type="file" wire:model="photoUpload" @change="select($event)"
            accept="image/jpeg,image/png,image/webp">
            <span class="field-error" x-show="error" x-text="error" role="alert">
            </span>
            <span x-show="filename" x-text="filename" class="file-name">
            </span>
            <template x-if="preview && mime !== 'application/pdf'">
                <img :src="preview" alt="Preview berkas lokal" class="image-preview">
            </template>
            <template x-if="preview && mime === 'application/pdf'">
                <iframe :src="preview" title="Preview PDF lokal" class="pdf-preview">
                </iframe>
            </template>
            @if (! empty($form['photo']))
                <img x-show="!preview" src="{{ $form['photo'] }}" alt="Foto aset tersimpan" class="image-preview">
            @endif
            <small>Berkas disimpan setelah tombol Simpan ditekan.</small>
            <span wire:loading wire:target="photoUpload">Mengunggah berkas...</span>
            @error('photoUpload')
                <span class="field-error" role="alert">{{ $message }}</span>
            @enderror
        </div>
        @error('form.photo')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-supplier">
        <label for="field-supplier">Supplier</label>
        <select id="field-supplier" wire:model.live="form.supplier">
            <option value="">Pilih supplier</option>
            @foreach ($this->options('supplier') as $value => $label)
                <option value="{{ $value }}" wire:key="option-supplier-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.supplier')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-assets-useful_life_years">
        <label for="field-useful_life_years">Masa manfaat (tahun)</label>
        <input id="field-useful_life_years" type="number" wire:model="form.useful_life_years" min="0" max="999999999999" step="1">
        @error('form.useful_life_years')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
