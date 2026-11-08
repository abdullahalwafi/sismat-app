<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetDisposal;
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

class Disposals extends Component
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
        Gate::authorize('module-view', 'disposals');
        $this->mode = match (true) {
            request()->routeIs('disposals.create') => 'create',
            request()->routeIs('disposals.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'disposals');
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
            'method' => array_combine(['Pemusnahan', 'Lelang', 'Hibah'], ['Pemusnahan', 'Lelang', 'Hibah']),
            'status' => array_combine(['Menunggu persetujuan', 'Disetujui', 'Ditolak', 'Selesai'], ['Menunggu persetujuan', 'Disetujui', 'Ditolak', 'Selesai']),
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
        if (! in_array($field, ['id', 'code', 'asset', 'method', 'date', 'status'], true)) {
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
        Gate::authorize('module-manage', 'disposals');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'disposals');
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
        $record = AssetDisposal::query()->with(['asset', 'requester'])->findOrFail($id);

        return [
            'code' => $record->disposal_code ?? '',
            'asset' => $record->asset_id === null ? '' : (string) $record->asset_id,
            'method' => $record->disposal_method ?? '',
            'date' => $record->disposal_date ?? '',
            'status' => $record->status ?? '',
            'description' => $record->disposal_reason ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->form = [
            'code' => '',
            'asset' => '',
            'method' => '',
            'date' => now()->timezone('Asia/Jakarta')->format('Y-m-d'),
            'status' => 'Menunggu persetujuan',
            'description' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', Rule::unique('asset_disposals', 'disposal_code')->ignore($this->recordId)],
            'form.asset' => ['required', Rule::in(array_keys($this->options('asset')))],
            'form.method' => ['required', Rule::in(array_keys($this->options('method')))],
            'form.date' => ['required', 'date_format:Y-m-d'],
            'form.description' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode pengajuan',
            'form.asset' => 'Aset',
            'form.method' => 'Metode penghapusan',
            'form.date' => 'Tanggal pengajuan',
            'form.status' => 'Status persetujuan',
            'form.description' => 'Alasan penghapusan',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'disposals');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        try {
            $savedId = DB::transaction(function () use ($validated): int {
                $values = $validated['form'];
                $record = $this->recordId ? AssetDisposal::query()->lockForUpdate()->findOrFail($this->recordId) : new AssetDisposal;
                $before = $record->getAttributes();
                if ($record->exists && $record->status !== 'Menunggu persetujuan') {
                    throw ValidationException::withMessages(['form.status' => 'Pengajuan yang sudah diproses tidak dapat diedit.']);
                }
                if ($record->exists && auth()->user()->role?->name === 'Petugas Ruangan' && (int) $record->requested_by !== auth()->id()) {
                    abort(403);
                }
                $attributes = [
                    'disposal_code' => $values['code'] ?? null,
                    'asset_id' => $values['asset'] ?? null,
                    'disposal_method' => $values['method'] ?? null,
                    'disposal_date' => $values['date'] ?? null,
                    'disposal_reason' => $values['description'] ?? null,
                ];
                $asset = Asset::query()->lockForUpdate()->findOrFail($values['asset']);
                if ($asset->status === 'Dihapuskan') {
                    throw ValidationException::withMessages(['form.asset' => 'Aset telah dihapuskan secara operasional.']);
                }
                if (! $record->exists) {
                    $attributes['requested_by'] = auth()->id();
                    $attributes['status'] = 'Menunggu persetujuan';
                }
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Penghapusan Aset', $record, $before);

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
            $this->redirectRoute('disposals.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'disposals');
        AssetDisposal::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'disposals');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = AssetDisposal::query()->lockForUpdate()->findOrFail($this->deleteId);
                if ($record->exists && $record->status !== 'Menunggu persetujuan') {
                    throw ValidationException::withMessages(['form.status' => 'Pengajuan yang sudah diproses tidak dapat diedit.']);
                }
                if ($record->exists && auth()->user()->role?->name === 'Petugas Ruangan' && (int) $record->requested_by !== auth()->id()) {
                    abort(403);
                }
                ActivityLog::record('Menghapus', 'Penghapusan Aset', $record, $record->getAttributes());
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
            Gate::authorize('module-approve', 'disposals');
            DB::transaction(function () use ($id, $status): void {
                $record = AssetDisposal::query()->lockForUpdate()->findOrFail($id);
                $before = $record->getAttributes();
                $pending = 'Menunggu persetujuan';
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
                    if ($asset->maintenances()->where('status', 'In Progress')->exists() || $asset->movements()->whereIn('status', ['Diajukan', 'Disetujui'])->exists()) {
                        throw ValidationException::withMessages(['status' => 'Selesaikan pemeliharaan atau mutasi yang masih berjalan terlebih dahulu.']);
                    }
                    $asset->maintenanceSchedules()->where('status', 'Terjadwal')->update(['status' => 'Dibatalkan']);
                    $asset->update(['status' => 'Dihapuskan']);
                    $record->completed_at = now();
                }
                $record->status = $status;
                $record->save();
                ActivityLog::record('Mengubah status', 'Penghapusan Aset', $record, $before);
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

    protected function displayRecord(AssetDisposal $record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->disposal_code ?? '',
            'asset' => $record->asset?->asset_code ?? '—',
            'method' => $record->disposal_method ?? '',
            'date' => $record->disposal_date ?? '',
            'status' => $record->status ?? '',
            'description' => $record->disposal_reason ?? '',
            'officer' => $record->requester?->name ?? '—',
            'asset_name' => $record->asset?->name ?? 'Aset tidak tersedia',
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'disposals');
        $query = AssetDisposal::query()->with(['asset', 'requester']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('disposal_code', 'like', '%'.$this->search.'%');
                $query->orWhere('disposal_method', 'like', '%'.$this->search.'%');
                $query->orWhere('disposal_date', 'like', '%'.$this->search.'%');
                $query->orWhere('status', 'like', '%'.$this->search.'%');
                $query->orWhere('disposal_reason', 'like', '%'.$this->search.'%');
                $query->orWhereHas('asset', fn (Builder $related) => $related->where('asset_code', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'disposal_code',
            'asset' => Asset::query()->select('asset_code')->whereColumn('assets.id', 'asset_disposals.asset_id')->limit(1),
            'method' => 'disposal_method',
            'date' => 'disposal_date',
            'status' => 'status',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (AssetDisposal $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(AssetDisposal::query()->with(['asset', 'requester'])->findOrFail($this->recordId)) : null;

        return view('livewire.disposals', [
            'items' => $items,
            'record' => $record,
            'module' => 'disposals',
        ])->layout('layouts.app', ['title' => 'Penghapusan Aset']);
    }
}
