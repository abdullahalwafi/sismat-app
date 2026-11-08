<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Room;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'code');
        $rooms = Room::query()->pluck('id', 'code');
        $suppliers = Supplier::query()->pluck('id', 'code');
        $creator = User::query()->where('email', 'admin.aset@sismat.example')->firstOrFail();
        $specifications = [
            ['Patient Monitor', 'KAT-001', 'RG-001', 'SUP-001', 'Philips', 'IntelliVue MX450', 45000000, 8],
            ['Ventilator', 'KAT-001', 'RG-001', 'SUP-005', 'Dräger', 'Savina 300', 185000000, 10],
            ['Infusion Pump', 'KAT-001', 'RG-002', 'SUP-001', 'Terumo', 'TE-SS800', 18500000, 7],
            ['Hematology Analyzer', 'KAT-002', 'RG-005', 'SUP-002', 'Sysmex', 'XN-550', 225000000, 10],
            ['Mikroskop Binokuler', 'KAT-002', 'RG-005', 'SUP-002', 'Olympus', 'CX23', 24000000, 8],
            ['Komputer Workstation', 'KAT-003', 'RG-008', 'SUP-003', 'Dell', 'OptiPlex 7010', 14500000, 5],
            ['Printer Laser', 'KAT-003', 'RG-009', 'SUP-003', 'HP', 'LaserJet Pro M404', 6200000, 5],
            ['Bed Pasien Elektrik', 'KAT-004', 'RG-004', 'SUP-004', 'Paramount', 'PA-9000', 32000000, 10],
            ['Lemari Arsip', 'KAT-005', 'RG-009', 'SUP-006', 'Brother', 'B-304', 3500000, 8],
            ['Ambulans', 'KAT-006', 'RG-010', 'SUP-008', 'Toyota', 'Hiace Premio', 625000000, 10],
            ['Defibrillator', 'KAT-001', 'RG-002', 'SUP-005', 'Zoll', 'R Series', 95000000, 8],
            ['USG Portable', 'KAT-001', 'RG-006', 'SUP-007', 'GE Healthcare', 'Vscan Air', 135000000, 8],
        ];

        for ($index = 0; $index < 36; $index++) {
            [$name, $categoryCode, $roomCode, $supplierCode, $brand, $model, $price, $usefulLife] = $specifications[$index % count($specifications)];
            $condition = $index % 11 === 0 ? 'Rusak berat' : ($index % 5 === 0 ? 'Rusak ringan' : 'Baik');
            $status = $condition === 'Rusak berat' ? 'Tidak aktif' : ($index % 7 === 0 ? 'Dalam pemeliharaan' : 'Aktif');
            $assetCode = 'AST-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            Asset::withTrashed()->firstOrCreate(['asset_code' => $assetCode], [
                'category_id' => $categories->get($categoryCode) ?? throw new \LogicException('Jalankan CategorySeeder terlebih dahulu.'),
                'room_id' => $rooms->get($roomCode) ?? throw new \LogicException('Jalankan RoomSeeder terlebih dahulu.'),
                'supplier_id' => $suppliers->get($supplierCode) ?? throw new \LogicException('Jalankan SupplierSeeder terlebih dahulu.'),
                'created_by' => $creator->id,
                'name' => $name.' '.(intdiv($index, 12) + 1),
                'brand' => $brand,
                'model' => $model,
                'serial_number' => 'SN26-'.str_pad((string) ($index + 101), 6, '0', STR_PAD_LEFT),
                'description' => $name.' untuk mendukung pelayanan RS UMMI.',
                'acquisition_date' => sprintf('2026-%02d-%02d', $index % 10 + 1, $index % 25 + 1),
                'acquisition_price' => $price,
                'condition' => $condition,
                'status' => $status,
                'useful_life_years' => $usefulLife,
            ]);
        }
    }
}
