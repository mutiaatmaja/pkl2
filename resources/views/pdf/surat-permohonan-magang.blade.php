<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Surat Permohonan PKL</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.55;
        }

        .container {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
        }

        .kop-default {
            border-bottom: 3px solid #0e7490;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .kop-default .sekolah-name {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0e7490;
        }

        .kop-default .sekolah-sub {
            font-size: 11px;
            color: #374151;
            margin-top: 2px;
        }

        .kop-default .sekolah-contact {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .kop-image {
            width: 100%;
            margin-bottom: 8px;
        }

        .title {
            text-align: center;
            font-weight: 700;
            text-decoration: underline;
            margin-top: 20px;
            margin-bottom: 2px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 24px;
        }

        .meta {
            margin-bottom: 14px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 18px;
        }

        .table th,
        .table td {
            border: 1px solid #334155;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table th {
            background: #f1f5f9;
            font-weight: 700;
        }

        .footer {
            margin-top: 36px;
            text-align: right;
        }

        .ttd-img {
            max-height: 70px;
            width: auto;
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- Kop Surat --}}
        @if ($kopSuratPath && file_exists($kopSuratPath))
            <img src="{{ $kopSuratPath }}" alt="Kop Surat" class="kop-image">
        @else
            <div class="kop-default">
                <div class="sekolah-name">SMK Negeri 7 Pontianak</div>
                <div class="sekolah-sub">Sekolah Menengah Kejuruan Negeri 7 Kota Pontianak</div>
                <div class="sekolah-contact">Jl. Sungai Raya Dalam, Pontianak &bull; Telp. (0561) 000000 &bull; Email:
                    smkn7pontianak@example.com</div>
            </div>
        @endif

        <div class="title">SURAT PERMOHONAN PRAKTIK KERJA LAPANGAN</div>
        <div class="subtitle">
            Nomor:
            {{ str_replace(['{tahun}', '{nomor}'], [$tanggal->format('Y'), $dudi->id], $pengaturan->nomor_surat) }}
        </div>

        <p class="meta">
            {{ $pengaturan->lokasi_penerbitan }}, {{ $tanggal->translatedFormat('d F Y') }}
        </p>

        <p>
            Kepada Yth.<br>
            {{ $dudi->panggilan_pimpinan }} {{ $dudi->name }}<br>
            di Tempat
        </p>

        <p>
            Dengan hormat,
        </p>

        <p>
            Sehubungan dengan program pembelajaran Praktik Kerja Lapangan (PKL), kami memohon kesediaan
            {{ $dudi->name }} untuk menerima peserta didik dari SMKN 7 Pontianak sebagai peserta PKL.
            Adapun nama-nama peserta didik yang kami usulkan adalah sebagai berikut:
        </p>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama</th>
                    <th style="width: 120px;">NIS</th>
                    <th style="width: 120px;">NISN</th>
                    <th style="width: 130px;">Kelas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($siswas as $index => $siswa)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $siswa->user?->name ?? '-' }}</td>
                        <td>{{ $siswa->nis }}</td>
                        <td>{{ $siswa->nisn }}</td>
                        <td>{{ $siswa->kelas?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">Belum ada peserta terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p>
            Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami
            ucapkan terima kasih.
        </p>

        <div class="footer">
            {{ $pengaturan->lokasi_penerbitan }}, {{ $tanggal->translatedFormat('d F Y') }}<br>
            {{ $pengaturan->jabatan_penandatangan }},<br>
            @if ($ttdPath && file_exists($ttdPath))
                <img src="{{ $ttdPath }}" alt="Tanda Tangan" class="ttd-img">
            @else
                <br><br><br><br>
            @endif
            <strong>{{ $pengaturan->pejabat_penandatangan }}</strong><br>
            @if ($pengaturan->nip_penandatangan)
                NIP. {{ $pengaturan->nip_penandatangan }}
            @endif
        </div>

    </div>
</body>

</html>
