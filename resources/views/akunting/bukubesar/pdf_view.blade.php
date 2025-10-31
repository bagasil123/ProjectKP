<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Besar</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px; /* Ukuran font bisa disesuaikan untuk PDF */
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-section h1 {
            margin: 0;
            font-size: 18px;
        }
        .header-section p {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px; /* Padding bisa disesuaikan */
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-weight-bold {
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: .25em .6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }
        .bg-success {
            color: #fff;
            background-color: #198754; /* Warna hijau */
        }
        .text-danger {
            color: #dc3545; /* Warna merah */
        }
        .text-primary {
             color: #0d6efd; /* Warna biru */
        }
        tfoot th {
            background-color: #e9ecef; /* Warna footer bisa berbeda */
        }
        .footer-info {
            margin-top: 30px;
            font-size: 9px;
            text-align: right;
        }
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <h1>Buku Besar</h1>
            <p>CV.PRIMA BELLA PANEN REJEKI </p>
            {{-- <p>Alamat Perusahaan</p> --}}
            <p>{{ $tanggalCetak }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Tanggal</th>
                    <th>Referensi</th>
                    <th>No. Rekening</th>
                    <th>Nama Perkiraan</th>
                    <th class="text-right">Debet</th>
                    <th class="text-right">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @if($jurnalEntries->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data penjurnalan.</td>
                    </tr>
                @else
                    @php $no = 1; @endphp
                    @foreach ($jurnalEntries as $entry)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>
                                @if($entry->header)
                                    {{ \Carbon\Carbon::parse($entry->header->tanggal_buat)->format('d M Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($entry->header)
                                    {{ $entry->header->no_jurnal }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($entry->perkiraan)
                                    {{ $entry->perkiraan->cls_kiraid }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($entry->perkiraan)
                                    {{ $entry->perkiraan->cls_ina }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($entry->debet, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($entry->kredit, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">Total Keseluruhan:</th>
                    <th class="text-right font-weight-bold">{{ number_format($totalDebetKeseluruhan, 2, ',', '.') }}</th>
                    <th class="text-right font-weight-bold">{{ number_format($totalKreditKeseluruhan, 2, ',', '.') }}</th>
                </tr>
                <tr>
                    <th colspan="5" class="text-right">
                        Selisih (Debet - Kredit):
                    </th>
                    <th class="text-right font-weight-bold" colspan="2">
                        @if ($selisihKeseluruhan == 0)
                            <span class="badge bg-success">Balance</span>
                            <span>( {{ number_format($selisihKeseluruhan, 2, ',', '.') }} )</span>
                        @elseif ($selisihKeseluruhan > 0)
                            <span class="text-danger">{{ number_format($selisihKeseluruhan, 2, ',', '.') }}</span>
                        @else
                            <span class="text-primary">{{ number_format($selisihKeseluruhan, 2, ',', '.') }}</span>
                        @endif
                    </th>
                </tr>
            </tfoot>
        </table>
        <div class="footer-info">
            Dicetak oleh Sistem pada {{ $tanggalCetak }}
        </div>
    </div>
</body>
</html>
