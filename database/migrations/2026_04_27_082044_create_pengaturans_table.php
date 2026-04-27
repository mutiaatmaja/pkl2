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
        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->default('421.5/SMKN7-PKL/{tahun}/{nomor}');
            $table->string('pejabat_penandatangan')->default('Kepala SMKN 7 Pontianak');
            $table->string('jabatan_penandatangan')->default('Kepala Sekolah');
            $table->date('tanggal_surat')->nullable();
            $table->string('ttd_pejabat')->nullable();
            $table->boolean('enable_ttd_scan')->default(false);
            $table->string('lokasi_penerbitan')->default('Pontianak');
            $table->string('kop_surat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturans');
    }
};
