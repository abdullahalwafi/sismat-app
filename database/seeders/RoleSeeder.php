<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin' => 'Mengelola seluruh fitur dan konfigurasi aplikasi.',
            'Admin Aset' => 'Mengelola inventaris dan operasional aset rumah sakit.',
            'Petugas Ruangan' => 'Memantau aset ruangan dan mengajukan perpindahan.',
            'Teknisi' => 'Melakukan pemeriksaan, perawatan, dan perbaikan aset.',
            'Pimpinan' => 'Meninjau laporan dan menyetujui penghapusan aset.',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(['name' => $name], ['description' => $description, 'permissions' => Role::defaultPermissions($name)]);
        }
    }
}
