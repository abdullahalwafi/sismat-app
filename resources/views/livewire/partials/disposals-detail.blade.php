<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-delete-bin-5-line" aria-hidden="true"></i>
        </div>
        <span class="eyebrow">{{ $record['code'] }}</span>
        <h2>{{ $record['name'] ?? $record['asset_name'] ?? $record['code'] }}</h2>
        <x-badge :value="$record['status']" />
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
            <h3>Informasi pengajuan</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Kode pengajuan</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Aset</dt>
                    <dd>{{ $record['asset'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Metode penghapusan</dt>
                    <dd>{{ $record['method'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal pengajuan</dt>
                    <dd>{{ $record['date'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Status persetujuan</dt>
                    <dd>
                        <x-badge :value="$record['status']" />
                    </dd>
                </div>
                <div class="full-width">
                    <dt>Alasan penghapusan</dt>
                    <dd>{{ $record['description'] ?: '—' }}</dd>
                </div>
            </dl>
            <div class="workflow-actions">
                <p>Petugas: <strong>{{ $record['officer'] }}</strong>
                </p>
                @can('module-approve', 'disposals')
                    @if ($record['status'] === 'Menunggu persetujuan')
                        <button type="button" class="button button-primary" wire:click="changeStatus({{ $record['id'] }}, 'Disetujui')">Setujui pengajuan</button>
                        <button type="button" class="button button-danger" wire:click="changeStatus({{ $record['id'] }}, 'Ditolak')">Tolak pengajuan</button>
                    @elseif ($record['status'] === 'Disetujui')
                        <button type="button" class="button button-primary" wire:click="changeStatus({{ $record['id'] }}, 'Selesai')" wire:confirm="Pastikan proses fisik sudah selesai. Lanjutkan?">Selesaikan penghapusan aset</button>
                    @endif
                @endcan
            </div>
        </div>
    </section>
</div>
