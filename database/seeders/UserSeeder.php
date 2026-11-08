<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'name');
        $users = [
            ['Super Admin', 'superadmin@sismat.example', 'Super Admin'],
            ['Dewi Lestari', 'admin.aset@sismat.example', 'Admin Aset'],
            ['Siti Rahma', 'petugas@sismat.example', 'Petugas Ruangan'],
            ['Budi Santoso', 'budi.teknisi@sismat.example', 'Teknisi'],
            ['Rina Pratiwi', 'rina.teknisi@sismat.example', 'Teknisi'],
            ['Ahmad Fauzi', 'ahmad.teknisi@sismat.example', 'Teknisi'],
            ['dr. Andi Wijaya', 'pimpinan@sismat.example', 'Pimpinan'],
        ];

        foreach ($users as $index => [$name, $email, $role]) {
            User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'role_id' => $roles->get($role) ?? throw new \LogicException('Jalankan RoleSeeder terlebih dahulu.'),
                'phone' => '0812345600'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'is_active' => true,
                'email_verified_at' => now(),

                'password' => 'password',
            ]);
        }
    }
}
