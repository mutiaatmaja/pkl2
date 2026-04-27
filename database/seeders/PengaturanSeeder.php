<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Pengaturan::firstOrCreate([], [
            'nomor_surat' => '421.5/SMKN7-PKL/{tahun}/{nomor}',
            'pejabat_penandatangan' => 'Kepala SMKN 7 Pontianak',
            'jabatan_penandatangan' => 'Kepala Sekolah',
            'nip_penandatangan' => null,
            'tanggal_surat' => null,
            'ttd_pejabat' => null,
            'enable_ttd_scan' => false,
            'lokasi_penerbitan' => 'Pontianak',
            'kop_surat' => null,
        ]);
    }
}
