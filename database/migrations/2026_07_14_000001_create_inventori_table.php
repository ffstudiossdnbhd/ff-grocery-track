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
        Schema::create('inventori', function (Blueprint $table) {
            $table->id();
            $table->string('nama_item');
            $table->string('kategori');
            $table->integer('jumlah_keseluruhan')->default(0);
            $table->integer('jumlah_belum_dibuka')->default(0);
            $table->integer('peratus_baki')->default(100); // 0 to 100
            $table->date('tarikh_luput')->nullable();
            $table->boolean('jejak_luput')->default(true);
            $table->integer('had_ambang')->default(0);
            $table->foreignId('dicipta_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('dikemaskini_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventori');
    }
};
