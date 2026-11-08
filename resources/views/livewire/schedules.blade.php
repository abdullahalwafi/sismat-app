<div>
    <x-page-heading title="Jadwal Pemeliharaan" description="Rencanakan perawatan berkala agar aset selalu siap digunakan.">
        @if ($mode === 'index')
            @can('module-manage', 'schedules')
                <button type="button" class="button button-primary" wire:click="create">
                    <i class="ri-add-line" aria-hidden="true"></i> Tambah Jadwal</button>
            @endcan
        @else
            <a href="{{ route('schedules.index') }}" class="button button-secondary">Kembali</a>
            @if ($mode === 'show')
                @can('module-manage', 'schedules')
                    <a href="{{ route('schedules.edit', $recordId) }}" class="button button-primary">Edit Jadwal</a>
                @endcan
            @endif
        @endif
    </x-page-heading>
    @if (in_array($mode, ['create', 'edit'], true))
        <section class="panel form-panel">
            <div class="panel-header">
                <h2>{{ $mode === 'create' ? 'Tambah' : 'Edit' }} Jadwal</h2>
            </div>
            <form wire:submit="save">
                @include('livewire.partials.schedules-form')
                <div class="form-footer">
                    <a href="{{ route('schedules.index') }}" class="button button-secondary">Batal</a>
                    <button type="submit" class="button button-primary" wire:loading.attr="disabled">Simpan Jadwal</button>
                </div>
            </form>
        </section>
    @elseif ($mode === 'show')
        @include('livewire.partials.schedules-detail')
    @else
        <div class="module-overview">
            <span class="surface-icon">
                <i class="ri-calendar-check-line" aria-hidden="true"></i>
            </span>
            <div>
                <strong>{{ $items->total() }}</strong>
                <span>data sesuai pencarian dan filter</span>
            </div>
            <span class="overview-tag">Tersimpan di database</span>
        </div>
        <div class="tab-bar">
            <button type="button" wire:click="$set('tab', 'information')" @class(['tab-button', 'selected' => $tab === 'information'])>Daftar jadwal</button>
            <button type="button" wire:click="$set('tab', 'calendar')" @class(['tab-button', 'selected' => $tab === 'calendar'])>Kalender</button>
        </div>
        <section class="panel">
            <div class="table-toolbar">
                <div class="search-field">
                    <i class="ri-search-line" aria-hidden="true"></i>
                    <input type="search" aria-label="Cari Jadwal Pemeliharaan" wire:model.live.debounce.300ms="search" placeholder="Cari kode, nama, atau informasi lainnya...">
                </div>
                <div class="toolbar-filters">
                    <select aria-label="Filter Status" wire:model.live="filters.status">
                        <option value="">Semua status</option>
                        @foreach ($this->filterOptions('status') as $value => $label)
                            <option value="{{ $value }}" wire:key="filter-status-{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="icon-button reset-filter" wire:click="clearFilters" aria-label="Reset pencarian dan filter">
                        <i class="ri-refresh-line" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="loading-strip" wire:loading.delay role="status">Memperbarui data...</div>
            @if ($tab === 'calendar')
                @include('livewire.partials.calendar')
            @else
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>
                                    <button type="button" wire:click="sortBy('code')">Kode jadwal<i class="{{ $sortField === 'code' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" wire:click="sortBy('asset')">Aset<i class="{{ $sortField === 'asset' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" wire:click="sortBy('date')">Tanggal jadwal<i class="{{ $sortField === 'date' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" wire:click="sortBy('technician')">Teknisi<i class="{{ $sortField === 'technician' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" wire:click="sortBy('status')">Status<i class="{{ $sortField === 'status' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th class="actions-column">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr wire:key="schedules-{{ $item['id'] }}">
                                    <td>
                                        <a class="code-link" href="{{ route('schedules.show', $item['id']) }}">{{ $item['code'] }}</a>
                                    </td>
                                    <td>
                                        <div class="asset-reference">
                                            <strong>{{ $item['asset'] }}</strong>
                                            <small>{{ $item['asset_name'] }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $item['date'] ?? '—' }}</td>
                                    <td>{{ $item['technician'] ?? '—' }}</td>
                                    <td>
                                        <x-badge :value="$item['status']" />
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="action-icon" href="{{ route('schedules.show', $item['id']) }}" aria-label="Detail {{ $item['code'] }}" title="Detail">
                                                <i class="ri-eye-line" aria-hidden="true"></i>
                                            </a>
                                            @can('module-manage', 'schedules')
                                                <button type="button" class="action-icon edit" wire:click="edit({{ $item['id'] }})" aria-label="Edit {{ $item['code'] }}" title="Edit">
                                                    <i class="ri-edit-line" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" class="action-icon danger" wire:click="confirmDelete({{ $item['id'] }})" aria-label="Hapus {{ $item['code'] }}" title="Hapus">
                                                    <i class="ri-delete-bin-line" aria-hidden="true"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="ri-search-eye-line" aria-hidden="true"></i>
                                            <h3>Data tidak ditemukan</h3>
                                            <p>Coba ubah kata kunci atau reset filter Anda.</p>
                                            <button type="button" class="button button-secondary" wire:click="clearFilters">Reset filter</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <div class="page-size">
                        <span>Tampilkan</span>
                        <select wire:model.live="perPage" aria-label="Jumlah data per halaman">
                            <option value="8">8</option>
                            <option value="16">16</option>
                            <option value="32">32</option>
                        </select>
                        <span>per halaman</span>
                    </div>
                    {{ $items->links('livewire.partials.pagination') }}
                </div>
            @endif
        </section>
    @endif
    @if ($showForm)
        <x-modal :title="($recordId ? 'Edit ' : 'Tambah ') . 'Jadwal'" :wide="true">
        <form wire:submit="save">
            <div class="modal-body">@include('livewire.partials.schedules-form')</div>
            <div class="modal-footer">
                <button type="button" class="button button-secondary" wire:click="closeForm">Batal</button>
                <button type="submit" class="button button-primary" wire:loading.attr="disabled">Simpan data</button>
            </div>
        </form>
        </x-modal>
    @endif
    @if ($deleteId !== null)
        <x-modal title="Hapus data?" close="cancelDelete">
        <div class="modal-body">
            <div class="delete-symbol">
                <i class="ri-delete-bin-line" aria-hidden="true"></i>
            </div>
            <p>Anda akan menghapus Jadwal #{{ $deleteId }} dari database. Data yang masih digunakan tidak dapat dihapus.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-secondary" wire:click="cancelDelete">Batal</button>
            <button type="button" class="button button-danger" wire:click="delete" wire:loading.attr="disabled">Ya, hapus data</button>
        </div>
        </x-modal>
    @endif
</div>
