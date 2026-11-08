<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-shield-user-line" aria-hidden="true"></i>
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
            <h3>Informasi role</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Nama role</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div class="full-width">
                    <dt>Deskripsi</dt>
                    <dd>{{ $record['description'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Hak akses</dt>
                    <dd>{{ implode(', ', $record['permissions'] ?? []) ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </section>
</div>
