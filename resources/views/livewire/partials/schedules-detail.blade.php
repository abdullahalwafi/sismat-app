<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-calendar-check-line" aria-hidden="true"></i>
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
            <h3>Informasi jadwal</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Kode jadwal</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Aset</dt>
                    <dd>{{ $record['asset'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal jadwal</dt>
                    <dd>{{ $record['date'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Teknisi</dt>
                    <dd>{{ $record['technician'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Jenis</dt>
                    <dd>{{ $record['type'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>
                        <x-badge :value="$record['status']" />
                    </dd>
                </div>
                <div class="full-width">
                    <dt>Catatan</dt>
                    <dd>{{ $record['description'] ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </section>
</div>
