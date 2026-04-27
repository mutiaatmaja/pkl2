<?php

namespace App\Imports;

use App\Models\Dudi;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DudiImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    /** @var int */
    public int $importedCount = 0;

    /** @var int */
    public int $skippedCount = 0;

    public function model(array $row): ?Dudi
    {
        $nama = trim($row['nama']);

        if (Dudi::where('name', $nama)->exists()) {
            $this->skippedCount++;

            return null;
        }

        $aktifRaw = strtolower(trim((string) $row['aktif']));
        $aktif = in_array($aktifRaw, ['1', 'ya', 'yes', 'true', 'aktif'], true);

        $kuota = (int) $row['kuota'];
        if ($kuota < 1) {
            $kuota = 1;
        }

        $this->importedCount++;

        return new Dudi([
            'name' => $nama,
            'address' => trim($row['alamat']),
            'aktif' => $aktif,
            'kuota' => $kuota,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'aktif' => 'required',
            'kuota' => 'required|integer|min:1',
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'nama' => 'Nama DUDI',
            'alamat' => 'Alamat',
            'aktif' => 'Status Aktif',
            'kuota' => 'Kuota',
        ];
    }

    public function totalSkipped(): int
    {
        return $this->skippedCount + count($this->failures()) + count($this->errors());
    }
}
