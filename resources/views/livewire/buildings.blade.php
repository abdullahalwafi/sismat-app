<div>
    <x-page-heading title="Gedung" description="Informasi gedung dan lokasi fasilitas rumah sakit.">
        @if ($mode === 'index')
            @can('module-manage', 'buildings')
                <button type="button" class="button button-primary" wire:click="create">
                    <i class="ri-add-line" aria-hidden="true"></i> Tambah Gedung</button>
            @endcan
        @else
            <a href="{{ route('buildings.index') }}" class="button button-secondary">Kembali</a>
            @if ($mode === 'show')
                @can('module-manage', 'buildings')
                    <a href="{{ route('buildings.edit', $recordId) }}" class="button button-primary">Edit Gedung</a>
                @endcan
            @endif
        @endif
    </x-page-heading>
    @if (in_array($mode, ['create', 'edit'], true))
        <section class="panel form-panel">
            <div class="panel-header">
                <h2>{{ $mode === 'create' ? 'Tambah' : 'Edit' }} Gedung</h2>
            </div>
            <form wire:submit="save">
                @include('livewire.partials.buildings-form')
                <div class="form-footer">
                    <a href="{{ route('buildings.index') }}" class="button button-secondary">Batal</a>
                    <button type="submit" class="button button-primary" wire:loading.attr="disabled">Simpan Gedung</button>
                </div>
            </form>
        </section>
    @elseif ($mode === 'show')
        @include('livewire.partials.buildings-detail')
    @else
        <div class="module-overview">
            <span class="surface-icon">
                <i class="ri-building-2-line" aria-hidden="true"></i>
            </span>
            <div>
                <strong>{{ $items->total() }}</strong>
                <span>data sesuai pencarian dan filter</span>
            </div>
            <span class="overview-tag">Tersimpan di database</span>
        </div>
        <section class="panel">
            <div class="table-toolbar">
                <div class="search-field">
                    <i class="ri-search-line" aria-hidden="true"></i>
                    <input type="search" aria-label="Cari Gedung" wire:model.live.debounce.300ms="search" placeholder="Cari kode, nama, atau informasi lainnya...">
                </div>
                <div class="toolbar-filters">
                    <button type="button" class="icon-button reset-filter" wire:click="clearFilters" aria-label="Reset pencarian dan filter">
                        <i class="ri-refresh-line" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="loading-strip" wire:loading.delay role="status">Memperbarui data...</div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
                                <button type="button" wire:click="sortBy('code')">Kode gedung<i class="{{ $sortField === 'code' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" wire:click="sortBy('name')">Nama gedung<i class="{{ $sortField === 'name' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" wire:click="sortBy('address')">Lokasi / alamat<i class="{{ $sortField === 'address' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th>
                                <button type="button" wire:click="sortBy('floors')">Jumlah lantai<i class="{{ $sortField === 'floors' ? ($sortDirection === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc') : 'ri-expand-up-down-line' }}" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th class="actions-column">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr wire:key="buildings-{{ $item['id'] }}">
                                <td>
                                    <a class="code-link" href="{{ route('buildings.show', $item['id']) }}">{{ $item['code'] }}</a>
                                </td>
                                <td>
                                    <div class="name-cell">
                                        <span class="item-icon">
                                            <i class="ri-building-2-line" aria-hidden="true"></i>
                                        </span>
                                        <div>
                                            <strong>{{ $item['name'] }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item['address'] ?? '—' }}</td>
                                <td>{{ $item['floors'] ?? '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="action-icon" href="{{ route('buildings.show', $item['id']) }}" aria-label="Detail {{ $item['code'] }}" title="Detail">
                                            <i class="ri-eye-line" aria-hidden="true"></i>
                                        </a>
                                        @can('module-manage', 'buildings')
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
                                <td colspan="5">
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
        </section>
    @endif
    @if ($showForm)
        <x-modal :title="($recordId ? 'Edit ' : 'Tambah ') . 'Gedung'" :wide="true">
        <form wire:submit="save">
            <div class="modal-body">@include('livewire.partials.buildings-form')</div>
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
            <p>Anda akan menghapus Gedung #{{ $deleteId }} dari database. Data yang masih digunakan tidak dapat dihapus.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-secondary" wire:click="cancelDelete">Batal</button>
            <button type="button" class="button button-danger" wire:click="delete" wire:loading.attr="disabled">Ya, hapus data</button>
        </div>
        </x-modal>
    @endif
</div>
