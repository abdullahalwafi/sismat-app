<div class="form-grid">
    <div class="form-field" wire:key="field-documents-code">
        <label for="field-code">Kode dokumen <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="50">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-documents-name">
        <label for="field-name">Nama dokumen <span class="required">*</span>
        </label>
        <input id="field-name" type="text" wire:model="form.name" required maxlength="100">
        @error('form.name')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-documents-asset">
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
    <div class="form-field" wire:key="field-documents-type">
        <label for="field-type">Jenis dokumen <span class="required">*</span>
        </label>
        <select id="field-type" wire:model.live="form.type" required>
            <option value="">Pilih jenis dokumen</option>
            @foreach ($this->options('type') as $value => $label)
                <option value="{{ $value }}" wire:key="option-type-{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('form.type')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-documents-date">
        <label for="field-date">Tanggal dokumen <span class="required">*</span>
        </label>
        <input id="field-date" type="date" wire:model="form.date" required>
        @error('form.date')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-documents-description">
        <label for="field-description">Catatan</label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000"></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-documents-attachment">
        <label for="field-attachment">Berkas dokumen</label>
        <div x-data="localPreview('document')" class="upload-area" wire:key="preview-documents-{{ $recordId ?? 'new' }}">
            <i aria-hidden="true" class="ri-upload-cloud-2-line upload-icon">
            </i>
            <strong>Pilih dokumen pendukung</strong>
            <p>PDF, JPG, PNG · maksimal 5 MB</p>
            <input id="field-attachment" type="file" wire:model="documentUpload" @change="select($event)"
            accept="application/pdf,image/jpeg,image/png">
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
            <small>Berkas disimpan setelah tombol Simpan ditekan.</small>
            <span wire:loading wire:target="documentUpload">Mengunggah berkas...</span>
            @error('documentUpload')
                <span class="field-error" role="alert">{{ $message }}</span>
            @enderror
        </div>
        @error('form.attachment')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
