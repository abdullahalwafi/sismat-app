<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::transaction(function (): void {

            $this->call([
                RoleSeeder::class,
                UserSeeder::class,
                CategorySeeder::class,
                BuildingSeeder::class,
                RoomSeeder::class,
                SupplierSeeder::class,
                AssetSeeder::class,
                AssetDocumentSeeder::class,
                MaintenanceSeeder::class,
                MaintenanceScheduleSeeder::class,
                AssetMovementSeeder::class,
                AssetDisposalSeeder::class,
                ActivityLogSeeder::class,
            ]);
        });
    }
}
