<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dudi;
use App\Models\Jurusan;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

class SiswaController extends Controller
{
    public function rekapDudiPdf()
    {
        $dudis = Dudi::query()
            ->with([
                'siswas' => fn ($query) => $query
                    ->with(['user:id,name'])
                    ->orderBy('nis'),
            ])
            ->whereHas('siswas')
            ->orderBy('name')
            ->get();

        $fileSegment = (string) Str::of(now()->format('Ymd_His'))->ascii();

        return pdf()
            ->view('pdf.rekap-dudi-menerima-siswa', [
                'dudis' => $dudis,
                'tanggalCetak' => now(),
            ])
            ->paperSize(210, 297, 'mm')
            ->margins(top: 12, right: 10, bottom: 12, left: 10, unit: 'mm')
            ->name('RekapDudiMenerimaSiswa_'.$fileSegment.'.pdf')
            ->download();
    }

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
