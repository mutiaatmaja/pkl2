<?php

namespace App\Imports;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    /** @var int */
    public int $importedCount = 0;

    /** @var int */
    public int $skippedCount = 0;

    public function model(array $row): ?Siswa
    {
        $jurusan = Jurusan::where('code', strtoupper(trim($row['kode_jurusan'])))->first();
        if (! $jurusan) {
            $this->skippedCount++;

            return null;
        }

        $kelas = Kelas::where('jurusan_id', $jurusan->id)
            ->where('name', trim($row['nama_kelas']))
            ->first();
        if (! $kelas) {
            $this->skippedCount++;

            return null;
        }

        $nisn = trim($row['nisn']);
        $nis = trim($row['nis']);
        $nama = trim($row['nama']);

        if (Siswa::where('nisn', $nisn)->exists() || Siswa::where('nis', $nis)->exists()) {
            $this->skippedCount++;

            return null;
        }

        $siswaRole = Role::where('name', 'siswa')->first();

        $user = User::firstOrCreate(
            ['email' => $nisn.'@claim.smkn7.local'],
            [
                'name' => $nama,
                'password' => Hash::make(Str::password(20)),
                'email_verified_at' => null,
            ]
        );

        if ($siswaRole) {
            $user->syncRoles([$siswaRole]);
        }

        $this->importedCount++;

        return new Siswa([
            'user_id' => $user->id,
            'jurusan_id' => $jurusan->id,
            'kelas_id' => $kelas->id,
            'nis' => $nis,
            'nisn' => $nisn,
        ]);
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:30',
            'nisn' => 'required|string|max:30',
            'nama' => 'required|string|max:255',
            'kode_jurusan' => 'required|string|max:20',
            'nama_kelas' => 'required|string|max:100',
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'nis' => 'NIS',
            'nisn' => 'NISN',
            'nama' => 'Nama',
            'kode_jurusan' => 'Kode Jurusan',
            'nama_kelas' => 'Nama Kelas',
        ];
    }

    /** @return int Total rows skipped (validation failures + business logic skips + db errors) */
    public function totalSkipped(): int
    {
        return $this->skippedCount
            + count($this->failures())
            + count($this->errors());
    }
}

    /** @var int */
    public int $importedCount = 0;

    /** @var int */
    public int $skippedCount = 0;

    public function model(array $row): ?Siswa
    {
        $jurusan = Jurusan::where('code', strtoupper(trim($row['kode_jurusan'])))->first();
        if (! $jurusan) {
            $this->skippedCount++;

            return null;
        }

        $kelas = Kelas::where('jurusan_id', $jurusan->id)
            ->where('name', trim($row['nama_kelas']))
            ->first();
        if (! $kelas) {
            $this->skippedCount++;

            return null;
        }

        $nisn = trim($row['nisn']);
        $nis = trim($row['nis']);
        $nama = trim($row['nama']);

        if (Siswa::where('nisn', $nisn)->exists() || Siswa::where('nis', $nis)->exists()) {
            $this->skippedCount++;

            return null;
        }

        $siswaRole = Role::where('name', 'siswa')->first();

        $user = User::firstOrCreate(
            ['email' => $nisn.'@claim.smkn7.local'],
            [
                'name' => $nama,
                'password' => Hash::make(Str::password(20)),
                'email_verified_at' => null,
            ]
        );

        if ($siswaRole) {
            $user->syncRoles([$siswaRole]);
        }

        $this->importedCount++;

        return new Siswa([
            'user_id' => $user->id,
            'jurusan_id' => $jurusan->id,
            'kelas_id' => $kelas->id,
            'nis' => $nis,
            'nisn' => $nisn,
        ]);
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:30',
            'nisn' => 'required|string|max:30',
            'nama' => 'required|string|max:255',
            'kode_jurusan' => 'required|string|max:20',
            'nama_kelas' => 'required|string|max:100',
        ];
    }
}
