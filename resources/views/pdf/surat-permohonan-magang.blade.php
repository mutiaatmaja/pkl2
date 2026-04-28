<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Surat Permohonan PKL</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .kop {
            text-align: center;
            border-bottom: 2px solid black;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid black;
            padding: 5px;
            font-size: 11px;
        }

        .fit-content {
            width: 1%;
            white-space: nowrap;
        }

        .ttd {
            width: 100%;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    @php
        $nomorSurat = str_replace(['{tahun}', '{nomor}'], [$tanggal->format('Y'), $dudi->id], $pengaturan->nomor_surat);

        $periodeMulai = $pengaturan->periode_pkl_mulai;
        $periodeSelesai = $pengaturan->periode_pkl_selesai;
        $periodePklText = null;
        $tujuanKota = 'Pontianak';

        if ($periodeMulai && $periodeSelesai) {
            $periodePklText =
                $periodeMulai->translatedFormat('d F Y') . ' s.d. ' . $periodeSelesai->translatedFormat('d F Y');
        } elseif ($periodeMulai) {
            $periodePklText = 'mulai ' . $periodeMulai->translatedFormat('d F Y');
        } elseif ($periodeSelesai) {
            $periodePklText = 'sampai ' . $periodeSelesai->translatedFormat('d F Y');
        }

        $ttdNamaJabatan = $pengaturan->jabatan_penandatangan ?: 'WAKA HUMAS';
        $ttdNamaPejabat = $pengaturan->pejabat_penandatangan ?: '-';
        $ttdNip = $pengaturan->nip_penandatangan ?: null;

        $kopSuratBase64 = null;
        if ($kopSuratPath && is_file($kopSuratPath)) {
            $kopSuratData = file_get_contents($kopSuratPath);
            if ($kopSuratData !== false) {
                $kopMimeType = mime_content_type($kopSuratPath) ?: 'image/png';
                $kopSuratBase64 = 'data:' . $kopMimeType . ';base64,' . base64_encode($kopSuratData);
            }
        }

        $ttdBase64 = null;
        if ($ttdPath && is_file($ttdPath)) {
            $ttdData = file_get_contents($ttdPath);
            if ($ttdData !== false) {
                $ttdMimeType = mime_content_type($ttdPath) ?: 'image/png';
                $ttdBase64 = 'data:' . $ttdMimeType . ';base64,' . base64_encode($ttdData);
            }
        }
    @endphp

    @if ($kopSuratBase64)
        <div style="margin-bottom: 15px;">
            <img src="{{ $kopSuratBase64 }}" alt="Kop Surat" style="width: 100%; height: auto;">
        </div>
    @else
        <div class="kop">
            <div><strong>PEMERINTAH PROVINSI KALIMANTAN BARAT</strong></div>
            <div><strong>SMK NEGERI 7 PONTIANAK</strong></div>
            <div>Jalan Tanjung Raya II Pontianak Timur, Kalimantan Barat 78232</div>
            <div>Website: smkn7ptk.sch.id | WA: 08115784200 | NPSN 30107398</div>
        </div>
    @endif

    <table>
        <tr>
            <td style="width: 80px;">Nomor</td>
            <td style="width: 10px;">:</td>
            <td>{{ $nomorSurat }}</td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td>Permohonan Praktek Kerja Lapangan (PKL)</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>-</td>
        </tr>
    </table>

    <br>

    <p>
        Kepada <br>
        Yth. {{ $dudi->panggilan_pimpinan }} {{ $dudi->name }} <br>
        di <br>
        {{ $tujuanKota }}
    </p>

    <p>
        Dengan Hormat,
    </p>

    <p>
        Dalam rangka pelaksanaan Pendidikan Vokasi terkait dengan program link and match guna meningkatkan
        kompetensi, peserta didik diwajibkan untuk melaksanakan Praktik Kerja Lapangan (PKL).
        Oleh karena itu kami mengajukan permohonan kepada Bapak/Ibu agar dapat menerima peserta didik kami sebagai
        berikut:
    </p>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 34px;">No</th>
                <th>Nama</th>
                <th class="fit-content">L/P</th>
                <th class="fit-content">NISN</th>
                <th>Alamat</th>
                <th style="width: 95px;">No HP</th>
                <th class="fit-content">Kelas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($siswas as $index => $siswa)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $siswa->user?->name ?? '-' }}</td>
                    <td class="fit-content" style="text-align: center;">{{ $siswa->jenis_kelamin ?? '-' }}</td>
                    <td class="fit-content">{{ $siswa->nisn }}</td>
                    <td>{{ $siswa->alamat ?? '-' }}</td>
                    <td>{{ $siswa->no_hp ?? '-' }}</td>
                    <td class="fit-content">{{ $siswa->kelas?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Belum ada peserta terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        Adapun pelaksanaan PKL direncanakan pada tanggal {{ $periodePklText ?? '-' }}.
    </p>

    <p>
        Demikian surat permohonan ini kami ajukan, atas perhatian Bapak/Ibu kami ucapkan terima kasih.
    </p>

    <div class="ttd">
        <div style="float:right; text-align:center;">
            {{ $pengaturan->lokasi_penerbitan }}, {{ $tanggal->translatedFormat('d F Y') }} <br><br>

            a.n Kepala SMK Negeri 7 Pontianak <br><br>

            <strong>{{ $ttdNamaJabatan }}</strong><br>

            @if ($ttdBase64)
                <img src="{{ $ttdBase64 }}" alt="Tanda Tangan"
                    style="max-height: 72px; width: auto; margin: 8px 0 6px;"><br>
            @else
                <br><br><br><br>
            @endif

            <strong>{{ $ttdNamaPejabat }}</strong><br>
            @if ($ttdNip)
                NIP. {{ $ttdNip }}
            @endif
        </div>
    </div>
</body>

</html>
