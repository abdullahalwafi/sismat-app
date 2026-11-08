<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['KAT-001', 'Alat Medis', 'Peralatan diagnosis, pemantauan, dan terapi pasien.'],
            ['KAT-002', 'Alat Laboratorium', 'Peralatan pemeriksaan sampel dan penunjang diagnosis.'],
            ['KAT-003', 'Peralatan IT', 'Komputer, jaringan, dan perangkat pendukung sistem informasi.'],
            ['KAT-004', 'Furniture', 'Tempat tidur, meja, kursi, dan perlengkapan ruangan.'],
            ['KAT-005', 'Peralatan Kantor', 'Peralatan administrasi dan pengarsipan.'],
            ['KAT-006', 'Kendaraan', 'Kendaraan operasional dan transportasi pasien.'],
        ];

        foreach ($categories as [$code, $name, $description]) {
            Category::firstOrCreate(['code' => $code], compact('name', 'description'));
        }
    }
}
