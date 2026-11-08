<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Role;
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

class Roles extends Component
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
        Gate::authorize('module-view', 'roles');
        $this->mode = match (true) {
            request()->routeIs('roles.create') => 'create',
            request()->routeIs('roles.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'roles');
        }
        $this->recordId = $record;
        if ($record !== null) {
            $this->form = $this->formValues($record);
        } elseif ($this->mode === 'create') {
            $this->prepareForm();
        }
    }

    public function updated(string $property): void
    {
        if ($property === 'search' || str_starts_with($property, 'filters.') || $property === 'perPage') {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'code', 'description'], true)) {
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
        Gate::authorize('module-manage', 'roles');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'roles');
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
        $record = Role::query()->findOrFail($id);

        return [
            'code' => $record->name ?? '',
            'description' => $record->description ?? '',
            'permissions' => $record->permissions ?? [],
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'description' => '',
            'permissions' => [],
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($this->recordId)],
            'form.description' => ['nullable', 'string', 'max:2000'],
            'form.permissions' => ['nullable', 'array'],
            'form.permissions.*' => ['string', Rule::in(['activities:view', 'assets:manage', 'assets:view', 'buildings:manage', 'buildings:view', 'categories:manage', 'categories:view', 'disposals:approve', 'disposals:manage', 'disposals:view', 'documents:manage', 'documents:view', 'maintenances:manage', 'maintenances:view', 'movements:approve', 'movements:manage', 'movements:view', 'reports:view', 'roles:manage', 'roles:view', 'rooms:manage', 'rooms:view', 'schedules:manage', 'schedules:view', 'suppliers:manage', 'suppliers:view', 'users:manage', 'users:view'])],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Nama role',
            'form.description' => 'Deskripsi',
            'form.permissions' => 'Hak akses',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'roles');
        $this->form['code'] = trim($this->form['code'] ?? '');
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? Role::query()->lockForUpdate()->findOrFail($this->recordId) : new Role;
                $before = $record->getAttributes();
                if ($record->exists && in_array($record->name, ['Super Admin', 'Admin Aset', 'Teknisi', 'Petugas Ruangan', 'Pimpinan'], true) && $values['code'] !== $record->name) {
                    throw ValidationException::withMessages(['form.code' => 'Nama role bawaan tidak dapat diubah; buat role baru untuk kebutuhan tambahan.']);
                }
                $attributes = [
                    'name' => $values['code'] ?? null,
                    'description' => $values['description'] ?? null,
                    'permissions' => $values['permissions'] ?? null,
                ];
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Role & Hak Akses', $record, $before);

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
            $this->redirectRoute('roles.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'roles');
        Role::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'roles');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = Role::query()->lockForUpdate()->findOrFail($this->deleteId);
                if ($record->users()->exists() || $record->name === 'Super Admin') {
                    throw ValidationException::withMessages(['delete' => 'Data masih digunakan oleh modul lain atau merupakan akun aktif Anda.']);
                }
                ActivityLog::record('Menghapus', 'Role & Hak Akses', $record, $record->getAttributes());
                $record->delete();
            });
            $this->notify('Data berhasil dihapus.');
        } catch (ValidationException $exception) {
            $this->notify(collect($exception->errors())->flatten()->first(), 'error');
        }
        $this->deleteId = null;
        $this->resetPage();
    }

    protected function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', message: $message, type: $type);
    }

    protected function displayRecord(Role $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->name ?? '',
            'description' => $record->description ?? '',
            'permissions' => $record->permissions ?? '',
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'roles');
        $query = Role::query();
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('name', 'like', '%'.$this->search.'%');
                $query->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'name',
            'description' => 'description',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (Role $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(Role::query()->findOrFail($this->recordId)) : null;

        return view('livewire.roles', [
            'items' => $items,
            'record' => $record,
            'module' => 'roles',
        ])->layout('layouts.app', ['title' => 'Role & Hak Akses']);
    }
}
