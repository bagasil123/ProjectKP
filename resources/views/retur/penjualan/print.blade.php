<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if (isset($row))
            Retur Penjualan {{ $row->trx_number }}
        @else
            Daftar Retur Penjualan
        @endif
    </title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
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

        .invoice-info-table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }

        .invoice-info-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
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
            background-color: #198754;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-primary {
            color: #0d6efd;
        }

        tfoot th {
            background-color: #e9ecef;
        }

        .footer-info {
            margin-top: 30px;
            font-size: 9px;
            text-align: right;
        }

        .item-note {
            font-style: italic;
            color: #666;
            font-size: 9px;
        }

        .item-note td {
            border-top: none;
            padding-top: 2px;
            padding-bottom: 6px;
        }

        .item-note td:first-child {
            border-left: 1px solid #ddd;
        }

        .item-note td:last-child {
            border-right: 1px solid #ddd;
        }

        .summary-section {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 15px;
        }

        .summary-table {
            width: 300px;
            float: right;
            border: none;
            margin: 0;
        }

        .summary-table td {
            padding: 6px 0;
            border: none;
            border-bottom: 1px dotted #ccc;
        }

        .summary-table .total-row td {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            font-weight: bold;
            font-size: 12px;
            padding: 10px 0;
        }

        @page {
            margin: 20mm;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        @if (isset($row))
            {{-- Single Sales Return - Header sama seperti Print All --}}
            <div class="header-section">
                <h1>Retur Penjualan {{ $row->trx_number }}</h1>
                <p>CV.PRIMA BELLA PANEN REJEKI</p>
                <p>{{ $tanggalCetak }}</p>
            </div>

            <table style="margin-bottom: 20px; border: none;">
                <tr style="border: none;">
                    <td style="width: 50%; border: none; vertical-align: top; padding: 0;">
                        <table style="border: none; width: 100%; margin: 0; border-spacing: 0;">
                            <tr style="border: none;">
                                <td style="border: none; width: 90px; padding: 2px 0; margin: 0;"><strong>No.
                                        Retur</strong></td>
                                <td style="border: none; width: 10px; padding: 2px 0; margin: 0;"><strong>:</strong>
                                </td>
                                <td style="border: none; padding: 2px 0; margin: 0;">{{ $row->trx_number }}</td>
                            </tr>
                            <tr style="border: none;">
                                <td style="border: none; width: 90px; padding: 2px 0; margin: 0;"><strong>Tanggal
                                        Retur</strong></td>
                                <td style="border: none; width: 10px; padding: 2px 0; margin: 0;"><strong>:</strong>
                                </td>
                                <td style="border: none; padding: 2px 0; margin: 0;">
                                    {{ $row->Trx_Date->format('d F Y') }}</td>
                            </tr>
                            <tr style="border: none;">
                                <td style="border: none; width: 90px; padding: 2px 0; margin: 0;">
                                    <strong>Status</strong>
                                </td>
                                <td style="border: none; width: 10px; padding: 2px 0; margin: 0;"><strong>:</strong>
                                </td>
                                <td style="border: none; padding: 2px 0; margin: 0;">
                                    {{ $row->trx_posting === 'T' ? 'Disetujui' : 'Menunggu' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; border: none; vertical-align: top; padding: 0; padding-left: 120px;">
                        <table style="border: none; width: 100%; margin: 0; border-spacing: 0; ">
                            <tr style="border: none;">
                                <td style="border: none; width: 100px; padding: 2px 0; margin: 0;">
                                    <strong>Kode Pelanggan</strong>
                                </td>
                                <td style="border: none; width: 10px; padding: 2px 0; margin: 0;">
                                    <strong>:</strong>
                                </td>
                                <td style="border: none; padding: 2px 0; margin: 0; ">
                                    {{ $row->customer ? $row->customer->kode : $row->Trx_SupCode }}</td>
                            </tr>
                            <tr style="border: none;">
                                <td style="border: none; width: 100px; padding: 2px 0; margin: 0; ">
                                    <strong>Nama Pelanggan</strong>
                                </td>
                                <td style="border: none; width: 10px; padding: 2px 0; margin: 0; ">
                                    <strong>:</strong>
                                </td>
                                <td style="border: none; padding: 2px 0; margin: 0;">
                                    {{ $row->customer->anggota }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <hr style="border: 1px solid #333; margin: 20px 0;">

            @if ($details->isEmpty())
                <table>
                    <tbody>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada detail untuk data ini.</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;">No</th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th class="text-right">Qty</th>
                            <th>Satuan</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-right">Disc (%)</th>
                            <th class="text-right">Pajak (%)</th>
                            <th class="text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($details as $i => $d)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $d->Trx_ProdCode }}</td>
                                <td>{{ $d->trx_prodname }}</td>
                                <td class="text-right">{{ number_format($d->Trx_QtyTrx, 2, ',', '.') }}</td>
                                <td>{{ $d->uom ? $d->uom->UOM_Code : $d->trx_uom }}</td>
                                <td class="text-right">{{ number_format($d->Trx_GrossPrice, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($d->Trx_Discount, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($d->Trx_Taxes, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($d->Trx_NettPrice, 2, ',', '.') }}</td>
                            </tr>
                            @if (!empty($d->Trx_Note))
                                <tr class="item-note">
                                    <td colspan="9" style="border-top: none; padding-top: 2px; padding-bottom: 6px;">
                                        <em>Catatan: {{ $d->Trx_Note }}</em>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="summary-section">
                <table class="summary-table">
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td style="text-align: right;">{{ number_format($row->Trx_GrossPrice, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Diskon:</strong></td>
                        <td style="text-align: right;">{{ number_format($row->Trx_TotDiscount, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pajak:</strong></td>
                        <td style="text-align: right;">{{ number_format($row->Trx_Taxes, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Total:</strong></td>
                        <td style="text-align: right;">
                            <strong>{{ number_format($row->Trx_NettPrice, 2, ',', '.') }}</strong>
                        </td>
                    </tr>
                </table>
                <div style="clear: both;"></div>
            </div>
        @else
            <div class="header-section">
                <h1>Daftar Retur Penjualan</h1>
                <p>CV.PRIMA BELLA PANEN REJEKI</p>
                <p>{{ $tanggalCetak }}</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th>Pelanggan</th>
                        <th>NO#</th>
                        <th>Tgl. Kembali</th>
                        <th class="text-right">Bruto</th>
                        <th class="text-right">Disc</th>
                        <th class="text-right">Pajak</th>
                        <th class="text-right">Netto</th>
                        <th>Pengguna</th>
                        <th class="text-center">Disetujui</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($rows->isEmpty())
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data retur penjualan.</td>
                        </tr>
                    @else
                        @php $no = 1; @endphp
                        @foreach ($rows as $r)
                            @if ($r->trx_posting !== 'D')
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $r->customer ? $r->customer->kode : $r->Trx_SupCode }}</td>
                                    <td>{{ $r->trx_number }}</td>
                                    <td>{{ $r->Trx_Date ? $r->Trx_Date->format('d M Y') : 'N/A' }}</td>
                                    <td class="text-right">{{ number_format($r->Trx_GrossPrice, 2, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($r->Trx_TotDiscount, 2, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($r->Trx_Taxes, 2, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($r->Trx_NettPrice, 2, ',', '.') }}</td>
                                    <td>{{ $r->user->Mem_UserName ?? $r->Trx_UserID }}</td>
                                    <td class="text-center">{{ $r->trx_posting === 'T' ? '✓' : '✗' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Total:</th>
                        <th class="text-right font-weight-bold">{{ number_format($totalGrossPrice, 2, ',', '.') }}</th>
                        <th class="text-right font-weight-bold">{{ number_format($totalDiscount, 2, ',', '.') }}</th>
                        <th class="text-right font-weight-bold">{{ number_format($totalTaxes, 2, ',', '.') }}</th>
                        <th class="text-right font-weight-bold">{{ number_format($totalNettPrice, 2, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="footer-info">
            Dicetak oleh {{ $currentUser->Mem_UserName ?? $currentUser->Mem_Auto }} pada {{ $tanggalCetak }}
        </div>
    </div>
</body>

</html>
