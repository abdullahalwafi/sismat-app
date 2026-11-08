<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetDisposalSeeder extends Seeder
{
    public function run(): void
    {
        $requester = User::query()->where('email', 'admin.aset@sismat.example')->firstOrFail();
        $approver = User::query()->where('email', 'pimpinan@sismat.example')->firstOrFail();
        $today = now()->timezone('Asia/Jakarta')->startOfDay();

        foreach ([1, 12, 23] as $index => $number) {
            $assetCode = 'AST-2026-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
            $asset = Asset::withTrashed()->where('asset_code', $assetCode)->firstOrFail();

            AssetDisposal::firstOrCreate([
                'asset_id' => $asset->id,
                'disposal_reason' => 'Usulan contoh: kerusakan berat dengan biaya perbaikan tidak ekonomis.',
            ], [
                'requested_by' => $requester->id,
                'disposal_code' => 'HPS-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'status' => $index === 1 ? 'Disetujui' : 'Menunggu persetujuan',
                'approved_at' => $index === 1 ? now() : null,
                'approved_by' => $index === 1 ? $approver->id : null,
                'disposal_date' => $today->copy()->subDays($index + 1)->toDateString(),
                'disposal_method' => $index === 1 ? 'Lelang' : 'Pemusnahan',
                'notes' => $index === 1 ? 'Persetujuan contoh telah diberikan; pelaksanaan menunggu administrasi.' : 'Usulan contoh menunggu peninjauan pimpinan.',
            ]);
        }
    }
}
