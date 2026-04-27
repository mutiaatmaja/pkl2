<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DudiRequest extends Model
{
    protected $fillable = [
        'siswa_id',
        'name',
        'address',
        'panggilan_pimpinan',
        'kuota',
        'status',
        'admin_feedback',
        'reviewed_by',
        'reviewed_at',
        'approved_dudi_id',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedDudi(): BelongsTo
    {
        return $this->belongsTo(Dudi::class, 'approved_dudi_id');
    }
}
