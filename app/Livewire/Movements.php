<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetMovement;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Movements extends Component
{
    use WithPagination;

    public string $search = '';

    public array $filters = [];

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public int $perPage = 8;

    public array $form = [];

    public bool $showForm = false;

    public ?int $deleteId = null;

    #[Locked]
    public ?int $recordId = null;

    #[Locked]
    public string $mode = 'index';

    public function mount(?int $record = null): void
    {
        Gate::authorize('module-view', 'movements');
        $this->mode = match (true) {
            request()->routeIs('movements.create') => 'create',
            request()->routeIs('movements.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'movements');
        }
        $this->recordId = $record;
        if ($record !== null) {
            $this->form = $this->formValues($record);
        } elseif ($this->mode === 'create') {
            $this->prepareForm();
        }
    }

    public function options(string $field): array
    {
        return match ($field) {
            'asset' => Asset::query()
                ->orderBy('asset_code')
                ->get()->mapWithKeys(fn ($asset): array => [(string) $asset->id => $asset->asset_code.' · '.$asset->name])->all(),
            'origin' => Room::query()
                ->orderBy('code')
                ->get()->mapWithKeys(fn ($room): array => [(string) $room->id => $room->building->name.' · '.$room->code.' · '.$room->name])->all(),
            'destination' => Room::query()
                ->orderBy('code')
                ->get()->mapWithKeys(fn ($room): array => [(string) $room->id => $room->building->name.' · '.$room->code.' · '.$room->name])->all(),
            'status' => array_combine(['Diajukan', 'Disetujui', 'Selesai', 'Ditolak'], ['Diajukan', 'Disetujui', 'Selesai', 'Ditolak']),
            default => [],
        };
    }

    public function filterOptions(string $field): array
    {
        return $this->options($field);
    }

    public function updated(string $property): void
    {
        if ($property === 'search' || str_starts_with($property, 'filters.') || $property === 'perPage') {
            $this->resetPage();
        }
        if ($property === 'form.asset') {
            $this->form['origin'] = (string) (Asset::query()->find($this->form['asset'])?->room_id ?? '');
        }
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'code', 'asset', 'origin', 'destination', 'date', 'status'], true)) {
            return;
        }
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'filters');
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('module-manage', 'movements');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'movements');
        $this->form = $this->formValues($id);
        $this->recordId = $id;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    protected function formValues(int $id): array
    {
        $record = AssetMovement::query()->with(['asset', 'fromRoom', 'toRoom', 'mover'])->findOrFail($id);

        return [
            'code' => $record->movement_code ?? '',
            'asset' => $record->asset_id === null ? '' : (string) $record->asset_id,
            'origin' => $record->from_room_id === null ? '' : (string) $record->from_room_id,
            'destination' => $record->to_room_id === null ? '' : (string) $record->to_room_id,
            'date' => $record->movement_date ?? '',
            'status' => $record->status ?? '',
            'description' => $record->reason ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'asset' => '',
            'origin' => '',
            'destination' => '',
            'date' => now()->timezone('Asia/Jakarta')->format('Y-m-d'),
            'status' => 'Diajukan',
            'description' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', Rule::unique('asset_movements', 'movement_code')->ignore($this->recordId)],
            'form.asset' => ['required', Rule::in(array_keys($this->options('asset')))],
            'form.origin' => ['required', Rule::in(array_keys($this->options('origin')))],
            'form.destination' => ['required', Rule::in(array_keys($this->options('destination')))],
            'form.date' => ['required', 'date_format:Y-m-d'],
            'form.description' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode mutasi',
            'form.asset' => 'Aset',
            'form.origin' => 'Lokasi asal',
            'form.destination' => 'Lokasi tujuan',
            'form.date' => 'Tanggal mutasi',
            'form.status' => 'Status',
            'form.description' => 'Alasan perpindahan',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'movements');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? AssetMovement::query()->lockForUpdate()->findOrFail($this->recordId) : new AssetMovement;
                $before = $record->getAttributes();
                if ($record->exists && $record->status !== 'Diajukan') {
                    throw ValidationException::withMessages(['form.status' => 'Pengajuan yang sudah diproses tidak dapat diedit.']);
                }
                if ($record->exists && auth()->user()->role?->name === 'Petugas Ruangan' && (int) $record->moved_by !== auth()->id()) {
                    abort(403);
                }
                $attributes = [
                    'movement_code' => $values['code'] ?? null,
                    'asset_id' => $values['asset'] ?? null,
                    'from_room_id' => $values['origin'] ?? null,
                    'to_room_id' => $values['destination'] ?? null,
                    'movement_date' => $values['date'] ?? null,
                    'reason' => $values['description'] ?? null,
                ];
                $asset = Asset::query()->lockForUpdate()->findOrFail($values['asset']);
                if ($asset->status === 'Dihapuskan') {
                    throw ValidationException::withMessages(['form.asset' => 'Aset telah dihapuskan secara operasional.']);
                }
                if ((int) $values['origin'] !== (int) $asset->room_id || (int) $values['origin'] === (int) $values['destination']) {
                    throw ValidationException::withMessages(['form.origin' => 'Lokasi asal harus sesuai lokasi aset dan berbeda dari tujuan.']);
                }
                if (! $record->exists) {
                    $attributes['moved_by'] = auth()->id();
                    $attributes['status'] = 'Diajukan';
                }
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Mutasi Aset', $record, $before);

                return $record->id;
            });
        } catch (QueryException $exception) {
            if (! str_contains(mb_strtolower($exception->getMessage()), 'unique')) {
                throw $exception;
            }
            $this->addError('form.code', 'Kode atau email sudah digunakan.');

            return;
        }
        $this->closeForm();
        $this->notify('Data berhasil disimpan ke database.');
        if (in_array($this->mode, ['create', 'edit'], true)) {
            session()->flash('success', 'Data berhasil disimpan ke database.');
            $this->redirectRoute('movements.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'movements');
        AssetMovement::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'movements');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = AssetMovement::query()->lockForUpdate()->findOrFail($this->deleteId);
                if ($record->exists && $record->status !== 'Diajukan') {
                    throw ValidationException::withMessages(['form.status' => 'Pengajuan yang sudah diproses tidak dapat diedit.']);
                }
                if ($record->exists && auth()->user()->role?->name === 'Petugas Ruangan' && (int) $record->moved_by !== auth()->id()) {
                    abort(403);
                }
                ActivityLog::record('Menghapus', 'Mutasi Aset', $record, $record->getAttributes());
                $record->delete();
            });
            $this->notify('Data berhasil dihapus.');
        } catch (ValidationException $exception) {
            $this->notify(collect($exception->errors())->flatten()->first(), 'error');
        }
        $this->deleteId = null;
        $this->resetPage();
    }

    public function changeStatus(int $id, string $status): void
    {
        try {
            Gate::authorize('module-approve', 'movements');
            DB::transaction(function () use ($id, $status): void {
                $record = AssetMovement::query()->lockForUpdate()->findOrFail($id);
                $before = $record->getAttributes();
                $pending = 'Diajukan';
                if (! ($record->status === $pending && in_array($status, ['Disetujui', 'Ditolak'], true)
                    || $record->status === 'Disetujui' && $status === 'Selesai')) {
                    throw ValidationException::withMessages(['status' => 'Perubahan status tidak diizinkan.']);
                }
                $asset = Asset::query()->lockForUpdate()->findOrFail($record->asset_id);
                if ($asset->status === 'Dihapuskan') {
                    throw ValidationException::withMessages(['status' => 'Aset telah dihapuskan secara operasional.']);
                }
                if ($status === 'Disetujui') {
                    $record->approved_by = auth()->id();
                    $record->approved_at = now();
                }
                if ($status === 'Selesai') {
                    if ((int) $asset->room_id !== (int) $record->from_room_id) {
                        throw ValidationException::withMessages(['status' => 'Lokasi aset telah berubah. Periksa kembali pengajuan.']);
                    }
                    $destination = Room::query()->findOrFail($record->to_room_id);
                    if ($destination->status !== 'Aktif') {
                        throw ValidationException::withMessages(['status' => 'Ruangan tujuan tidak aktif.']);
                    }
                    $asset->update(['room_id' => $record->to_room_id]);
                }
                $record->status = $status;
                $record->save();
                ActivityLog::record('Mengubah status', 'Mutasi Aset', $record, $before);
            });
            $this->notify('Status pengajuan berhasil diperbarui.');
        } catch (ValidationException $exception) {
            $this->notify(collect($exception->errors())->flatten()->first(), 'error');
        }
    }

    protected function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', message: $message, type: $type);
    }

    protected function displayRecord(AssetMovement $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->movement_code ?? '',
            'asset' => $record->asset?->asset_code ?? '—',
            'origin' => $record->fromRoom?->name ?? '—',
            'destination' => $record->toRoom?->name ?? '—',
            'date' => $record->movement_date ?? '',
            'status' => $record->status ?? '',
            'description' => $record->reason ?? '',
            'officer' => $record->mover?->name ?? '—',
            'asset_name' => $record->asset?->name ?? 'Aset tidak tersedia',
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'movements');
        $query = AssetMovement::query()->with(['asset', 'fromRoom', 'toRoom', 'mover']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('movement_code', 'like', '%'.$this->search.'%');
                $query->orWhere('movement_date', 'like', '%'.$this->search.'%');
                $query->orWhere('status', 'like', '%'.$this->search.'%');
                $query->orWhere('reason', 'like', '%'.$this->search.'%');
                $query->orWhereHas('asset', fn (Builder $related) => $related->where('asset_code', 'like', '%'.$this->search.'%'));
                $query->orWhereHas('fromRoom', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
                $query->orWhereHas('toRoom', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'movement_code',
            'asset' => Asset::query()->select('asset_code')->whereColumn('assets.id', 'asset_movements.asset_id')->limit(1),
            'origin' => Room::query()->select('name')->whereColumn('rooms.id', 'asset_movements.from_room_id')->limit(1),
            'destination' => Room::query()->select('name')->whereColumn('rooms.id', 'asset_movements.to_room_id')->limit(1),
            'date' => 'movement_date',
            'status' => 'status',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (AssetMovement $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(AssetMovement::query()->with(['asset', 'fromRoom', 'toRoom', 'mover'])->findOrFail($this->recordId)) : null;

        return view('livewire.movements', [
            'items' => $items,
            'record' => $record,
            'module' => 'movements',
            'movements' => $this->mode === 'show' ? AssetMovement::query()->with(['asset', 'fromRoom', 'toRoom', 'mover'])->get()->map(fn (AssetMovement $record): array => [
                'id' => $record->id,
                'code' => $record->movement_code ?? '',
                'asset' => $record->asset?->asset_code ?? '—',
                'origin' => $record->fromRoom?->name ?? '—',
                'destination' => $record->toRoom?->name ?? '—',
                'date' => $record->movement_date ?? '',
                'status' => $record->status ?? '',
                'description' => $record->reason ?? '',
                'officer' => $record->mover?->name ?? '—',
                'asset_name' => $record->asset?->name ?? 'Aset tidak tersedia',
            ]) : collect(),
        ])->layout('layouts.app', ['title' => 'Mutasi Aset']);
    }
}
