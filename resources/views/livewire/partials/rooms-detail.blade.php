<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-door-open-line" aria-hidden="true"></i>
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
            <button type="button" class="tab-button" :class="{ selected: activeTab === 'related' }" @click="activeTab = 'related'">Daftar aset</button>
        </div>
        <div class="detail-content" x-show="activeTab === 'information'">
            <h3>Informasi ruangan</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Kode ruangan</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Nama ruangan</dt>
                    <dd>{{ $record['name'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Gedung</dt>
                    <dd>{{ $record['building'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Lantai</dt>
                    <dd>{{ $record['floor'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Kapasitas aset</dt>
                    <dd>{{ $record['capacity'] ?: '—' }}</dd>
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
                    <dt>Unit / departemen</dt>
                    <dd>{{ $record['department'] ?: '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="detail-content" x-show="activeTab === 'related'" x-cloak>
            <h3>Aset di {{ $record['name'] }}</h3>
            <div class="related-list">
                @forelse ($assets->where('room_id', $record['id']) as $asset)
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
        </div>
    </section>
</div>
