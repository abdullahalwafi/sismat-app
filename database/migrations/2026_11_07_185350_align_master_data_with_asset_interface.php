<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buildings', function (Blueprint $table): void {
            $table->unsignedSmallInteger('floors')->default(1);
        });
        Schema::table('rooms', function (Blueprint $table): void {
            $table->unsignedInteger('capacity')->default(30);
            $table->string('status', 30)->default('Aktif');
        });
        Schema::table('assets', function (Blueprint $table): void {
            $table->string('photo_path')->nullable();
        });
        Schema::table('roles', function (Blueprint $table): void {
            $table->json('permissions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', fn (Blueprint $table) => $table->dropColumn('permissions'));
        Schema::table('assets', fn (Blueprint $table) => $table->dropColumn('photo_path'));
        Schema::table('rooms', fn (Blueprint $table) => $table->dropColumn(['capacity', 'status']));
        Schema::table('buildings', fn (Blueprint $table) => $table->dropColumn('floors'));
    }
};
