<div class="detail-layout">
    <aside class="panel identity-panel">
        <div class="identity-image">
            <i class="ri-user-settings-line" aria-hidden="true"></i>
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
            <h3>Informasi pengguna</h3>
            <dl class="detail-grid">
                <div>
                    <dt>Email</dt>
                    <dd>{{ $record['code'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Nama pengguna</dt>
                    <dd>{{ $record['name'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Role</dt>
                    <dd>{{ $record['role'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Telepon</dt>
                    <dd>{{ $record['phone'] ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Status akun</dt>
                    <dd>
                        <x-badge :value="$record['status']" />
                    </dd>
                </div>
            </dl>
        </div>
    </section>
</div>
