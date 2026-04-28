<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dudi;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Spatie\LaravelPdf\Support\pdf;

class SuratController extends Controller
{
    public function suratPermohonan(Dudi $dudi)
    {
        $siswa = Auth::user()?->siswa;

        if (! $siswa || (int) $siswa->dudi_id !== (int) $dudi->id) {
            abort(403);
        }

        $dudi->load([
            'siswas' => fn ($query) => $query
                ->with(['user:id,name', 'kelas:id,name', 'jurusan:id,name'])
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
            ->paperSize(210, 330, 'mm')
            ->name($pdfFileName)
            ->download();
    }
}
