<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        ActivityLog::firstOrCreate([
            'action' => 'Inisialisasi', 'module' => 'Sistem',

        ], [
            'description' => 'Data awal inventaris rumah sakit berhasil dimuat.',
            'user_id' => User::query()->where('email', 'superadmin@sismat.example')->firstOrFail()->id,
        ]);
    }
}
