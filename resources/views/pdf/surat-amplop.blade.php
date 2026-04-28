<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Amplop Surat PKL</title>
    <style>
        @page {
            size: 25cm 10cm;
            margin: 0;
        }

        body {
            margin: 0;
            font-family: 'Times New Roman', serif;
            color: #000;
        }

        .envelope {
            width: 25cm;
            height: 10cm;
            box-sizing: border-box;
            padding: 0.6cm 0.8cm;
        }

        .kop {
            width: 100%;
            margin-bottom: 8px;
        }

        .kop img {
            width: 100%;
            height: auto;
            display: block;
        }

        .kop-fallback {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: center;
            font-size: 11px;
            line-height: 1.35;
            box-sizing: border-box;
        }

        .meta {
            width: 58%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 11px;
        }

        .meta td {
            padding: 1px 0;
            vertical-align: top;
        }

        .meta .label {
            width: 60px;
            white-space: nowrap;
        }

        .meta .separator {
            width: 10px;
            text-align: center;
        }

        .target {
            width: 42%;
            margin-left: auto;
            border: 1px solid #000;
            padding: 8px 10px;
            min-height: 2.7cm;
            box-sizing: border-box;
            font-size: 11px;
            line-height: 1.45;
        }
    </style>
</head>

<body>
    @php
        $nomorSurat = str_replace(['{tahun}', '{nomor}'], [$tanggal->format('Y'), $dudi->id], $pengaturan->nomor_surat);

        $kopSuratBase64 = null;
        if ($kopSuratPath && is_file($kopSuratPath)) {
            $kopSuratData = file_get_contents($kopSuratPath);
            if ($kopSuratData !== false) {
                $kopMimeType = mime_content_type($kopSuratPath) ?: 'image/png';
                $kopSuratBase64 = 'data:' . $kopMimeType . ';base64,' . base64_encode($kopSuratData);
            }
        }
    @endphp

    <div class="envelope">
        <div class="kop">
            @if ($kopSuratBase64)
                <img src="{{ $kopSuratBase64 }}" alt="Kop Surat">
            @else
                <div class="kop-fallback">
                    <div><strong>PEMERINTAH PROVINSI KALIMANTAN BARAT</strong></div>
                    <div><strong>SMK NEGERI 7 PONTIANAK</strong></div>
                    <div>Jalan Tanjung Raya II Pontianak Timur, Kalimantan Barat 78232</div>
                    <div>Website: smkn7ptk.sch.id | WA: 08115784200</div>
                </div>
            @endif
        </div>

        <table class="meta">
            <tr>
                <td class="label">Nomor</td>
                <td class="separator">:</td>
                <td>{{ $nomorSurat }}</td>
            </tr>
            <tr>
                <td class="label">Hal</td>
                <td class="separator">:</td>
                <td>Permohonan PKL</td>
            </tr>
            <tr>
                <td class="label">Lampiran</td>
                <td class="separator">:</td>
                <td>-</td>
            </tr>
        </table>

        <div class="target">
            Kepada Yth.<br>
            {{ $dudi->panggilan_pimpinan }} {{ $dudi->name }}<br>
            {!! nl2br(e($dudi->address)) !!}
        </div>
    </div>
</body>

</html>
