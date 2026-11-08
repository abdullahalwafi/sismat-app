<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetDocument;
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

class Documents extends Component
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

    public ?TemporaryUploadedFile $documentUpload = null;

    public function mount(?int $record = null): void
    {
        Gate::authorize('module-view', 'documents');
        $this->mode = match (true) {
            request()->routeIs('documents.create') => 'create',
            request()->routeIs('documents.edit') => 'edit',
            $record !== null => 'show',
            default => 'index',
        };
        if (in_array($this->mode, ['create', 'edit'], true)) {
            Gate::authorize('module-manage', 'documents');
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
            'type' => array_combine(['Invoice', 'Garansi', 'Manual book', 'Sertifikat kalibrasi', 'Pengadaan'], ['Invoice', 'Garansi', 'Manual book', 'Sertifikat kalibrasi', 'Pengadaan']),
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
        if ($property === 'documentUpload') {
            $this->validateOnly($property);
        }
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'code', 'name', 'asset', 'type', 'date'], true)) {
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
        Gate::authorize('module-manage', 'documents');
        $this->recordId = null;
        $this->prepareForm();
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        Gate::authorize('module-manage', 'documents');
        $this->reset('documentUpload');
        $this->form = $this->formValues($id);
        $this->recordId = $id;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset('documentUpload');
        $this->resetValidation();
    }

    protected function formValues(int $id): array
    {
        $record = AssetDocument::query()->with(['asset'])->findOrFail($id);

        return [
            'code' => $record->document_code ?? '',
            'name' => $record->name ?? '',
            'asset' => $record->asset_id === null ? '' : (string) $record->asset_id,
            'type' => $record->document_type ?? '',
            'date' => $record->document_date ?? '',
            'description' => $record->description ?? '',
            'attachment' => $record->file_path ?? '',
        ];
    }

    protected function prepareForm(): void
    {
        $this->reset('documentUpload');
        $this->form = [
            'code' => '',
            'name' => '',
            'asset' => '',
            'type' => '',
            'date' => now()->timezone('Asia/Jakarta')->format('Y-m-d'),
            'description' => '',
            'attachment' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50', Rule::unique('asset_documents', 'document_code')->ignore($this->recordId)],
            'form.name' => ['required', 'string', 'max:100'],
            'form.asset' => ['required', Rule::in(array_keys($this->options('asset')))],
            'form.type' => ['required', Rule::in(array_keys($this->options('type')))],
            'form.date' => ['required', 'date_format:Y-m-d'],
            'form.description' => ['nullable', 'string', 'max:2000'],
            'documentUpload' => [Rule::requiredIf($this->recordId === null), 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'Kode dokumen',
            'form.name' => 'Nama dokumen',
            'form.asset' => 'Aset',
            'form.type' => 'Jenis dokumen',
            'form.date' => 'Tanggal dokumen',
            'form.description' => 'Catatan',
            'documentUpload' => 'Berkas dokumen',
        ];
    }

    public function save(): void
    {
        Gate::authorize('module-manage', 'documents');
        $this->form['code'] = mb_strtoupper(trim($this->form['code'] ?? ''));
        $validated = $this->validate(null, [
            'required' => ':attribute wajib diisi.', 'in' => 'Pilihan :attribute tidak valid.',
            'min' => ':attribute kurang dari batas yang diizinkan.', 'email' => 'Format email tidak valid.',
            'max' => ':attribute melebihi batas yang diizinkan.', 'unique' => ':attribute sudah digunakan.',
        ]);
        $paths = [];
        $files = [];
        $old = $this->recordId ? AssetDocument::query()->findOrFail($this->recordId) : null;
        try {
            if ($this->documentUpload) {
                $files['file_path'] = $this->documentUpload->store('asset-documents', 'local');
                if (! is_string($files['file_path']) || $files['file_path'] === '') {
                    throw new \RuntimeException('Berkas gagal disimpan ke storage.');
                }
                $paths[] = $files['file_path'];
                $files['file_name'] = mb_substr($this->documentUpload->getClientOriginalName(), 0, 255);
                $files['file_type'] = $this->documentUpload->getMimeType();
            }
            $savedId = DB::transaction(function () use ($validated, $files): int {
                $values = $validated['form'];
                $record = $this->recordId ? AssetDocument::query()->lockForUpdate()->findOrFail($this->recordId) : new AssetDocument;
                $before = $record->getAttributes();
                $attributes = [
                    'document_code' => $values['code'] ?? null,
                    'name' => $values['name'] ?? null,
                    'asset_id' => $values['asset'] ?? null,
                    'document_type' => $values['type'] ?? null,
                    'document_date' => $values['date'] ?? null,
                    'description' => $values['description'] ?? null,
                ];
                if (! $record->exists) {
                    $attributes['uploaded_by'] = auth()->id();
                }
                $attributes = array_merge($attributes, $files);
                $record->fill($attributes)->save();
                ActivityLog::record($this->recordId ? 'Mengubah' : 'Menambahkan', 'Dokumen Aset', $record, $before);

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
        if (isset($files['file_path']) && $old?->file_path) {
            Storage::disk('local')->delete($old->file_path);
        }
        $this->closeForm();
        $this->notify('Data berhasil disimpan ke database.');
        if (in_array($this->mode, ['create', 'edit'], true)) {
            session()->flash('success', 'Data berhasil disimpan ke database.');
            $this->redirectRoute('documents.show', ['record' => $savedId]);
        }
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('module-manage', 'documents');
        AssetDocument::query()->findOrFail($id);
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function delete(): void
    {
        Gate::authorize('module-manage', 'documents');
        if ($this->deleteId === null) {
            return;
        }
        try {
            $record = AssetDocument::query()->findOrFail($this->deleteId);
            DB::transaction(function (): void {
                $record = AssetDocument::query()->lockForUpdate()->findOrFail($this->deleteId);
                ActivityLog::record('Menghapus', 'Dokumen Aset', $record, $record->getAttributes());
                $record->delete();
            });
            Storage::disk('local')->delete($record->file_path);
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

    protected function displayRecord(AssetDocument $record): array
    {
        return [
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
        ];
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'documents');
        $query = AssetDocument::query()->with(['asset']);
        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->orWhere('document_code', 'like', '%'.$this->search.'%');
                $query->orWhere('name', 'like', '%'.$this->search.'%');
                $query->orWhere('document_type', 'like', '%'.$this->search.'%');
                $query->orWhere('document_date', 'like', '%'.$this->search.'%');
                $query->orWhere('description', 'like', '%'.$this->search.'%');
                $query->orWhereHas('asset', fn (Builder $related) => $related->where('asset_code', 'like', '%'.$this->search.'%'));
            });
        }
        if (! empty($this->filters['type'])) {
            $query->where('document_type', $this->filters['type']);
        }
        $sort = match ($this->sortField) {
            'id' => 'id',
            'code' => 'document_code',
            'name' => 'name',
            'asset' => Asset::query()->select('asset_code')->whereColumn('assets.id', 'asset_documents.asset_id')->limit(1),
            'type' => 'document_type',
            'date' => 'document_date',
            default => 'id',
        };
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [8, 16, 32], true) ? $this->perPage : 8;
        $items = $query->orderBy($sort, $direction)->orderByDesc('id')->paginate($perPage)
            ->through(fn (AssetDocument $record): array => $this->displayRecord($record));
        $record = $this->mode === 'show' ? $this->displayRecord(AssetDocument::query()->with(['asset'])->findOrFail($this->recordId)) : null;

        return view('livewire.documents', [
            'items' => $items,
            'record' => $record,
            'module' => 'documents',
        ])->layout('layouts.app', ['title' => 'Dokumen Aset']);
    }
}
