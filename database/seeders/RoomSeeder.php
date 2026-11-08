<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $buildings = Building::query()->pluck('id', 'code');
        $rooms = [
            ['GD-001', 'RG-001', 'ICU', '2', 'Perawatan Intensif'],
            ['GD-001', 'RG-002', 'IGD', '1', 'Gawat Darurat'],
            ['GD-001', 'RG-003', 'Ruang Operasi', '3', 'Bedah Sentral'],
            ['GD-001', 'RG-004', 'Rawat Inap Melati', '4', 'Rawat Inap'],
            ['GD-002', 'RG-005', 'Laboratorium', '1', 'Patologi Klinik'],
            ['GD-002', 'RG-006', 'Radiologi', '1', 'Radiologi'],
            ['GD-002', 'RG-007', 'Farmasi', '2', 'Farmasi'],
            ['GD-003', 'RG-008', 'Ruang Server', '2', 'Teknologi Informasi'],
            ['GD-003', 'RG-009', 'Administrasi', '1', 'Administrasi Umum'],
            ['GD-003', 'RG-010', 'Garasi Ambulans', 'Dasar', 'Transportasi'],
        ];

        foreach ($rooms as [$buildingCode, $code, $name, $floor, $department]) {
            Room::firstOrCreate([
                'building_id' => $buildings->get($buildingCode) ?? throw new \LogicException('Jalankan BuildingSeeder terlebih dahulu.'),
                'code' => $code,
            ], [
                'name' => $name,
                'floor' => $floor,
                'department' => $department,
                'description' => 'Lokasi inventaris unit '.$department.'.',
            ]);
        }
    }
}
