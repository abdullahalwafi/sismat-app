<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public static function record(string $action, string $module, ?Model $record = null, array $before = []): void
    {
        $after = $record?->getAttributes() ?? [];
        unset($before['password'], $before['remember_token'], $after['password'], $after['remember_token']);

        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $record ? $record::class : null,
            'subject_id' => $record?->id,
            'description' => $action.' '.($record?->name ?? $record?->asset_code ?? $record?->movement_code ?? $record?->disposal_code ?? $record?->maintenance_code ?? $record?->schedule_code ?? $module),
            'changes' => ['before' => $before, 'after' => $after],
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
