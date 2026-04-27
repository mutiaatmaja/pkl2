<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaController extends Controller
{
    public function downloadTemplate(): StreamedResponse
    {
        $jurusans = Jurusan::with('kelas')->get();

        $rows = [];
        $rows[] = ['nis', 'nisn', 'nama', 'kode_jurusan', 'nama_kelas'];
        $rows[] = ['2407099', '0099999001', 'Contoh Nama Siswa', 'RPL', 'XI-RPL-A'];

        foreach ($jurusans->take(3) as $jurusan) {
            foreach ($jurusan->kelas->take(2) as $kelas) {
                $rows[] = ['', '', '', $jurusan->code, $kelas->name];
            }
        }

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, 'format-import-siswa.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
