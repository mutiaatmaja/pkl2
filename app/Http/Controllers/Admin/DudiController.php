<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dudi;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function Spatie\LaravelPdf\Support\pdf;

class DudiController extends Controller
{
    public function suratPermohonan(Dudi $dudi)
    {
        $dudi->load([
            'siswas' => fn ($query) => $query
                ->with(['user:id,name', 'kelas:id,name'])
                ->orderBy('nis'),
        ]);

        $pengaturan = Pengaturan::instance();
        $tanggal = $pengaturan->tanggal_surat ?? now();

        $kopSuratPath = $pengaturan->kop_surat
            ? Storage::disk('public')->path($pengaturan->kop_surat)
            : null;

        $ttdPath = ($pengaturan->enable_ttd_scan && $pengaturan->ttd_pejabat)
            ? Storage::disk('public')->path($pengaturan->ttd_pejabat)
            : null;

        $dudiFileSegment = (string) Str::of($dudi->name)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '');

        $pdfFileName = 'SuratPermohonan_'.($dudiFileSegment !== '' ? $dudiFileSegment : $dudi->id).'.pdf';

        return pdf()
            ->view('pdf.surat-permohonan-magang', [
                'dudi' => $dudi,
                'siswas' => $dudi->siswas,
                'tanggal' => $tanggal,
                'pengaturan' => $pengaturan,
                'kopSuratPath' => $kopSuratPath,
                'ttdPath' => $ttdPath,
            ])
            ->name($pdfFileName)
            ->download();
    }

    public function downloadTemplate(): StreamedResponse
    {
        $rows = [];
        $rows[] = ['nama', 'alamat', 'aktif', 'kuota'];
        $rows[] = ['PT Contoh Perusahaan', 'Jl. Contoh Alamat No. 1, Pontianak', 'ya', '5'];
        $rows[] = ['CV Contoh Usaha', 'Jl. Contoh Alamat No. 2, Pontianak', '1', '3'];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, 'format-import-dudi.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
