<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-building-2-line" aria-hidden="true"></i>
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
            <button type="button" class="tab-button" :class="{ selected: activeTab === 'related' }" @click="activeTab = 'related'">Daftar aset</button>
        </div>
        <div class="detail-content" x-show="activeTab === 'information'">
            <h3>Informasi gedung</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Kode gedung</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Nama gedung</dt>
                    <dd>{{ $record['name'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Lokasi / alamat</dt>
                    <dd>{{ $record['address'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Jumlah lantai</dt>
                    <dd>{{ $record['floors'] ?: '—' }}</dd>
                </div>
                <div class="full-width">
                    <dt>Deskripsi</dt>
                    <dd>{{ $record['description'] ?: '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="detail-content" x-show="activeTab === 'related'" x-cloak>
            <h3>Aset di {{ $record['name'] }}</h3>
            <div class="related-list">
                @forelse ($assets->where('building_id', $record['id']) as $asset)
                    <a href="{{ route('assets.show', $asset['id']) }}" wire:key="related-asset-{{ $asset['id'] }}">
                        <span class="surface-icon">
                            <i class="ri-stethoscope-line" aria-hidden="true"></i>
                        </span>
                        <div>
                            <strong>{{ $asset['name'] }}</strong>
                            <small>{{ $asset['code'] }} · {{ $asset['room'] }}</small>
                        </div>
                        <x-badge :value="$asset['condition']" />
                    </a>
                @empty
                    <p class="empty-inline">Belum ada aset di lokasi ini.</p>
                @endforelse
            </div>
            <h3>Ruangan di gedung</h3>
            <div class="related-list">
                @foreach ($rooms->where('building_id', $record['id']) as $room)
                    <a href="{{ route('rooms.show', $room['id']) }}" wire:key="related-room-{{ $room['id'] }}">
                        <strong>{{ $room['name'] }}</strong>
                        <small>Lantai {{ $room['floor'] }}</small>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</div>
