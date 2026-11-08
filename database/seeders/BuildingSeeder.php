<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        $buildings = [
            ['GD-001', 'Gedung A — Pelayanan', 'Sayap timur RS UMMI', 'Pelayanan gawat darurat, operasi, dan rawat inap.'],
            ['GD-002', 'Gedung B — Penunjang', 'Sayap barat RS UMMI', 'Laboratorium, radiologi, dan farmasi.'],
            ['GD-003', 'Gedung C — Administrasi', 'Area utama RS UMMI', 'Administrasi, teknologi informasi, dan kendaraan operasional.'],
        ];

        foreach ($buildings as $index => [$code, $name, $address, $description]) {
            Building::firstOrCreate(['code' => $code], array_merge(compact('name', 'address', 'description'), ['floors' => [4, 3, 2][$index]]));
        }
    }
}
