<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dudi extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'panggilan_pimpinan',
        'address',
        'aktif',
        'kuota',
        'sudah_cetak_surat',
        'diterima',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'sudah_cetak_surat' => 'boolean',
            'diterima' => 'boolean',
        ];
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}
