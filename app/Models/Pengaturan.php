<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $fillable = [
        'nomor_surat',
        'pejabat_penandatangan',
        'jabatan_penandatangan',
        'nip_penandatangan',
        'tanggal_surat',
        'ttd_pejabat',
        'enable_ttd_scan',
        'lokasi_penerbitan',
        'kop_surat',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'enable_ttd_scan' => 'boolean',
    ];

    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'nomor_surat' => '421.5/SMKN7-PKL/{tahun}/{nomor}',
            'pejabat_penandatangan' => 'Kepala SMKN 7 Pontianak',
            'jabatan_penandatangan' => 'Kepala Sekolah',
            'nip_penandatangan' => null,
            'lokasi_penerbitan' => 'Pontianak',
            'enable_ttd_scan' => false,
        ]);
    }
}
