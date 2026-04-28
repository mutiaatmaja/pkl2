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
        Schema::table('pengaturans', function (Blueprint $table) {
            $table->date('periode_pkl_mulai')->nullable()->after('tanggal_surat');
            $table->date('periode_pkl_selesai')->nullable()->after('periode_pkl_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturans', function (Blueprint $table) {
            $table->dropColumn(['periode_pkl_mulai', 'periode_pkl_selesai']);
        });
    }
};
