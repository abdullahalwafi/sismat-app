<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Supplier;
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

class Suppliers extends Component
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
        Gate::authorize('module-view', 'suppliers');
        $this->mode = match (true) {
            request()->routeIs('suppliers.create') => 'create',
            request()->routeIs('suppliers.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'suppliers');
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
        if (! in_array($field, ['id', 'code', 'name', 'contact', 'phone', 'email'], true)) {
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
        Gate::authorize('module-manage', 'suppliers');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'suppliers');
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
        $record = Supplier::query()->findOrFail($id);

        return [
            'code' => $record->code ?? '',
            'name' => $record->name ?? '',
            'contact' => $record->contact_person ?? '',
            'phone' => $record->phone ?? '',
            'email' => $record->email ?? '',
            'address' => $record->address ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'contact' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:30', Rule::unique('suppliers', 'code')->ignore($this->recordId)],
            'form.name' => ['required', 'string', 'max:100'],
            'form.contact' => ['required', 'string', 'max:100'],
            'form.phone' => ['required', 'string', 'max:20'],
            'form.email' => ['required', 'email', 'max:255'],
            'form.address' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode supplier',
            'form.name' => 'Nama perusahaan',
            'form.contact' => 'Nama kontak',
            'form.phone' => 'Nomor telepon',
            'form.email' => 'Email',
            'form.address' => 'Alamat',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'suppliers');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? Supplier::query()->lockForUpdate()->findOrFail($this->recordId) : new Supplier;
                $before = $record->getAttributes();
                $attributes = [
                    'code' => $values['code'] ?? null,
                    'name' => $values['name'] ?? null,
                    'contact_person' => $values['contact'] ?? null,
                    'phone' => $values['phone'] ?? null,
                    'email' => $values['email'] ?? null,
                    'address' => $values['address'] ?? null,
                ];
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Supplier', $record, $before);

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
            $this->redirectRoute('suppliers.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'suppliers');
        Supplier::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'suppliers');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = Supplier::query()->lockForUpdate()->findOrFail($this->deleteId);
                if ($record->assets()->withTrashed()->exists()) {
                    throw ValidationException::withMessages(['delete' => 'Data masih digunakan oleh modul lain atau merupakan akun aktif Anda.']);
                }
                ActivityLog::record('Menghapus', 'Supplier', $record, $record->getAttributes());
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

    protected function displayRecord(Supplier $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->code ?? '',
            'name' => $record->name ?? '',
            'contact' => $record->contact_person ?? '',
            'phone' => $record->phone ?? '',
            'email' => $record->email ?? '',
            'address' => $record->address ?? '',
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'suppliers');
        $query = Supplier::query();
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('code', 'like', '%'.$this->search.'%');
                $query->orWhere('name', 'like', '%'.$this->search.'%');
                $query->orWhere('contact_person', 'like', '%'.$this->search.'%');
                $query->orWhere('phone', 'like', '%'.$this->search.'%');
                $query->orWhere('email', 'like', '%'.$this->search.'%');
                $query->orWhere('address', 'like', '%'.$this->search.'%');
            });
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'code',
            'name' => 'name',
            'contact' => 'contact_person',
            'phone' => 'phone',
            'email' => 'email',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (Supplier $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(Supplier::query()->findOrFail($this->recordId)) : null;

        return view('livewire.suppliers', [
            'items' => $items,
            'record' => $record,
            'module' => 'suppliers',
        ])->layout('layouts.app', ['title' => 'Supplier']);
    }
}
