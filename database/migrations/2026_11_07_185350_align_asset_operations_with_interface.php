<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table): void {
            $table->string('schedule_code', 50)->nullable()->unique();
        });
        Schema::table('asset_documents', function (Blueprint $table): void {
            $table->string('document_code', 50)->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('document_type', 50)->default('Pengadaan');
            $table->date('document_date')->nullable();
        });
        Schema::table('asset_movements', function (Blueprint $table): void {

            $table->string('status', 30)->default('Selesai');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
        });
        Schema::table('asset_disposals', function (Blueprint $table): void {
            $table->string('disposal_code', 50)->nullable()->unique();
            $table->string('status', 30)->default('Menunggu persetujuan');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        DB::table('maintenance_schedules')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('maintenance_schedules')->where('id', $row->id)->update(['schedule_code' => 'JDL-LEGACY-'.$row->id]);
            }
        });
        DB::table('asset_documents')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('asset_documents')->where('id', $row->id)->update([
                    'document_code' => 'DOK-LEGACY-'.$row->id, 'name' => $row->file_name,
                    'document_date' => substr($row->created_at ?? now()->toDateString(), 0, 10),
                ]);
            }
        });
        DB::table('asset_disposals')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('asset_disposals')->where('id', $row->id)->update([
                    'disposal_code' => 'HPS-LEGACY-'.$row->id,
                    'status' => $row->approved_by ? 'Disetujui' : 'Menunggu persetujuan',
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('asset_disposals', fn (Blueprint $table) => $table->dropColumn(['disposal_code', 'status', 'approved_at', 'completed_at']));
        Schema::table('asset_movements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at']);
        });
        Schema::table('asset_documents', fn (Blueprint $table) => $table->dropColumn(['document_code', 'name', 'document_type', 'document_date']));
        Schema::table('maintenance_schedules', fn (Blueprint $table) => $table->dropColumn('schedule_code'));
    }
};
