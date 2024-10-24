<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan {{ env('COMPANY_NAME') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            text-transform: uppercase;
            margin: 3px;
            padding: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th:first-child,
        td:first-child {
            text-align: left;
        }

        .header-table {
            border: none;
        }

        .header-table td {
            border: none;
            padding: 5px;
        }

        h1,
        p {
            margin: 0;
            padding: 0;
        }

        img {
            max-width: 100px;
        }

        /* Style untuk page break */
        .page-break {
            page-break-before: always;
        }

        /* Buat kolom NO, TANGGAL DAFTAR, NAMA, NEGARA TUJUAN, JOB rata kiri */
        .left-align td:nth-child(1),
        .left-align td:nth-child(2),
        .left-align td:nth-child(3),
        .left-align td:nth-child(4),
        .left-align td:nth-child(5) {
            text-align: left;
        }

        .status-table td {
            text-align: left;
        }
    </style>
</head>

<body>
    <table class="header-table" width="100%">
        <tr>
            <td width="33%" style="text-align: left;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}" alt="Logo" width="100">
            </td>
            <td width="33%" style="text-align: right;">
                <div>
                    <p><strong>Tanggal Unduh:</strong> {{ date('d-m-Y H:i:s') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <h1 align="center">{{ env('COMPANY_NAME') }}</h1>
    <p align="center"><strong>Periode:</strong>
        {{ $start ? \Carbon\Carbon::parse($start)->format('d-m-Y') : 'Semua tanggal' }}
        s/d
        {{ $end ? \Carbon\Carbon::parse($end)->format('d-m-Y') : 'Sekarang' }}
    </p>

    <!-- Tabel Status -->
    <h2 align="center">Status</h2>
    <table>
        <thead>
            <tr>
                <th>Kantor</th>
                @foreach($statuses as $status)
                <th>{{ $status->nama }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($jumlahData as $kantorNama => $dataStatus)
            <tr>
                <td>{{ $kantorNama }}</td>
                @php
                $totalStatus = 0;
                @endphp
                @foreach($statuses as $status)
                <td>{{ $dataStatus[$status->nama] ?? 0 }}</td>
                @php
                $totalStatus += $dataStatus[$status->nama] ?? 0;
                @endphp
                @endforeach
            </tr>
            @endforeach
            <tr>
                <td><strong>Total</strong></td>
                @foreach($statuses as $status)
                <td><strong>{{ $totalPerStatus[$status->id] ?? 0 }}</strong></td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <!-- Tabel Pra Medical, Siap Kerja, ID BP2MI, Dapat Job, Penerbangan -->
    <h2 align="center">Pra Medical, Siap Kerja, ID BP2MI, Dapat Job, Penerbangan</h2>
    <table>
        <thead>
            <tr>
                <th>Kantor</th>
                <th>Pra Medical</th>
                <th>Siap Kerja</th>
                <th>ID BP2MI</th>
                <th>Dapat Job</th>
                <th>Penerbangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kantors as $kantor)
            <tr>
                <td>{{ $kantor->nama }}</td>
                <td>{{ $jumlahPraMedical[$kantor->nama] ?? 0 }}</td>
                <td>{{ $jumlahSiapKerja[$kantor->nama] ?? 0 }}</td>
                <td>{{ $jumlahBp2mi[$kantor->nama] ?? 0 }}</td>
                <td>{{ $jumlahDapatJob[$kantor->nama] ?? 0 }}</td>
                <td>{{ $jumlahPenerbangan[$kantor->nama] ?? 0 }}</td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Total</strong></td>
                <td><strong>{{ array_sum($jumlahPraMedical) }}</strong></td>
                <td><strong>{{ array_sum($jumlahSiapKerja) }}</strong></td>
                <td><strong>{{ array_sum($jumlahBp2mi) }}</strong></td>
                <td><strong>{{ array_sum($jumlahDapatJob) }}</strong></td>
                <td><strong>{{ array_sum($jumlahPenerbangan) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- resources/views/kelompokan_berdasarkan_status.blade.php -->
    @foreach($kantors as $kantor)
    <div class="page-break"></div> <!-- Page break per kantor -->
    <table class="header-table" width="100%">
        <tr>
            <td width="33%" style="text-align: left;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}" alt="Logo" width="40">
            </td>
            <td width="33%" style="text-align: right;">
                <div>
                    <p><strong>Tanggal Unduh:</strong> {{ date('d-m-Y H:i:s') }}</p>
                </div>
            </td>
        </tr>
    </table>
    <!-- <h2 align="center">Kelompokan Berdasarkan Status</h2> -->
    <h2 align="center">KANTOR - {{ $kantor->nama }}</h2> <!-- Nama kantor -->
    <p align="center"><strong>Periode:</strong>
        {{ $start ? \Carbon\Carbon::parse($start)->format('d-m-Y') : 'Semua tanggal' }}
        s/d
        {{ $end ? \Carbon\Carbon::parse($end)->format('d-m-Y') : 'Sekarang' }}
    </p>

    <!-- Iterasi per status di dalam setiap kantor -->
    @foreach($statuses as $status)
    <h3>{{ $status->nama }}</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Daftar</th>
                <th>Nama</th>
                <th>Tujuan</th>
                <th>LPKS/BLK</th>
                <th>Agency/Job</th>
                @if($status->id == 3 || strtolower($status->nama) == 'terbang')
                <th>Penerbangan</th>
                @endif
            </tr>
        </thead>
        <tbody class="left-align">
            @php $no = 1; @endphp
            @forelse($dataByStatus[$status->nama]->where('pendaftaran.kantor.nama', $kantor->nama)->sortBy(fn($item) => $item->pendaftaran->created_at ?? now()) as $prosesCpmi)
            <tr>
                <td style="text-align: left;">{{ $no++ }}</td>

                <!-- Format Tanggal Pendaftaran -->
                <td style="text-align: left;">{{ isset($prosesCpmi->pendaftaran->created_at) ? \Carbon\Carbon::parse($prosesCpmi->pendaftaran->created_at)->format('d-m-Y') : '-' }}</td>

                <!-- Nama Pendaftar -->
                <td style="text-align: left;">{{ $prosesCpmi->pendaftaran->nama ?? '-' }}</td>

                <!-- Negara Tujuan -->
                <td style="text-align: left;">{{ $prosesCpmi->tujuan->nama ?? '-' }}</td>


                <!-- Negara Pelatihan -->
                <td style="text-align: left;">{{ $prosesCpmi->pelatihan->nama ?? '-' }}</td>

                <!-- Iterasi jika relasi marketing hasMany -->
                <td style="text-align: left;">
                    @foreach($prosesCpmi->pendaftaran->marketing as $marketing)
                    {{ $marketing->agency->nama ?? '-' }} <br>
                    @endforeach
                </td>

                <!-- Tampilkan kolom Penerbangan hanya jika status 'Terbang' -->
                @if($status->id == 3 || strtolower($status->nama) == 'terbang')
                <td style="text-align: left;">{{ isset($prosesCpmi->tanggal_penerbangan) ? \Carbon\Carbon::parse($prosesCpmi->tanggal_penerbangan)->format('d-m-Y') : '-' }}</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $status->id == 3 || strtolower($status->nama) == 'terbang' ? 6 : 5 }}" style="text-align: left;">Tidak ada data {{ $kantor->nama }}.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endforeach
    @endforeach



</body>

</html>