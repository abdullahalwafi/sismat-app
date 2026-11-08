<div>
    <x-page-heading title="Aktivitas" description="Jejak perubahan dan aktivitas pengguna di aplikasi." />
    <section class="panel">
        <div class="table-toolbar">
            <div class="search-field">
                <i aria-hidden="true" class="ri-search-line"></i>
                <input type="search" aria-label="Cari aktivitas" wire:model.live.debounce.300ms="search"
                    placeholder="Cari pengguna atau aktivitas...">
            </div>
            <div class="toolbar-filters">
                <select wire:model.live="action" aria-label="Filter jenis aktivitas">
                    <option value="">Semua aktivitas</option>
                    @foreach ($actions as $item)
                        <option value="{{ $item }}" wire:key="action-{{ $loop->index }}">{{ $item }}</option>
                    @endforeach
                </select>
                <select wire:model.live="moduleFilter" aria-label="Filter modul">
                    <option value="">Semua modul</option>
                    @foreach ($modules as $item)
                        <option value="{{ $item }}" wire:key="module-{{ $loop->index }}">{{ $item }}</option>
                    @endforeach
                </select>
                <button type="button" class="icon-button" wire:click="clearFilters" aria-label="Reset filter">
                    <i aria-hidden="true" class="ri-refresh-line"></i>
                </button>
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pengguna
                        </th>
                        <th>Jenis aktivitas
                        </th>
                        <th>Modul
                        </th>
                        <th>Keterangan
                        </th>
                        <th>Waktu (WIB)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $activity)
                        <tr wire:key="activity-{{ $activity['id'] }}">
                            <td>
                                <div class="name-cell">
                                    <span class="avatar initials">{{ mb_substr($activity['user'], 0, 1) }}</span>
                                    <strong>{{ $activity['user'] }}</strong>
                                </div>
                            </td>
                            <td>
                                <x-badge :value="$activity['action']" />
                            </td>
                            <td>{{ $activity['module'] }}
                            </td>
                            <td>{{ $activity['description'] }}
                            </td>
                            <td>{{ $activity['date'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-inline">Tidak ada aktivitas yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Riwayat aktivitas tersimpan</span>{{ $items->links('livewire.partials.pagination') }}
        </div>
    </section>
</div>
