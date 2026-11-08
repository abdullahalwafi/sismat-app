<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $technician = User::query()->where('email', 'budi.teknisi@sismat.example')->firstOrFail();
        $today = now()->timezone('Asia/Jakarta')->startOfDay();

        for ($index = 1; $index <= 9; $index++) {
            $assetCode = 'AST-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $asset = Asset::withTrashed()->where('asset_code', $assetCode)->firstOrFail();

            MaintenanceSchedule::firstOrCreate([
                'asset_id' => $asset->id,
                'description' => 'Jadwal contoh perawatan berkala '.$assetCode.'.',
            ], [
                'assigned_to' => $technician->id,
                'schedule_code' => 'JDL-2026-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'schedule_date' => $today->copy()->addDays($index * 2)->toDateString(),
                'maintenance_type' => 'Preventive',
                'status' => 'Terjadwal',
            ]);
        }
    }
}
