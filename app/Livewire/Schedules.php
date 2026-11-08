<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\MaintenanceSchedule;
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

class Schedules extends Component
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

    public string $tab = 'information';

    public string $calendarMonth = '';

    public function mount(?int $record = null): void
    {
        Gate::authorize('module-view', 'schedules');
        $this->calendarMonth = now()->timezone('Asia/Jakarta')->format('Y-m');
        $this->mode = match (true) {
            request()->routeIs('schedules.create') => 'create',
            request()->routeIs('schedules.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'schedules');
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
            'technician' => User::query()
                ->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'Teknisi'))
                ->orderBy('name')->pluck('name', 'id')->all(),
            'type' => array_combine(['Preventive', 'Corrective'], ['Preventive', 'Corrective']),
            'status' => array_combine(['Terjadwal', 'Selesai', 'Dibatalkan'], ['Terjadwal', 'Selesai', 'Dibatalkan']),
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
        if (! in_array($field, ['id', 'code', 'asset', 'date', 'technician', 'status'], true)) {
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
        Gate::authorize('module-manage', 'schedules');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'schedules');
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
        $record = MaintenanceSchedule::query()->with(['asset', 'assignee'])->findOrFail($id);

        return [
            'code' => $record->schedule_code ?? '',
            'asset' => $record->asset_id === null ? '' : (string) $record->asset_id,
            'date' => $record->schedule_date ?? '',
            'technician' => $record->assigned_to === null ? '' : (string) $record->assigned_to,
            'type' => $record->maintenance_type ?? '',
            'status' => $record->status ?? '',
            'description' => $record->description ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'asset' => '',
            'date' => now()->timezone('Asia/Jakarta')->format('Y-m-d'),
            'technician' => '',
            'type' => '',
            'status' => '',
            'description' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', Rule::unique('maintenance_schedules', 'schedule_code')->ignore($this->recordId)],
            'form.asset' => ['required', Rule::in(array_keys($this->options('asset')))],
            'form.date' => ['required', 'date_format:Y-m-d'],
            'form.technician' => ['required', Rule::in(array_keys($this->options('technician')))],
            'form.type' => ['required', Rule::in(array_keys($this->options('type')))],
            'form.status' => ['required', Rule::in(array_keys($this->options('status')))],
            'form.description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode jadwal',
            'form.asset' => 'Aset',
            'form.date' => 'Tanggal jadwal',
            'form.technician' => 'Teknisi',
            'form.type' => 'Jenis',
            'form.status' => 'Status',
            'form.description' => 'Catatan',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'schedules');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? MaintenanceSchedule::query()->lockForUpdate()->findOrFail($this->recordId) : new MaintenanceSchedule;
                $before = $record->getAttributes();
                $attributes = [
                    'schedule_code' => $values['code'] ?? null,
                    'asset_id' => $values['asset'] ?? null,
                    'schedule_date' => $values['date'] ?? null,
                    'assigned_to' => $values['technician'] ?? null,
                    'maintenance_type' => $values['type'] ?? null,
                    'status' => $values['status'] ?? null,
                    'description' => $values['description'] ?? null,
                ];
                $asset = Asset::query()->lockForUpdate()->findOrFail($values['asset']);
                if ($asset->status === 'Dihapuskan') {
                    throw ValidationException::withMessages(['form.asset' => 'Aset telah dihapuskan secara operasional.']);
                }
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Jadwal Pemeliharaan', $record, $before);

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
            $this->redirectRoute('schedules.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'schedules');
        MaintenanceSchedule::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'schedules');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = MaintenanceSchedule::query()->lockForUpdate()->findOrFail($this->deleteId);
                ActivityLog::record('Menghapus', 'Jadwal Pemeliharaan', $record, $record->getAttributes());
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

    protected function displayRecord(MaintenanceSchedule $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->schedule_code ?? '',
            'asset' => $record->asset?->asset_code ?? '—',
            'date' => $record->schedule_date ?? '',
            'technician' => $record->assignee?->name ?? '—',
            'type' => $record->maintenance_type ?? '',
            'status' => $record->status ?? '',
            'description' => $record->description ?? '',
            'asset_name' => $record->asset?->name ?? 'Aset tidak tersedia',
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'schedules');
        $query = MaintenanceSchedule::query()->with(['asset', 'assignee']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('schedule_code', 'like', '%'.$this->search.'%');
                $query->orWhere('schedule_date', 'like', '%'.$this->search.'%');
                $query->orWhere('maintenance_type', 'like', '%'.$this->search.'%');
                $query->orWhere('status', 'like', '%'.$this->search.'%');
                $query->orWhere('description', 'like', '%'.$this->search.'%');
                $query->orWhereHas('asset', fn (Builder $related) => $related->where('asset_code', 'like', '%'.$this->search.'%'));
                $query->orWhereHas('assignee', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'schedule_code',
            'asset' => Asset::query()->select('asset_code')->whereColumn('assets.id', 'maintenance_schedules.asset_id')->limit(1),
            'date' => 'schedule_date',
            'technician' => User::query()->select('name')->whereColumn('users.id', 'maintenance_schedules.assigned_to')->limit(1),
            'status' => 'status',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $allRecords = (clone $query)->get()->map(fn (MaintenanceSchedule $record): array => $this->displayRecord($record));
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (MaintenanceSchedule $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(MaintenanceSchedule::query()->with(['asset', 'assignee'])->findOrFail($this->recordId)) : null;

        return view('livewire.schedules', [
            'items' => $items,
            'record' => $record,
            'module' => 'schedules',
            'allRecords' => $allRecords,
        ])->layout('layouts.app', ['title' => 'Jadwal Pemeliharaan']);
    }
}
