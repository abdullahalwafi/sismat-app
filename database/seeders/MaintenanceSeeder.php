<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()->where('email', 'admin.aset@sismat.example')->firstOrFail();
        $technicians = User::query()->whereIn('email', [
            'budi.teknisi@sismat.example', 'rina.teknisi@sismat.example', 'ahmad.teknisi@sismat.example',
        ])->orderBy('email')->get();
        $assetNumbers = [8, 15, 22, 29, 36, 2, 3, 4, 5];
        $today = now()->timezone('Asia/Jakarta')->startOfDay();

        foreach ($assetNumbers as $index => $number) {
            $assetCode = 'AST-2026-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
            $asset = Asset::withTrashed()->where('asset_code', $assetCode)->firstOrFail();
            $status = $index < 5 ? 'In Progress' : ($index < 7 ? 'Completed' : 'Pending');
            $date = $today->copy()->subDays(9 - $index);

            Maintenance::firstOrCreate(['maintenance_code' => 'MNT-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)], [
                'asset_id' => $asset->id,
                'technician_id' => $technicians[$index % $technicians->count()]->id,
                'created_by' => $creator->id,
                'maintenance_type' => $index % 2 === 0 ? 'Preventive' : 'Corrective',
                'maintenance_date' => $date->toDateString(),
                'description' => 'Pemeriksaan fungsi dan keselamatan '.$asset->name.'.',
                'cost' => ($index + 1) * 250000,
                'status' => $status,
                'notes' => $status === 'Completed' ? 'Uji fungsi selesai; aset dapat digunakan kembali.' : 'Koordinasi pemeriksaan dengan petugas unit terkait.',
                'completed_at' => $status === 'Completed' ? $date->copy()->addHours(14) : null,
            ]);
        }
    }
}
