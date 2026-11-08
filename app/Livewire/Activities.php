<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Activities extends Component
{
    use WithPagination;

    public string $search = '';

    public string $action = '';

    public string $moduleFilter = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'action', 'moduleFilter');
        $this->resetPage();
    }

    public function render(): View
    {
        Gate::authorize('module-view', 'activities');
        $query = ActivityLog::query()->with('user');
        if ($this->search !== '') {
            $query->where(function ($query): void {
                $query->where('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$this->search.'%'));
            });
        }
        $query->when($this->action !== '', fn ($query) => $query->where('action', $this->action));
        $query->when($this->moduleFilter !== '', fn ($query) => $query->where('module', $this->moduleFilter));
        $items = $query->latest('id')->paginate(10)->through(fn ($item): array => [
            'id' => $item->id,
            'user' => $item->user?->name ?? 'Sistem / pengguna dihapus',
            'action' => $item->action,
            'module' => $item->module,
            'description' => $item->description,
            'date' => $item->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
        ]);

        return view('livewire.activities', [
            'items' => $items, 'actions' => ActivityLog::query()->distinct()->pluck('action'),
            'modules' => ActivityLog::query()->distinct()->pluck('module'),
        ])->layout('layouts.app', ['title' => 'Aktivitas']);
    }
}
