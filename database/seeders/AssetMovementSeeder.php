<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetMovement;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetMovementSeeder extends Seeder
{
    public function run(): void
    {
        $mover = User::query()->where('email', 'admin.aset@sismat.example')->firstOrFail();
        $icu = Room::query()->where('code', 'RG-001')->firstOrFail();
        $igd = Room::query()->where('code', 'RG-002')->firstOrFail();
        $today = now()->timezone('Asia/Jakarta')->startOfDay();

        for ($index = 1; $index <= 6; $index++) {
            $assetCode = 'AST-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $asset = Asset::withTrashed()->where('asset_code', $assetCode)->firstOrFail();
            $origin = $asset->room_id === $icu->id ? $igd : $icu;

            AssetMovement::firstOrCreate(['movement_code' => 'MUT-2026-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT)], [
                'asset_id' => $asset->id,
                'from_room_id' => $origin->id,
                'to_room_id' => $asset->room_id,
                'moved_by' => $mover->id,
                'movement_date' => $today->copy()->subDays($index + 2)->toDateString(),
                'reason' => 'Penyesuaian kebutuhan inventaris dan pelayanan unit tujuan.',
                'notes' => 'Perpindahan contoh selesai dan aset telah diterima petugas ruangan.',
            ]);
        }
    }
}
