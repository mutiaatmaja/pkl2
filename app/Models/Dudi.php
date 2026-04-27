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
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}
