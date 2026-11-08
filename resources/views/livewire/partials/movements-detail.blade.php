<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-arrow-left-right-line" aria-hidden="true"></i>
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
            <button type="button" class="tab-button" :class="{ selected: activeTab === 'related' }" @click="activeTab = 'related'">Riwayat perpindahan</button>
        </div>
        <div class="detail-content" x-show="activeTab === 'information'">
            <h3>Informasi mutasi</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Kode mutasi</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Aset</dt>
                    <dd>{{ $record['asset'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Lokasi asal</dt>
                    <dd>{{ $record['origin'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Lokasi tujuan</dt>
                    <dd>{{ $record['destination'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal mutasi</dt>
                    <dd>{{ $record['date'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>
                        <x-badge :value="$record['status']" />
                    </dd>
                </div>
                <div class="full-width">
                    <dt>Alasan perpindahan</dt>
                    <dd>{{ $record['description'] ?: '—' }}</dd>
                </div>
            </dl>
            <div class="workflow-actions">
                <p>Petugas: <strong>{{ $record['officer'] }}</strong>
                </p>
                @can('module-approve', 'movements')
                    @if ($record['status'] === 'Diajukan')
                        <button type="button" class="button button-primary" wire:click="changeStatus({{ $record['id'] }}, 'Disetujui')">Setujui pengajuan</button>
                        <button type="button" class="button button-danger" wire:click="changeStatus({{ $record['id'] }}, 'Ditolak')">Tolak pengajuan</button>
                    @elseif ($record['status'] === 'Disetujui')
                        <button type="button" class="button button-primary" wire:click="changeStatus({{ $record['id'] }}, 'Selesai')" wire:confirm="Pastikan proses fisik sudah selesai. Lanjutkan?">Selesaikan perpindahan</button>
                    @endif
                @endcan
            </div>
        </div>
        <div class="detail-content" x-show="activeTab === 'related'" x-cloak>
            <h3>Riwayat perpindahan</h3>
            <div class="timeline">
                @forelse ($movements->where('asset', $record['asset'])->sortByDesc('date') as $movement)
                    <a href="{{ route('movements.show', $movement['id']) }}" class="timeline-item" wire:key="related-movement-{{ $movement['id'] }}">
                        <span class="timeline-dot">
                        </span>
                        <div>
                            <strong>{{ $movement['origin'] }} → {{ $movement['destination'] }}</strong>
                            <small>{{ $movement['date'] }} · {{ $movement['officer'] }} · {{ $movement['status'] }}</small>
                        </div>
                    </a>
                @empty
                    <p class="empty-inline">Belum ada riwayat mutasi.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
