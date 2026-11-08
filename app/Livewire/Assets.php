<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetDocument;
use App\Models\AssetMovement;
use App\Models\Building;
use App\Models\Category;
use App\Models\Maintenance;
use App\Models\Room;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Assets extends Component
{
    use WithFileUploads, WithPagination;

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

    public ?TemporaryUploadedFile $photoUpload = null;

    public function mount(?int $record = null): void
    {
        Gate::authorize('module-view', 'assets');
        $this->mode = match (true) {
            request()->routeIs('assets.create') => 'create',
            request()->routeIs('assets.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'assets');
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
            'category' => Category::query()->orderBy('name')->pluck('name', 'id')->all(),
            'building' => Building::query()->orderBy('name')->pluck('name', 'id')->all(),
            'room' => Room::query()
                ->when(! empty($this->form['building']), fn ($query) => $query->where('building_id', $this->form['building']))
                ->orderBy('code')
                ->get()->mapWithKeys(fn ($room): array => [(string) $room->id => $room->building->name.' · '.$room->code.' · '.$room->name])->all(),
            'condition' => array_combine(['Baik', 'Rusak ringan', 'Rusak berat'], ['Baik', 'Rusak ringan', 'Rusak berat']),
            'status' => array_combine(['Aktif', 'Tidak aktif', 'Dalam pemeliharaan', 'Dihapuskan'], ['Aktif', 'Tidak aktif', 'Dalam pemeliharaan', 'Dihapuskan']),
            'supplier' => Supplier::query()->orderBy('name')->pluck('name', 'id')->all(),
            default => [],
        };
    }

    public function filterOptions(string $field): array
    {
        if ($field === 'room') {
            return Room::query()
                ->when(! empty($this->filters['building']), fn ($query) => $query->where('building_id', $this->filters['building']))
                ->orderBy('code')->get()
                ->mapWithKeys(fn ($room): array => [(string) $room->id => $room->building->name.' · '.$room->code.' · '.$room->name])->all();
        }

        return $this->options($field);
    }

    public function updated(string $property): void
    {
        if ($property === 'search' || str_starts_with($property, 'filters.') || $property === 'perPage') {
            $this->resetPage();
        }
        if ($property === 'filters.building') {
            $this->filters['room'] = '';
        }
        if ($property === 'form.building') {
            $this->form['room'] = '';
        }
        if ($property === 'photoUpload') {
            $this->validateOnly($property);
        }
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'code', 'name', 'category', 'room', 'condition', 'status'], true)) {
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
        Gate::authorize('module-manage', 'assets');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'assets');
        $this->reset('photoUpload');
        $this->form = $this->formValues($id);
        $this->recordId = $id;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset('photoUpload');
        $this->resetValidation();
    }

    protected function formValues(int $id): array
    {
        $record = Asset::query()->with(['category', 'room', 'supplier', 'room.building'])->findOrFail($id);

        return [
            'code' => $record->asset_code ?? '',
            'name' => $record->name ?? '',
            'category' => $record->category_id === null ? '' : (string) $record->category_id,
            'brand' => $record->brand ?? '',
            'model' => $record->model ?? '',
            'serial_number' => $record->serial_number ?? '',
            'building' => (string) $record->room->building_id,
            'room' => $record->room_id === null ? '' : (string) $record->room_id,
            'acquired_at' => $record->acquisition_date ?? '',
            'price' => $record->acquisition_price ?? '',
            'condition' => $record->condition ?? '',
            'status' => $record->status ?? '',
            'description' => $record->description ?? '',
            'photo' => $record->photo_path ? route('assets.photo', $record->id) : '',
            'supplier' => $record->supplier_id === null ? '' : (string) $record->supplier_id,
            'useful_life_years' => $record->useful_life_years ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->reset('photoUpload');
        $this->form = [
            'code' => '',
            'name' => '',
            'category' => '',
            'brand' => '',
            'model' => '',
            'serial_number' => '',
            'building' => '',
            'room' => '',
            'acquired_at' => now()->timezone('Asia/Jakarta')->format('Y-m-d'),
            'price' => 0,
            'condition' => '',
            'status' => '',
            'description' => '',
            'photo' => '',
            'supplier' => '',
            'useful_life_years' => 0,
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', Rule::unique('assets', 'asset_code')->ignore($this->recordId)],
            'form.name' => ['required', 'string', 'max:100'],
            'form.category' => ['required', Rule::in(array_keys($this->options('category')))],
            'form.brand' => ['required', 'string', 'max:100'],
            'form.model' => ['required', 'string', 'max:100'],
            'form.serial_number' => ['required', 'string', 'max:100'],
            'form.building' => ['required', Rule::in(array_keys($this->options('building')))],
            'form.room' => ['required', Rule::in(array_keys($this->options('room')))],
            'form.acquired_at' => ['required', 'date_format:Y-m-d'],
            'form.price' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'form.condition' => ['required', Rule::in(array_keys($this->options('condition')))],
            'form.status' => ['required', Rule::in(array_keys($this->options('status')))],
            'form.description' => ['nullable', 'string', 'max:2000'],
            'form.supplier' => ['nullable', Rule::in(array_keys($this->options('supplier')))],
            'form.useful_life_years' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'photoUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode aset',
            'form.name' => 'Nama aset',
            'form.category' => 'Kategori',
            'form.brand' => 'Merek',
            'form.model' => 'Model',
            'form.serial_number' => 'Serial number',
            'form.building' => 'Gedung',
            'form.room' => 'Ruangan',
            'form.acquired_at' => 'Tanggal perolehan',
            'form.price' => 'Harga perolehan (Rp)',
            'form.condition' => 'Kondisi',
            'form.status' => 'Status',
            'form.description' => 'Deskripsi',
            'form.supplier' => 'Supplier',
            'form.useful_life_years' => 'Masa manfaat (tahun)',
            'photoUpload' => 'Foto aset',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'assets');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        $paths = [];
        $files = [];
        $old = $this->recordId ? Asset::query()->findOrFail($this->recordId) : null;
        try {
            if ($this->photoUpload) {
                $files['photo_path'] = $this->photoUpload->store('asset-photos', 'local');
                if (! is_string($files['photo_path']) || $files['photo_path'] === '') {
                    throw new \RuntimeException('Berkas gagal disimpan ke storage.');
                }
                $paths[] = $files['photo_path'];

            }
            $savedId = DB::transaction(function () use ($validated, $files): int {
                $values = $validated['form'];
                $record = $this->recordId ? Asset::query()->lockForUpdate()->findOrFail($this->recordId) : new Asset;
                $before = $record->getAttributes();
                $attributes = [
                    'asset_code' => $values['code'] ?? null,
                    'name' => $values['name'] ?? null,
                    'category_id' => $values['category'] ?? null,
                    'brand' => $values['brand'] ?? null,
                    'model' => $values['model'] ?? null,
                    'serial_number' => $values['serial_number'] ?? null,
                    'room_id' => $values['room'] ?? null,
                    'acquisition_date' => $values['acquired_at'] ?? null,
                    'acquisition_price' => $values['price'] ?? null,
                    'condition' => $values['condition'] ?? null,
                    'status' => $values['status'] ?? null,
                    'description' => $values['description'] ?? null,
                    'supplier_id' => ($values['supplier'] ?? '') === '' ? null : $values['supplier'],
                    'useful_life_years' => $values['useful_life_years'] ?? null,
                ];
                if ($record->status === 'Dihapuskan' && $values['status'] !== 'Dihapuskan') {
                    throw ValidationException::withMessages(['form.status' => 'Aset yang telah dihapuskan tidak dapat diaktifkan kembali.']);
                }
                if ($record->status !== 'Dihapuskan' && $values['status'] === 'Dihapuskan') {
                    throw ValidationException::withMessages(['form.status' => 'Gunakan alur persetujuan penghapusan untuk menghapuskan aset.']);
                }
                if (! $record->exists) {
                    $attributes['created_by'] = auth()->id();
                }
                $attributes = array_merge($attributes, $files);
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Data Aset', $record, $before);

                return $record->id;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            if ($exception instanceof QueryException && str_contains(mb_strtolower($exception->getMessage()), 'unique')) {
                $this->addError('form.code', 'Kode atau email sudah digunakan.');

                return;
            }
            throw $exception;
        }
        if (isset($files['photo_path']) && $old?->photo_path) {
            Storage::disk('local')->delete($old->photo_path);
        }
        $this->closeForm();
        $this->notify('Data berhasil disimpan ke database.');
        if (in_array($this->mode, ['create', 'edit'], true)) {
            session()->flash('success', 'Data berhasil disimpan ke database.');
            $this->redirectRoute('assets.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'assets');
        Asset::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'assets');
        if ($this->deleteId === null) {
            return;
        }
        try {
            DB::transaction(function (): void {
                $record = Asset::query()->lockForUpdate()->findOrFail($this->deleteId);
                if ($record->maintenanceSchedules()->where('status', 'Terjadwal')->exists()
                || $record->disposals()->whereIn('status', ['Menunggu persetujuan', 'Disetujui'])->exists()
                || $record->maintenances()->whereIn('status', ['Pending', 'In Progress'])->exists()
                || $record->movements()->whereIn('status', ['Diajukan', 'Disetujui'])->exists()) {
                    throw ValidationException::withMessages(['delete' => 'Data masih digunakan oleh modul lain atau merupakan akun aktif Anda.']);
                }
                ActivityLog::record('Menghapus', 'Data Aset', $record, $record->getAttributes());
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

    protected function displayRecord(Asset $record): array
    {
        return [
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
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'assets');
        $query = Asset::query()->with(['category', 'room', 'supplier', 'room.building']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('asset_code', 'like', '%'.$this->search.'%');
                $query->orWhere('name', 'like', '%'.$this->search.'%');
                $query->orWhere('brand', 'like', '%'.$this->search.'%');
                $query->orWhere('model', 'like', '%'.$this->search.'%');
                $query->orWhere('serial_number', 'like', '%'.$this->search.'%');
                $query->orWhere('acquisition_date', 'like', '%'.$this->search.'%');
                $query->orWhere('acquisition_price', 'like', '%'.$this->search.'%');
                $query->orWhere('condition', 'like', '%'.$this->search.'%');
                $query->orWhere('status', 'like', '%'.$this->search.'%');
                $query->orWhere('description', 'like', '%'.$this->search.'%');
                $query->orWhere('useful_life_years', 'like', '%'.$this->search.'%');
                $query->orWhereHas('category', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
                $query->orWhereHas('room', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
                $query->orWhereHas('supplier', fn (Builder $related) => $related->where('name', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['category'])) {
            $query->where('category_id', $this->filters['category']);
        }
        if (! empty($this->filters['condition'])) {
            $query->where('condition', $this->filters['condition']);
        }
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (! empty($this->filters['building'])) {
            $query->whereHas('room', fn (Builder $room) => $room->where('building_id', $this->filters['building']));
        }
        if (! empty($this->filters['room'])) {
            $query->where('room_id', $this->filters['room']);
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'asset_code',
            'name' => 'name',
            'category' => Category::query()->select('name')->whereColumn('categories.id', 'assets.category_id')->limit(1),
            'room' => Room::query()->select('name')->whereColumn('rooms.id', 'assets.room_id')->limit(1),
            'condition' => 'condition',
            'status' => 'status',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (Asset $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(Asset::query()->with(['category', 'room', 'supplier', 'room.building'])->findOrFail($this->recordId)) : null;

        return view('livewire.assets', [
            'items' => $items,
            'record' => $record,
            'module' => 'assets',
            'maintenances' => $this->mode === 'show' ? Maintenance::query()->with(['asset', 'technician'])->get()->map(fn (Maintenance $record): array => [
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
            ]) : collect(),
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
            'documents' => $this->mode === 'show' ? AssetDocument::query()->with(['asset'])->get()->map(fn (AssetDocument $record): array => [
                'id' => $record->id,
                'code' => $record->document_code ?? '',
                'name' => $record->name ?? '',
                'asset' => $record->asset?->asset_code ?? '—',
                'type' => $record->document_type ?? '',
                'date' => $record->document_date ?? '',
                'description' => $record->description ?? '',
                'attachment' => $record->file_name,
                'asset_name' => $record->asset?->name ?? 'Aset tidak tersedia',
                'available' => $record->file_path && Storage::disk('local')->exists($record->file_path),
            ]) : collect(),
        ])->layout('layouts.app', ['title' => 'Data Aset']);
    }
}
