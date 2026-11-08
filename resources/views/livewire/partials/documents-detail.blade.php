<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-file-text-line" aria-hidden="true"></i>
        </div>
        <span class="eyebrow">{{ $record['code'] }}</span>
        <h2>{{ $record['name'] ?? $record['asset_name'] ?? $record['code'] }}</h2>
        <div class="identity-note">
            <i class="ri-shield-check-line" aria-hidden="true"></i>
            <span>Informasi inventaris<br>
                <small>Tersimpan di database</small>
            </span>
        </div>
    </aside>
    <section class="panel detail-panel" x-data="{ activeTab: 'information' }">
        <div class="tab-bar">
            <button type="button" class="tab-button" :class="{ selected: activeTab === 'information' }" @click="activeTab = 'information'">Informasi</button>
        </div>
        <div class="detail-content" x-show="activeTab === 'information'">
            <h3>Informasi dokumen</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Kode dokumen</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Nama dokumen</dt>
                    <dd>{{ $record['name'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Aset</dt>
                    <dd>{{ $record['asset'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Jenis dokumen</dt>
                    <dd>{{ $record['type'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal dokumen</dt>
                    <dd>{{ $record['date'] ?: '—' }}</dd>
                </div>
                <div class="full-width">
                    <dt>Catatan</dt>
                    <dd>{{ $record['description'] ?: '—' }}</dd>
                </div>
            </dl>
            <div class="document-placeholder">
                <i class="ri-file-pdf-2-line" aria-hidden="true"></i>
                <h3>{{ $record['attachment'] }}</h3>
                @if ($record['available'])
                    <a href="{{ route('documents.download', $record['id']) }}" class="button button-primary">Download</a>
                    <a href="{{ route('documents.preview', $record['id']) }}" target="_blank" rel="noopener" class="button button-secondary">Buka preview</a>
                    <iframe src="{{ route('documents.preview', $record['id']) }}" class="pdf-preview" title="Preview dokumen tersimpan">
                    </iframe>
                @else
                    <p>Metadata tersedia, tetapi berkas fisik belum diunggah. Edit dokumen untuk mengunggah berkas.</p>
                @endif
            </div>
        </div>
    </section>
</div>
