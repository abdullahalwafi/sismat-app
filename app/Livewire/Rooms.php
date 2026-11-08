<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetMovement;
use App\Models\Building;
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

class Rooms extends Component
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
        Gate::authorize('module-view', 'rooms');
        $this->mode = match (true) {
            request()->routeIs('rooms.create') => 'create',
            request()->routeIs('rooms.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'rooms');
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
            'building' => Building::query()->orderBy('name')->pluck('name', 'id')->all(),
            'status' => array_combine(['Aktif', 'Tidak aktif'], ['Aktif', 'Tidak aktif']),
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
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'code', 'name', 'building', 'floor', 'status'], true)) {
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
        Gate::authorize('module-manage', 'rooms');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'rooms');
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
        $record = Room::query()->with(['building'])->findOrFail($id);

        return [
            'code' => $record->code ?? '',
            'name' => $record->name ?? '',
            'building' => $record->building_id === null ? '' : (string) $record->building_id,
            'floor' => $record->floor ?? '',
            'capacity' => $record->capacity ?? '',
            'status' => $record->status ?? '',
            'description' => $record->description ?? '',
            'department' => $record->department ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'building' => '',
            'floor' => '',
            'capacity' => 0,
            'status' => '',
            'description' => '',
            'department' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:30', Rule::unique('rooms', 'code')->ignore($this->recordId)->where('building_id', $this->form['building'] ?? null)],
            'form.name' => ['required', 'string', 'max:100'],
            'form.building' => ['required', Rule::in(array_keys($this->options('building')))],
            'form.floor' => ['required', 'string', 'max:20'],
            'form.capacity' => ['required', 'integer', 'min:0', 'max:65535'],
            'form.status' => ['required', Rule::in(array_keys($this->options('status')))],
            'form.description' => ['nullable', 'string', 'max:2000'],
            'form.department' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode ruangan',
            'form.name' => 'Nama ruangan',
            'form.building' => 'Gedung',
            'form.floor' => 'Lantai',
            'form.capacity' => 'Kapasitas aset',
            'form.status' => 'Status',
            'form.description' => 'Deskripsi',
            'form.department' => 'Unit / departemen',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'rooms');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? Room::query()->lockForUpdate()->findOrFail($this->recordId) : new Room;
                $before = $record->getAttributes();
                $attributes = [
                    'code' => $values['code'] ?? null,
                    'name' => $values['name'] ?? null,
                    'building_id' => $values['building'] ?? null,
                    'floor' => $values['floor'] ?? null,
                    'capacity' => $values['capacity'] ?? null,
                    'status' => $values['status'] ?? null,
                    'description' => $values['description'] ?? null,
                    'department' => $values['department'] ?? null,
                ];
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Ruangan', $record, $before);

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
            $this->redirectRoute('rooms.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'rooms');
        Room::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'rooms');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = Room::query()->lockForUpdate()->findOrFail($this->deleteId);
                if ($record->assets()->withTrashed()->exists() || AssetMovement::query()->where('from_room_id', $record->id)->orWhere('to_room_id', $record->id)->exists()) {
                    throw ValidationException::withMessages(['delete' => 'Data masih digunakan oleh modul lain atau merupakan akun aktif Anda.']);
                }
                ActivityLog::record('Menghapus', 'Ruangan', $record, $record->getAttributes());
                $record->delete();
            });
            $this->notify('Data berhasil dihapus.');
        } catch (ValidationException $exception) {
            $this->notify(collect($exception->errors())->flatten()->first(), 'error');
        }
        $this->deleteId = null;
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        Gate::authorize('module-manage', 'rooms');
        DB::transaction(function () use ($id): void {
            $record = Room::query()->lockForUpdate()->findOrFail($id);
            $before = $record->getAttributes();
            $record->update(['status' => $record->status === 'Aktif' ? 'Tidak aktif' : 'Aktif']);
            ActivityLog::record('Mengubah', 'Ruangan', $record, $before);
        });
        $this->notify('Status ruangan diperbarui.');
    }

    protected function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', message: $message, type: $type);
    }

    protected function displayRecord(Room $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->code ?? '',
            'name' => $record->name ?? '',
            'building' => $record->building?->name ?? '—',
            'floor' => $record->floor ?? '',
            'capacity' => $record->capacity ?? '',
            'status' => $record->status ?? '',
            'description' => $record->description ?? '',
            'department' => $record->department ?? '',
            'building_id' => $record->building_id,
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'rooms');
        $query = Room::query()->with(['building']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('code', 'like', '%'.$this->search.'%');
                $query->orWhere('name', 'like', '%'.$this->search.'%');
                $query->orWhere('floor', 'like', '%'.$this->search.'%');
                $query->orWhere('capacity', 'like', '%'.$this->search.'%');
                $query->orWhere('status', 'like', '%'.$this->search.'%');
                $query->orWhere('description', 'like', '%'.$this->search.'%');
                $query->orWhere('department', 'like', '%'.$this->search.'%');
                $query->orWhereHas('building', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['building'])) {
            $query->where('building_id', $this->filters['building']);
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'code',
            'name' => 'name',
            'building' => Building::query()->select('name')->whereColumn('buildings.id', 'rooms.building_id')->limit(1),
            'floor' => 'floor',
            'status' => 'status',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (Room $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(Room::query()->with(['building'])->findOrFail($this->recordId)) : null;

        return view('livewire.rooms', [
            'items' => $items,
            'record' => $record,
            'module' => 'rooms',
            'assets' => $this->mode === 'show' ? Asset::query()->with(['category', 'room', 'supplier', 'room.building'])->get()->map(fn (Asset $record): array => [
                'id' => $record->id,
                'code' => $record->asset_code ?? '',
                'name' => $record->name ?? '',
                'category' => $record->category?->name ?? '—',
                'brand' => $record->brand ?? '',
                'model' => $record->model ?? '',
                'serial_number' => $record->serial_number ?? '',
                'building' => $record->room?->building?->name ?? '—',
                'room' => $record->room?->name ?? '—',
                'acquired_at' => $record->acquisition_date ?? '',
                'price' => $record->acquisition_price ?? '',
                'condition' => $record->condition ?? '',
                'status' => $record->status ?? '',
                'description' => $record->description ?? '',
                'photo' => $record->photo_path ? route('assets.photo', $record->id) : '',
                'supplier' => $record->supplier?->name ?? '—',
                'useful_life_years' => $record->useful_life_years ?? '',
                'building_id' => $record->room?->building_id,
                'room_id' => $record->room_id,
            ]) : collect(),
        ])->layout('layouts.app', ['title' => 'Ruangan']);
    }
}
