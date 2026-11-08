<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Support\ReportExport;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $groupBy = 'category';

    protected function rules(): array
    {
        return [
            'startDate' => ['nullable', 'date_format:Y-m-d'],
            'endDate' => ['nullable', 'date_format:Y-m-d', ...($this->startDate ? ['after_or_equal:startDate'] : [])],
            'groupBy' => ['required', 'in:category,condition,room'],
        ];
    }

    public function export(string $format, ReportExport $export): StreamedResponse
    {
        Gate::authorize('module-view', 'reports');
        abort_unless(in_array($format, ['Excel', 'PDF'], true), 404);
        $this->validate();
        $groups = $this->grouped($this->assets(), $this->groupBy);
        ActivityLog::record('Export '.$format, 'Laporan Aset');
        $filename = 'laporan-aset-'.now()->timezone('Asia/Jakarta')->format('Ymd');
        if ($format === 'PDF') {
            $pdf = $export->pdf($groups, ($this->startDate ?: 'Semua tanggal').' - '.($this->endDate ?: 'Sekarang'));

            return response()->streamDownload(function () use ($pdf): void {
                echo $pdf;
            }, $filename.'.pdf', ['Content-Type' => 'application/pdf']);
        }

        return response()->streamDownload(function () use ($groups, $export): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Kelompok', 'Jumlah aset', 'Nilai perolehan (Rp)'], ';', '"', '');
            foreach ($groups as $name => $assets) {
                fputcsv($stream, [$export->spreadsheetCell((string) $name), $assets->count(), $assets->sum('price')], ';', '"', '');
            }
            fclose($stream);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function clearPeriod(): void
    {
        $this->reset('startDate', 'endDate');
        $this->resetValidation();
    }

    public function updated(): void
    {
        $this->validate();
    }

    private function assets(): Collection
    {
        $query = Asset::query()->with(['category', 'room.building']);
        if ($this->startDate !== '') {
            $query->whereDate('acquisition_date', '>=', $this->startDate);
        }
        if ($this->endDate !== '') {
            $query->whereDate('acquisition_date', '<=', $this->endDate);
        }

        return $query->get()->map(fn ($asset): array => [
            'id' => $asset->id,
            'code' => $asset->asset_code,
            'name' => $asset->name,
            'condition' => $asset->condition,
            'status' => $asset->status,
            'price' => $asset->acquisition_price,
            'category' => $asset->category?->name ?? '—',
            'room' => $asset->room?->name ?? '—',
            'building' => $asset->room?->building?->name ?? '—',
        ]);
    }

    private function grouped(Collection $assets, string $group): Collection
    {
        return $assets->groupBy($group === 'room'
            ? fn (array $asset): string => $asset['building'].' / '.$asset['room']
            : $group);
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'reports');
        $valid = (! $this->startDate || preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->startDate))
            && (! $this->endDate || preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->endDate))
            && (! $this->startDate || ! $this->endDate || $this->endDate >= $this->startDate);
        $assets = $valid ? $this->assets() : collect();
        $group = in_array($this->groupBy, ['category', 'condition', 'room'], true) ? $this->groupBy : 'category';

        return view('livewire.reports', ['assets' => $assets, 'groups' => $this->grouped($assets, $group)])
            ->layout('layouts.app', ['title' => 'Laporan Aset']);
    }
}
