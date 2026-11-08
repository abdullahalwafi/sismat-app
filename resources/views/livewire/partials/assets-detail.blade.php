<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            @if ($record['photo'])
                <img src="{{ $record['photo'] }}" alt="Foto {{ $record['name'] }}">
            @else
                <i class="ri-stethoscope-line" aria-hidden="true"></i>
            @endif
        </div>
        <span class="eyebrow">{{ $record['code'] }}</span>
        <h2>{{ $record['name'] ?? $record['asset_name'] ?? $record['code'] }}</h2>
        <x-badge :value="$record['status']" />
        <x-badge :value="$record['condition']" />
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
            <button type="button" class="tab-button" :class="{ selected: activeTab === 'related' }" @click="activeTab = 'related'">Riwayat & dokumen</button>
        </div>
        <div class="detail-content" x-show="activeTab === 'information'">
            <h3>Informasi aset</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Kode aset</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Nama aset</dt>
                    <dd>{{ $record['name'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Kategori</dt>
                    <dd>{{ $record['category'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Merek</dt>
                    <dd>{{ $record['brand'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Model</dt>
                    <dd>{{ $record['model'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Serial number</dt>
                    <dd>{{ $record['serial_number'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Gedung</dt>
                    <dd>{{ $record['building'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Ruangan</dt>
                    <dd>{{ $record['room'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal perolehan</dt>
                    <dd>{{ $record['acquired_at'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Harga perolehan (Rp)</dt>
                    <dd>Rp {{ number_format($record['price'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Kondisi</dt>
                    <dd>
                        <x-badge :value="$record['condition']" />
                    </dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>
                        <x-badge :value="$record['status']" />
                    </dd>
                </div>
                <div class="full-width">
                    <dt>Deskripsi</dt>
                    <dd>{{ $record['description'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Supplier</dt>
                    <dd>{{ $record['supplier'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Masa manfaat (tahun)</dt>
                    <dd>{{ $record['useful_life_years'] ?: '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="detail-content" x-show="activeTab === 'related'" x-cloak>
            <h3>Riwayat pemeliharaan</h3>
            <div class="related-list">
                @forelse ($maintenances->where('asset', $record['code']) as $maintenance)
                    <a href="{{ route('maintenances.show', $maintenance['id']) }}" wire:key="related-maintenance-{{ $maintenance['id'] }}">
                        <div>
                            <strong>{{ $maintenance['type'] }} · {{ $maintenance['date'] }}</strong>
                            <small>{{ $maintenance['technician'] }}</small>
                        </div>
                        <x-badge :value="$maintenance['status']" />
                    </a>
                @empty
                    <p class="empty-inline">Belum ada riwayat pemeliharaan.</p>
                @endforelse
            </div>
            <h3>Riwayat perpindahan</h3>
            <div class="timeline">
                @forelse ($movements->where('asset', $record['code'])->sortByDesc('date') as $movement)
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
            <h3>Dokumen pendukung</h3>
            <div class="related-list">
                @forelse ($documents->where('asset', $record['code']) as $document)
                    <a href="{{ route('documents.show', $document['id']) }}" wire:key="related-document-{{ $document['id'] }}">
                        <i class="ri-file-text-line" aria-hidden="true"></i>
                        <div>
                            <strong>{{ $document['name'] }}</strong>
                            <small>{{ $document['type'] }} · {{ $document['date'] }}</small>
                        </div>
                    </a>
                @empty
                    <p class="empty-inline">Belum ada dokumen.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
