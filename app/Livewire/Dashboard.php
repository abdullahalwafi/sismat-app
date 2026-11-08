<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        Gate::authorize('module-view', 'assets');
        $assets = Asset::query()->with(['category', 'room.building'])->get()->map(fn (Asset $asset): array => [
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

        return view('livewire.dashboard', [
            'assets' => $assets,
            'conditions' => $assets->countBy('condition'),
            'statuses' => $assets->countBy('status'),
            'categories' => $assets->countBy('category')->sortDesc(),
            'latestAssets' => $assets->sortByDesc('id')->take(5),
            'activities' => Gate::allows('module-view', 'activities') ? ActivityLog::query()->with('user')->latest('id')->limit(5)->get()->map(fn ($item): array => [
                'id' => $item->id, 'description' => $item->description,
                'user' => $item->user?->name ?? 'Sistem / pengguna dihapus',
                'module' => $item->module,
                'date' => $item->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
            ]) : collect(),
            'maintenances' => Gate::allows('module-view', 'maintenances') ? Maintenance::query()->with(['asset', 'technician'])->whereNotIn('status', ['Completed', 'Cancelled'])->orderBy('maintenance_date')->limit(4)->get()->map(fn ($item): array => [
                'id' => $item->id, 'asset' => $item->asset?->asset_code ?? '—',
                'type' => $item->maintenance_type, 'date' => $item->maintenance_date,
                'status' => $item->status, 'technician' => $item->technician?->name ?? '—',
            ]) : collect(),
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
