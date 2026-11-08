<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function defaultPermissions(string $role): array
    {
        $modules = array_keys(config('inventory.modules'));
        $read = array_map(fn (string $module): string => $module.':view', $modules);
        $operations = array_values(array_diff($modules, ['users', 'roles']));
        $manage = array_map(fn (string $module): string => $module.':manage', $operations);

        return match ($role) {
            'Super Admin' => array_merge($read, $manage, ['users:manage', 'roles:manage', 'reports:view', 'activities:view', 'movements:approve', 'disposals:approve']),
            'Admin Aset' => array_merge(array_diff($read, ['users:view', 'roles:view']), $manage, ['reports:view', 'activities:view', 'movements:approve']),
            'Teknisi' => ['assets:view', 'rooms:view', 'buildings:view', 'documents:view', 'maintenances:view', 'maintenances:manage', 'schedules:view', 'schedules:manage'],
            'Petugas Ruangan' => ['assets:view', 'rooms:view', 'buildings:view', 'documents:view', 'movements:view', 'movements:manage'],
            'Pimpinan' => array_merge(array_diff($read, ['users:view', 'roles:view']), ['reports:view', 'activities:view', 'movements:approve', 'disposals:approve']),
            default => [],
        };
    }
}
