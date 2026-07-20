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
        Schema::table('inventori', function (Blueprint $table) {
            $table->string('jenama')->nullable()->after('kategori');
            $table->string('jenis')->nullable()->after('jenama');
            $table->string('capacity')->nullable()->after('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventori', function (Blueprint $table) {
            $table->dropColumn(['jenama', 'jenis', 'capacity']);
        });
    }
};
