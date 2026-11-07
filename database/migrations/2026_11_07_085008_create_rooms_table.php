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
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->string('code', 30);
            $table->string('name', 150);
            $table->string('floor', 20)->nullable();
            $table->string('department', 100)->nullable();
            $table->text('description')->nullable();
            $table->unique(['building_id', 'code']);
            $table->foreign('building_id')->references('id')->on('buildings')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
