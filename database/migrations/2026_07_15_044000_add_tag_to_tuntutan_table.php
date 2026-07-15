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
        Schema::table('tuntutan', function (Blueprint $table) {
            $table->string('tag')->default('Stok')->after('nama_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuntutan', function (Blueprint $table) {
            $table->dropColumn('tag');
        });
    }
};
