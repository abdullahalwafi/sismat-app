<div>
    <x-page-heading title="Dashboard" description="Selamat datang kembali. Berikut ringkasan aset rumah sakit Anda.">
        @can('module-view', 'reports')
            <a href="{{ route('reports.index') }}" class="button button-secondary">
                <i aria-hidden="true" class="ri-bar-chart-box-line"></i> Lihat laporan
            </a>
        @endcan
        @can('module-manage', 'assets')
            <a href="{{ route('assets.create') }}" class="button button-primary">
                <i aria-hidden="true" class="ri-add-line"></i> Tambah aset
            </a>
        @endcan
    </x-page-heading>
    <div class="welcome-banner">
        <div>
            <span class="welcome-label">
                <span></span> INVENTARIS RUMAH SAKIT</span>
            <h2>Aset terkelola,
                <br>pelayanan lebih optimal.
            </h2>
            <p>Pantau kondisi, lokasi, dan kesiapan aset dalam satu tempat.</p>
            <a href="{{ route('assets.index') }}">Jelajahi inventaris
                <i aria-hidden="true" class="ri-arrow-right-line"></i>
            </a>
        </div>
        <div class="hospital-illustration" aria-hidden="true">
            <div class="illustration-circle"></div>
            <i aria-hidden="true" class="ri-hospital-line"></i>
            <span class="illustration-label">
                <i aria-hidden="true" class="ri-shield-check-fill"></i> Asset Monitoring</span>
        </div>
    </div>
    <div class="stats-grid">
        <x-stat-card label="Total Aset" :value="$assets->count()" icon="ri-archive-stack-line" note="Seluruh inventaris rumah sakit" />
        <x-stat-card label="Kondisi Baik" :value="$conditions->get('Baik', 0)" icon="ri-shield-check-line" tone="green" :note="($assets->count() ? round(($conditions->get('Baik', 0) / $assets->count()) * 100) : 0) . '% dari total aset'" />
        <x-stat-card label="Perlu Perhatian" :value="$conditions->get('Rusak ringan', 0) + $conditions->get('Rusak berat', 0)" icon="ri-error-warning-line" tone="amber"
            note="Rusak ringan & rusak berat" />
        <x-stat-card label="Dalam Pemeliharaan" :value="$statuses->get('Dalam pemeliharaan', 0)" icon="ri-tools-line" tone="purple" note="Status operasional aset" />
    </div>
    <div class="dashboard-middle">
        <section class="panel category-panel">
            <div class="panel-header">
                <div>
                    <h2>Aset berdasarkan kategori</h2>
                    <p>Distribusi inventaris rumah sakit</p>
                </div>
                <span class="chart-icon">
                    <i aria-hidden="true" class="ri-bar-chart-horizontal-line"></i>
                </span>
            </div>
            <div class="bar-chart" role="img" aria-label="Grafik jumlah aset berdasarkan kategori">
                @foreach ($categories as $label => $count)
                    <div class="bar-row" wire:key="category-{{ $loop->index }}">
                        <div>
                            <span>{{ $label }}</span>
                            <strong>{{ $count }}
                                <small>aset</small>
                            </strong>
                        </div>
                        <div class="bar-track">
                            <span
                                style="width:{{ $assets->count() ? ($count / $assets->count()) * 100 : 0 }}%;background:{{ ['#487fff', '#6e94f7', '#87adff', '#40b5b8', '#8c80d9', '#b2c4ec'][$loop->index % 6] }}">
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="chart-footer">
                <i aria-hidden="true" class="ri-stack-line"></i> {{ $categories->count() }} kategori aktif dalam
                inventaris
            </div>
        </section>
        <section class="panel condition-panel">
            <div class="panel-header">
                <div>
                    <h2>Kondisi aset</h2>
                    <p>Ringkasan kelayakan inventaris</p>
                </div>
                <i aria-hidden="true" class="ri-pie-chart-2-line chart-icon"></i>
            </div>
            @php($total = max(1, $assets->count()))
            @php($good = ($conditions->get('Baik', 0) / $total) * 100)
            @php($light = ($conditions->get('Rusak ringan', 0) / $total) * 100)
            <div class="donut-wrap">
                <div class="donut-chart" role="img"
                    aria-label="Kondisi aset: {{ $conditions->map(fn($count, $label) => $label . ' ' . $count)->implode(', ') }}"
                    style="background:conic-gradient(#35b68b 0% {{ $good }}%,#f4b454 {{ $good }}% {{ $good + $light }}%,#ed6a73 {{ $good + $light }}% 100%)">
                    <div>
                        <strong>{{ $assets->count() }}</strong>
                        <span>Total aset</span>
                    </div>
                </div>
            </div>
            <div class="condition-legend">
                @foreach (['Baik' => '#35b68b', 'Rusak ringan' => '#f4b454', 'Rusak berat' => '#ed6a73'] as $condition => $color)
                    <div wire:key="condition-{{ $loop->index }}">
                        <span class="legend-dot" style="background:{{ $color }}"></span>
                        <span>{{ $condition }}</span>
                        <strong>{{ $conditions->get($condition, 0) }}</strong>
                        <small>{{ round(($conditions->get($condition, 0) / $total) * 100) }}%</small>
                    </div>
                @endforeach
            </div>
        </section>
        <section class="panel status-panel">
            <div class="panel-header">
                <div>
                    <h2>Status operasional</h2>
                    <p>Kesiapan penggunaan aset</p>
                </div>
            </div>
            <div class="status-list">
                @foreach (['Aktif' => ['ri-checkbox-circle-line', 'green'], 'Dalam pemeliharaan' => ['ri-tools-line', 'blue'], 'Tidak aktif' => ['ri-pause-circle-line', 'red'], 'Dihapuskan' => ['ri-delete-bin-line', 'red']] as $status => [$icon, $tone])
                    <div class="status-row" wire:key="status-{{ $loop->index }}">
                        <span class="stat-icon {{ $tone }}">
                            <i aria-hidden="true" class="{{ $icon }}"></i>
                        </span>
                        <span>{{ $status }}</span>
                        <strong>{{ $statuses->get($status, 0) }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="asset-value">
                <small>Total nilai perolehan</small>
                <strong>Rp {{ number_format($assets->sum('price'), 0, ',', '.') }}</strong>
                <span>
                    <i aria-hidden="true" class="ri-information-line"></i> Nilai pembelian, sebelum penyusutan</span>
            </div>
        </section>
    </div>
    <div class="dashboard-bottom">
        <section class="panel latest-assets">
            <div class="panel-header">
                <div>
                    <h2>Aset terbaru</h2>
                    <p>Inventaris yang terakhir ditambahkan</p>
                </div>
                <a href="{{ route('assets.index') }}" class="text-link">Lihat semua
                    <i aria-hidden="true" class="ri-arrow-right-line"></i>
                </a>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama aset
                            </th>
                            <th>Lokasi
                            </th>
                            <th>Kondisi
                            </th>
                            <th>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestAssets as $asset)
                            <tr wire:key="latest-{{ $asset['id'] }}">
                                <td>
                                    <a class="name-cell" href="{{ route('assets.show', $asset['id']) }}">
                                        <span class="item-icon">
                                            <i aria-hidden="true" class="ri-stethoscope-line"></i>
                                        </span>
                                        <div>
                                            <strong>{{ $asset['name'] }}</strong>
                                            <small>{{ $asset['code'] }}</small>
                                        </div>
                                    </a>
                                </td>
                                <td>{{ $asset['room'] }}
                                </td>
                                <td>
                                    <x-badge :value="$asset['condition']" />
                                </td>
                                <td>
                                    <a href="{{ route('assets.show', $asset['id']) }}" class="action-icon"
                                        aria-label="Detail {{ $asset['name'] }}">
                                        <i aria-hidden="true" class="ri-arrow-right-up-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-inline">Belum ada aset.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel activity-panel">
            <div class="panel-header">
                <div>
                    <h2>Aktivitas terbaru</h2>
                    <p>Perubahan dalam ruang kerja</p>
                </div>
                @can('module-view', 'activities')
                    <a href="{{ route('activities.index') }}" class="action-icon" aria-label="Lihat semua aktivitas">
                        <i aria-hidden="true" class="ri-arrow-right-up-line"></i>
                    </a>
                @endcan
            </div>
            <div class="activity-feed">
                @foreach ($activities as $activity)
                    <div class="feed-item" wire:key="activity-{{ $activity['id'] }}">
                        <span class="feed-dot">
                            <i aria-hidden="true" class="ri-history-line"></i>
                        </span>
                        <div>
                            <strong>{{ $activity['description'] }}</strong>
                            <small>{{ $activity['user'] }} · {{ $activity['module'] }}</small>
                            <time>{{ $activity['date'] }} WIB</time>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
    <div class="dashboard-final">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Pemeliharaan berjalan</h2>
                    <p>Pekerjaan yang perlu ditindaklanjuti</p>
                </div>
                @can('module-view', 'maintenances')
                    <a href="{{ route('maintenances.index') }}" class="text-link">Lihat semua
                        <i aria-hidden="true" class="ri-arrow-right-line"></i>
                    </a>
                @endcan
            </div>
            <div class="maintenance-grid">
                @forelse($maintenances as $maintenance)
                    <a class="maintenance-card" href="{{ route('maintenances.show', $maintenance['id']) }}"
                        wire:key="maintenance-{{ $maintenance['id'] }}">
                        <div>
                            <span class="surface-icon">
                                <i aria-hidden="true" class="ri-tools-line"></i>
                            </span>
                            <x-badge :value="$maintenance['status']" />
                        </div>
                        <strong>{{ $assets->firstWhere('code', $maintenance['asset'])['name'] ?? $maintenance['asset'] }}</strong>
                        <small>{{ $maintenance['type'] }} · {{ $maintenance['date'] }}</small>
                        <span>{{ $maintenance['technician'] }}
                            <i aria-hidden="true" class="ri-arrow-right-line"></i>
                        </span>
                    </a>
                @empty
                    <p class="empty-inline">Tidak ada pekerjaan pemeliharaan yang berjalan.</p>
                @endforelse
            </div>
        </section>
        <section class="panel quick-panel">
            <div class="panel-header">
                <div>
                    <h2>Akses cepat</h2>
                    <p>Mulai aktivitas harian Anda</p>
                </div>
            </div>
            <div class="quick-grid">
                @foreach ([['assets.create', 'ri-add-circle-line', 'Tambah aset'], ['categories.index', 'ri-stack-line', 'Kategori'], ['movements.create', 'ri-arrow-left-right-line', 'Mutasi aset'], ['schedules.create', 'ri-calendar-check-line', 'Buat jadwal']] as [$route, $icon, $label])
                    @if (auth()->user()->can(str_ends_with($route, '.create') ? 'module-manage' : 'module-view', explode('.', $route)[0]))
                        <a href="{{ route($route) }}" wire:key="quick-{{ $route }}">
                            <i aria-hidden="true" class="{{ $icon }}"></i>
                            <span>{{ $label }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    </div>
</div>
