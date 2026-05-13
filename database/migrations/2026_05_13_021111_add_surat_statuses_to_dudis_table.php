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
        Schema::table('dudis', function (Blueprint $table) {
            $table->boolean('sudah_cetak_surat')->default(false)->after('kuota');
            $table->boolean('diterima')->default(false)->after('sudah_cetak_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dudis', function (Blueprint $table) {
            $table->dropColumn(['sudah_cetak_surat', 'diterima']);
        });
    }
};
