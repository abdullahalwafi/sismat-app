<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['SUP-001', 'PT Medika Nusantara', 'Andi Prasetyo', '021-5550101', 'Jakarta Pusat'],
            ['SUP-002', 'PT Sains Diagnostik Indonesia', 'Siti Aminah', '021-5550102', 'Jakarta Selatan'],
            ['SUP-003', 'PT Solusi Informatika', 'Hendra Saputra', '021-5550103', 'Tangerang'],
            ['SUP-004', 'CV Sarana Rumah Sakit', 'Ratna Sari', '021-5550104', 'Bekasi'],
            ['SUP-005', 'PT Mitra Alkes Sejahtera', 'Dedi Kurniawan', '021-5550105', 'Depok'],
            ['SUP-006', 'CV Prima Furnitur', 'Sri Wahyuni', '021-5550106', 'Bogor'],
            ['SUP-007', 'PT Teknologi Sehat', 'Rizky Pratama', '021-5550107', 'Jakarta Barat'],
            ['SUP-008', 'PT Armada Medika', 'Lina Marlina', '021-5550108', 'Jakarta Timur'],
        ];

        foreach ($suppliers as $index => [$code, $name, $contact, $phone, $city]) {
            Supplier::firstOrCreate(['code' => $code], [
                'name' => $name,
                'contact_person' => $contact,
                'phone' => $phone,
                'email' => 'kontak'.($index + 1).'@supplier.example',
                'address' => 'Jl. Kesehatan No. '.($index + 10).', '.$city,
            ]);
        }
    }
}
