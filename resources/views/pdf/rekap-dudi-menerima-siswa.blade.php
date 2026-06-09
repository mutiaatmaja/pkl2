<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap DUDI Menerima Siswa Magang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #111827;
        }

        h1 {
            margin: 0;
            font-size: 18px;
        }

        p {
            margin: 4px 0 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .table th,
        .table td {
            border: 1px solid #111827;
            padding: 6px 8px;
            vertical-align: top;
            text-align: left;
        }

        .table th {
            background: #f3f4f6;
            font-weight: 700;
        }

        .fit {
            width: 1%;
            white-space: nowrap;
        }

        .stacked-list {
            margin: 0;
            padding-left: 16px;
        }

        .stacked-list li+li {
            margin-top: 4px;
        }

        .badge-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .badge-cetak {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #93c5fd;
        }

        .badge-diterima {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }
    </style>
</head>

<body>
    <div>
        <h1>Rekap Tempat DUDI yang Sudah Menerima Siswa Magang</h1>
        <p>Tanggal cetak: {{ $tanggalCetak->translatedFormat('d F Y H:i') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th class="fit">No</th>
                <th>DUDI</th>
                <th>Status</th>
                <th>Siswa-Siswa</th>
                <th>No HP Siswa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dudis as $index => $dudi)
                <tr>
                    <td class="fit">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $dudi->name }}</strong><br>
                        <span>{{ $dudi->address }}</span>
                    </td>
                    <td>
                        <div class="badge-wrap">
                            @if ($dudi->sudah_cetak_surat)
                                <span class="badge badge-cetak">Sudah Cetak</span>
                            @endif

                            @if ($dudi->diterima)
                                <span class="badge badge-diterima">Sudah Diterima</span>
                            @endif

                            @if (!$dudi->sudah_cetak_surat && !$dudi->diterima)
                                <span class="badge badge-pending">Belum Diproses</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <ol class="stacked-list">
                            @foreach ($dudi->siswas as $siswa)
                                <li>{{ $siswa->user?->name ?? '-' }}</li>
                            @endforeach
                        </ol>
                    </td>
                    <td>
                        <ol class="stacked-list">
                            @foreach ($dudi->siswas as $siswa)
                                <li>{{ $siswa->no_hp ?? '-' }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada DUDI yang menerima siswa magang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
