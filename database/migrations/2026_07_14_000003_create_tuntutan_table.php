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
        Schema::create('tuntutan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_item');
            $table->decimal('nilai_tuntutan', 10, 2);
            $table->date('tarikh_beli');
            $table->string('minggu_tuntutan'); // Format: YYYY-Www (e.g. 2026-W29)
            $table->string('status')->default('Dalam Proses'); // Dalam Proses, Selesai, Ditolak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuntutan');
    }
};
