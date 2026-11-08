<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Maintenance;
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

class Maintenances extends Component
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
        Gate::authorize('module-view', 'maintenances');
        $this->mode = match (true) {
            request()->routeIs('maintenances.create') => 'create',
            request()->routeIs('maintenances.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'maintenances');
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
            'type' => array_combine(['Preventive', 'Corrective'], ['Preventive', 'Corrective']),
            'technician' => User::query()
                ->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'Teknisi'))
                ->orderBy('name')->pluck('name', 'id')->all(),
            'status' => array_combine(['Pending', 'In Progress', 'Completed', 'Cancelled'], ['Pending', 'In Progress', 'Completed', 'Cancelled']),
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
        if (! in_array($field, ['id', 'code', 'asset', 'type', 'technician', 'date', 'status'], true)) {
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
        Gate::authorize('module-manage', 'maintenances');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'maintenances');
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
        $record = Maintenance::query()->with(['asset', 'technician'])->findOrFail($id);

        return [
            'code' => $record->maintenance_code ?? '',
            'asset' => $record->asset_id === null ? '' : (string) $record->asset_id,
            'type' => $record->maintenance_type ?? '',
            'technician' => $record->technician_id === null ? '' : (string) $record->technician_id,
            'date' => $record->maintenance_date ?? '',
            'cost' => $record->cost ?? '',
            'status' => $record->status ?? '',
            'description' => $record->description ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'asset' => '',
            'type' => '',
            'technician' => '',
            'date' => now()->timezone('Asia/Jakarta')->format('Y-m-d'),
            'cost' => 0,
            'status' => '',
            'description' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', Rule::unique('maintenances', 'maintenance_code')->ignore($this->recordId)],
            'form.asset' => ['required', Rule::in(array_keys($this->options('asset')))],
            'form.type' => ['required', Rule::in(array_keys($this->options('type')))],
            'form.technician' => ['required', Rule::in(array_keys($this->options('technician')))],
            'form.date' => ['required', 'date_format:Y-m-d'],
            'form.cost' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'form.status' => ['required', Rule::in(array_keys($this->options('status')))],
            'form.description' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode pekerjaan',
            'form.asset' => 'Aset',
            'form.type' => 'Jenis pemeliharaan',
            'form.technician' => 'Teknisi',
            'form.date' => 'Tanggal pemeliharaan',
            'form.cost' => 'Biaya (Rp)',
            'form.status' => 'Status pekerjaan',
            'form.description' => 'Hasil pemeriksaan / catatan',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'maintenances');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? Maintenance::query()->lockForUpdate()->findOrFail($this->recordId) : new Maintenance;
                $before = $record->getAttributes();
                $attributes = [
                    'maintenance_code' => $values['code'] ?? null,
                    'asset_id' => $values['asset'] ?? null,
                    'maintenance_type' => $values['type'] ?? null,
                    'technician_id' => $values['technician'] ?? null,
                    'maintenance_date' => $values['date'] ?? null,
                    'cost' => $values['cost'] ?? null,
                    'status' => $values['status'] ?? null,
                    'description' => $values['description'] ?? null,
                ];
                $asset = Asset::query()->lockForUpdate()->findOrFail($values['asset']);
                if ($asset->status === 'Dihapuskan') {
                    throw ValidationException::withMessages(['form.asset' => 'Aset telah dihapuskan secara operasional.']);
                }
                if (! $record->exists) {
                    $attributes['created_by'] = auth()->id();
                }
                $attributes['completed_at'] = $values['status'] === 'Completed' ? ($record->completed_at ?? now()) : null;
                $record->fill($attributes)->save();
                foreach (array_unique(array_filter([$record->asset_id, $before['asset_id'] ?? null])) as $assetId) {
                    $asset = Asset::query()->lockForUpdate()->findOrFail($assetId);
                    $running = Maintenance::query()->where('asset_id', $assetId)->where('status', 'In Progress')->exists();
                    if ($running && $asset->status !== 'Dihapuskan') {
                        $asset->update(['status' => 'Dalam pemeliharaan']);
                    } elseif (! $running && $asset->status === 'Dalam pemeliharaan') {
                        $asset->update(['status' => $asset->condition === 'Rusak berat' ? 'Tidak aktif' : 'Aktif']);
                    }
                }
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Pemeliharaan', $record, $before);

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
            $this->redirectRoute('maintenances.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'maintenances');
        Maintenance::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'maintenances');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = Maintenance::query()->lockForUpdate()->findOrFail($this->deleteId);
                if (in_array($record->status, ['In Progress', 'Completed'], true)) {
                    throw ValidationException::withMessages(['delete' => 'Data masih digunakan oleh modul lain atau merupakan akun aktif Anda.']);
                }
                ActivityLog::record('Menghapus', 'Pemeliharaan', $record, $record->getAttributes());
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

    protected function displayRecord(Maintenance $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->maintenance_code ?? '',
            'asset' => $record->asset?->asset_code ?? '—',
            'type' => $record->maintenance_type ?? '',
            'technician' => $record->technician?->name ?? '—',
            'date' => $record->maintenance_date ?? '',
            'cost' => $record->cost ?? '',
            'status' => $record->status ?? '',
            'description' => $record->description ?? '',
            'asset_name' => $record->asset?->name ?? 'Aset tidak tersedia',
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'maintenances');
        $query = Maintenance::query()->with(['asset', 'technician']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('maintenance_code', 'like', '%'.$this->search.'%');
                $query->orWhere('maintenance_type', 'like', '%'.$this->search.'%');
                $query->orWhere('maintenance_date', 'like', '%'.$this->search.'%');
                $query->orWhere('cost', 'like', '%'.$this->search.'%');
                $query->orWhere('status', 'like', '%'.$this->search.'%');
                $query->orWhere('description', 'like', '%'.$this->search.'%');
                $query->orWhereHas('asset', fn (Builder $related) => $related->where('asset_code', 'like', '%'.$this->search.'%'));
                $query->orWhereHas('technician', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['type'])) {
            $query->where('maintenance_type', $this->filters['type']);
        }
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'maintenance_code',
            'asset' => Asset::query()->select('asset_code')->whereColumn('assets.id', 'maintenances.asset_id')->limit(1),
            'type' => 'maintenance_type',
            'technician' => User::query()->select('name')->whereColumn('users.id', 'maintenances.technician_id')->limit(1),
            'date' => 'maintenance_date',
            'status' => 'status',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (Maintenance $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(Maintenance::query()->with(['asset', 'technician'])->findOrFail($this->recordId)) : null;

        return view('livewire.maintenances', [
            'items' => $items,
            'record' => $record,
            'module' => 'maintenances',
        ])->layout('layouts.app', ['title' => 'Pemeliharaan']);
    }
}
