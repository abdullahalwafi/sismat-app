<div>
    <x-page-heading title="Laporan Aset" description="Ringkasan inventaris untuk evaluasi dan pengambilan keputusan.">
        <button type="button" class="button button-secondary" wire:click="export('Excel')">
            <i aria-hidden="true" class="ri-file-excel-2-line"></i> Excel (CSV)
        </button>
        <button type="button" class="button button-secondary" wire:click="export('PDF')">
            <i aria-hidden="true" class="ri-file-pdf-2-line"></i> PDF
        </button>
        <button type="button" class="button button-primary" @click="window.print()">
            <i aria-hidden="true" class="ri-printer-line"></i> Cetak laporan
        </button>
    </x-page-heading>
    <section class="panel report-filters">
        <div>
            <label for="start-date">Perolehan mulai
            </label>
            <input id="start-date" type="date" wire:model.live="startDate">
            @error('startDate')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div>
            <label for="end-date">Perolehan sampai
            </label>
            <input id="end-date" type="date" wire:model.live="endDate">
            @error('endDate')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <button type="button" class="button button-secondary" wire:click="clearPeriod">Reset periode
        </button>
        <span>
            <i aria-hidden="true" class="ri-information-line"></i> Periode berdasarkan tanggal perolehan aset.</span>
    </section>
    <div class="stats-grid three">
        <x-stat-card label="Jumlah aset" :value="$assets->count()" icon="ri-archive-stack-line" note="Sesuai periode terpilih" />
        <x-stat-card label="Nilai perolehan" :value="'Rp ' . number_format($assets->sum('price'), 0, ',', '.')" icon="ri-wallet-3-line" note="Total harga perolehan" />
        <x-stat-card label="Kondisi baik" :value="$assets->where('condition', 'Baik')->count()" icon="ri-shield-check-line" tone="green" note="Aset dengan kondisi baik" />
    </div>
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2>Ringkasan laporan inventaris</h2>
                <p>RS UMMI · {{ $startDate ?: 'Semua tanggal' }} — {{ $endDate ?: 'Sekarang' }}</p>
            </div>
            <span class="demo-chip">Data database</span>
        </div>
        <div class="tab-bar">
            @foreach (['category' => 'Kategori', 'condition' => 'Kondisi', 'room' => 'Lokasi'] as $key => $label)
                <button type="button" wire:click="$set('groupBy','{{ $key }}')" @class(['tab-button', 'selected' => $groupBy === $key])
                    wire:key="report-tab-{{ $key }}">Berdasarkan {{ $label }}
                </button>
            @endforeach
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ ['category' => 'Kategori', 'condition' => 'Kondisi', 'room' => 'Ruangan'][$groupBy] ?? 'Kategori' }}
                        </th>
                        <th>Jumlah aset
                        </th>
                        <th>Proporsi
                        </th>
                        <th>Nilai perolehan
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $name=>$items)
                        <tr wire:key="report-{{ $loop->index }}">
                            <td>
                                <strong>{{ $name }}</strong>
                            </td>
                            <td>{{ $items->count() }} aset
                            </td>
                            <td>
                                <div class="report-bar">
                                    <span style="width:{{ $assets->count() ? ($items->count() / $assets->count()) * 100 : 0 }}%">
                                    </span>
                                </div>
                                <small>{{ $assets->count() ? round(($items->count() / $assets->count()) * 100, 1) : 0 }}%</small>
                            </td>
                            <td>Rp {{ number_format($items->sum('price'), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-inline">Tidak ada data dalam periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total
                        </td>
                        <td>{{ $assets->count() }} aset
                        </td>
                        <td>{{ $assets->isEmpty() ? 0 : 100 }}%
                        </td>
                        <td>Rp {{ number_format($assets->sum('price'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="report-signature">Dicetak pada {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB ·
            SISMAT · Data database
        </div>
    </section>
</div>
