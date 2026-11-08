<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetDocument;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $uploader = User::query()->where('email', 'admin.aset@sismat.example')->firstOrFail();
        $types = ['Invoice', 'Garansi', 'Sertifikat kalibrasi'];

        for ($index = 1; $index <= 9; $index++) {
            $assetCode = 'AST-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $asset = Asset::withTrashed()->where('asset_code', $assetCode)->firstOrFail();
            $type = $types[($index - 1) % count($types)];
            $fileName = str_replace(' ', '-', strtolower($type)).'-'.$assetCode.'.pdf';

            AssetDocument::firstOrCreate([
                'asset_id' => $asset->id,
                'file_path' => 'demo/asset-documents/'.$fileName,
            ], [
                'uploaded_by' => $uploader->id,
                'document_code' => 'DOK-2026-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'name' => $type.' '.$asset->name,
                'document_type' => $type,
                'document_date' => now()->timezone('Asia/Jakarta')->toDateString(),
                'file_name' => $fileName,
                'file_type' => 'application/pdf',
                'description' => $type.' '.$asset->name.'. Metadata contoh; berkas fisik belum tersedia di storage.',
            ]);
        }
    }
}
