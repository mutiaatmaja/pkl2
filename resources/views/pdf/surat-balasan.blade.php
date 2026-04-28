<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Balasan PKL</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            line-height: 1.6;
        }

        .container {
            width: 100%;
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

        $tanggalSurat = ($pengaturan->tanggal_surat ?? $tanggal)->translatedFormat('d F Y');
        $periodeMulai = $pengaturan->periode_pkl_mulai;
        $periodeSelesai = $pengaturan->periode_pkl_selesai;
        $periode = null;

        if ($periodeMulai && $periodeSelesai) {
            $periode = $periodeMulai->translatedFormat('d F Y') . ' s.d ' . $periodeSelesai->translatedFormat('d F Y');
        } elseif ($periodeMulai) {
            $periode = 'mulai ' . $periodeMulai->translatedFormat('d F Y');
        } elseif ($periodeSelesai) {
            $periode = 'sampai ' . $periodeSelesai->translatedFormat('d F Y');
        }
        $status = 'BERSEDIA';
    @endphp

    <div class="container">

        {{-- Tujuan --}}
        <p>
            Kepada <br>
            Yth. Kepala SMK Negeri 7 Pontianak <br>
            Di <br>
            Pontianak
        </p>

        {{-- Pembuka --}}
        <p>Dengan hormat,</p>

        {{-- Isi --}}
        <p>
            Menanggapi surat saudara Nomor : {{ $nomorSurat }},
            Tanggal {{ $tanggalSurat }},
            tentang Permohonan Praktek Kerja Lapangan di Perusahaan/Instansi kami,
            pada prinsipnya kami :.
        </p>

        <p style="text-align:center; font-weight:bold; letter-spacing:2px;">
            BERSEDIA / BELUM BERSEDIA
        </p>

        {{-- Tabel --}}
        @if (count($siswas ?? []) > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama</th>
                        <th style="width:60px;">L/P</th>
                        <th style="width:120px;">NISN</th>
                        <th style="width:150px;">Program Keahlian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($siswas as $i => $siswa)
                        <tr>
                            <td style="text-align:center;">{{ $i + 1 }}</td>
                            <td>{{ $siswa->user?->name ?? '-' }}</td>
                            <td style="text-align:center;">{{ $siswa->jenis_kelamin ?? '-' }}</td>
                            <td>{{ $siswa->nisn }}</td>
                            <td>{{ $siswa->jurusan?->name ?? ($siswa->kelas?->name ?? '-') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Isi lanjutan --}}
        <p>
            Menerima Peserta Didik PKL pada tanggal
            {{ $periode ?? '1 Juli s.d 31 Desember 2026' }}.
        </p>

        <p>
            Dengan catatan selama pelaksanaan PKL mematuhi dan ikut menjaga/memelihara
            tata tertib serta keamanan yang berlaku di Perusahaan/Industri kami.
        </p>

        <p>
            Demikian surat kami sampaikan, atas perhatian dan kerjasama yang baik
            dari saudara kami ucapkan terima kasih.
        </p>

        {{-- TTD --}}
        <div class="ttd">
            <div style="float:right; text-align:left;">
                {{ $pengaturan->lokasi_penerbitan ?? 'Pontianak' }}, ____________________<br><br><br><br>

                <strong>(____________________________)</strong><br>
                ____________________________
            </div>
        </div>

    </div>

</body>

</html>
