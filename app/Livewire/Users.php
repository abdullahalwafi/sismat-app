<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\AssetDocument;
use App\Models\AssetMovement;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\Role;
use App\Models\User;
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

class Users extends Component
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
        Gate::authorize('module-view', 'users');
        $this->mode = match (true) {
            request()->routeIs('users.create') => 'create',
            request()->routeIs('users.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'users');
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
            'role' => Role::query()->orderBy('name')->pluck('name', 'id')->all(),
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
        if (! in_array($field, ['id', 'code', 'name', 'role', 'phone', 'status'], true)) {
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
        Gate::authorize('module-manage', 'users');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'users');
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
        $record = User::query()->with(['role'])->findOrFail($id);

        return [
            'code' => $record->email ?? '',
            'name' => $record->name ?? '',
            'role' => $record->role_id === null ? '' : (string) $record->role_id,
            'phone' => $record->phone ?? '',
            'status' => $record->is_active ? 'Aktif' : 'Tidak aktif',
            'password' => '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'role' => '',
            'phone' => '',
            'status' => '',
            'password' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->recordId)],
            'form.name' => ['required', 'string', 'max:100'],
            'form.role' => ['required', Rule::in(array_keys($this->options('role')))],
            'form.phone' => ['nullable', 'string', 'max:20'],
            'form.status' => ['required', Rule::in(array_keys($this->options('status')))],
            'form.password' => ['nullable', Rule::requiredIf($this->recordId === null), 'string', 'min:8', 'max:200'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Email',
            'form.name' => 'Nama pengguna',
            'form.role' => 'Role',
            'form.phone' => 'Telepon',
            'form.status' => 'Status akun',
            'form.password' => 'Password baru (kosongkan saat edit)',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'users');
        $this->form['code'] = mb_strtolower(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? User::query()->lockForUpdate()->findOrFail($this->recordId) : new User;
                $before = $record->getAttributes();
                $attributes = [
                    'email' => $values['code'] ?? null,
                    'name' => $values['name'] ?? null,
                    'role_id' => $values['role'] ?? null,
                    'phone' => $values['phone'] ?? null,
                    'is_active' => ($values['status'] ?? '') === 'Aktif',
                ];
                if ($record->id === auth()->id() && (! $attributes['is_active'] || (int) $attributes['role_id'] !== (int) $record->role_id)) {
                    throw ValidationException::withMessages(['form.status' => 'Anda tidak dapat menonaktifkan atau mengubah role akun sendiri.']);
                }
                if (! empty($values['password'])) {
                    $attributes['password'] = $values['password'];
                }
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Pengguna', $record, $before);

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
            $this->redirectRoute('users.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'users');
        User::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'users');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = User::query()->lockForUpdate()->findOrFail($this->deleteId);
                if ($record->id === auth()->id() || Asset::withTrashed()->where('created_by', $record->id)->exists()
                || Maintenance::query()->where('technician_id', $record->id)->orWhere('created_by', $record->id)->exists()
                || MaintenanceSchedule::query()->where('assigned_to', $record->id)->exists()
                || AssetDocument::query()->where('uploaded_by', $record->id)->exists()
                || AssetMovement::query()->where('moved_by', $record->id)->exists() || AssetDisposal::query()->where('requested_by', $record->id)->exists()) {
                    throw ValidationException::withMessages(['delete' => 'Data masih digunakan oleh modul lain atau merupakan akun aktif Anda.']);
                }
                ActivityLog::record('Menghapus', 'Pengguna', $record, $record->getAttributes());
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

    protected function displayRecord(User $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->email ?? '',
            'name' => $record->name ?? '',
            'role' => $record->role?->name ?? '—',
            'phone' => $record->phone ?? '',
            'status' => $record->is_active ? 'Aktif' : 'Tidak aktif',
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'users');
        $query = User::query()->with(['role']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('email', 'like', '%'.$this->search.'%');
                $query->orWhere('name', 'like', '%'.$this->search.'%');
                $query->orWhere('phone', 'like', '%'.$this->search.'%');
                $query->orWhere('is_active', 'like', '%'.$this->search.'%');
                $query->orWhereHas('role', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['role'])) {
            $query->where('role_id', $this->filters['role']);
        }
        if (! empty($this->filters['status'])) {
            $query->where('is_active', $this->filters['status'] === 'Aktif');
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'email',
            'name' => 'name',
            'role' => Role::query()->select('name')->whereColumn('roles.id', 'users.role_id')->limit(1),
            'phone' => 'phone',
            'status' => 'is_active',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (User $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(User::query()->with(['role'])->findOrFail($this->recordId)) : null;

        return view('livewire.users', [
            'items' => $items,
            'record' => $record,
            'module' => 'users',
        ])->layout('layouts.app', ['title' => 'Pengguna']);
    }
}
