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
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithCustomCsvSettings, WithHeadingRow
{
    use Importable, SkipsErrors, SkipsFailures;
    use RemembersRowNumber;

    public int $importedCount = 0;

    public int $skippedCount = 0;

    /** @var array<int, string> */
    public array $skipReasons = [];

    public function model(array $row): ?Siswa
    {
        $normalizedRow = $this->normalizeRowKeys($row);

        $nis = trim((string) ($normalizedRow['nis'] ?? ''));
        $nisn = trim((string) ($normalizedRow['nisn'] ?? ''));
        $nama = trim((string) ($normalizedRow['nama'] ?? ''));
        $kodeJurusan = strtoupper(trim((string) ($normalizedRow['kode_jurusan'] ?? '')));
        $namaKelas = trim((string) ($normalizedRow['nama_kelas'] ?? ''));

        if ($nis === '' || $nisn === '' || $nama === '' || $kodeJurusan === '' || $namaKelas === '') {
            $this->addSkip('Kolom wajib kosong (nis/nisn/nama/kode_jurusan/nama_kelas).');

            return null;
        }

        $jurusan = Jurusan::where('code', $kodeJurusan)->first();
        if (! $jurusan) {
            $this->addSkip('Kode jurusan tidak ditemukan: '.$kodeJurusan.'.');

            return null;
        }

        $kelas = Kelas::where('jurusan_id', $jurusan->id)
            ->where('name', $namaKelas)
            ->first();
        if (! $kelas) {
            $this->addSkip('Nama kelas tidak ditemukan pada jurusan '.$kodeJurusan.': '.$namaKelas.'.');

            return null;
        }

        if (Siswa::where('nisn', $nisn)->exists() || Siswa::where('nis', $nis)->exists()) {
            $this->addSkip('Data duplikat (nis/nisn sudah ada): '.$nis.' / '.$nisn.'.');

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

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
        ];
    }

    private function normalizeRowKeys(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalizedKey = (string) $key;
            $normalizedKey = str_replace("\u{FEFF}", '', $normalizedKey);
            $normalizedKey = trim($normalizedKey);
            $normalizedKey = (string) Str::of($normalizedKey)
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_');

            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }

    private function addSkip(string $message): void
    {
        $this->skippedCount++;

        if (count($this->skipReasons) >= 5) {
            return;
        }

        $this->skipReasons[] = 'Baris '.$this->getRowNumber().': '.$message;
    }

    /** @return int Total rows skipped (validation failures + business logic skips + db errors) */
    public function totalSkipped(): int
    {
        return $this->skippedCount
            + count($this->failures())
            + count($this->errors());
    }
}
