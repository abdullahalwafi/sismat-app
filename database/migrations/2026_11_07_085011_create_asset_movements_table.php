<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_movements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('from_room_id');
            $table->unsignedBigInteger('to_room_id');
            $table->unsignedBigInteger('moved_by');
            $table->string('movement_code', 50)->unique();
            $table->date('movement_date');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('from_room_id')->references('id')->on('rooms')->restrictOnDelete();
            $table->foreign('to_room_id')->references('id')->on('rooms')->restrictOnDelete();
            $table->foreign('moved_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
