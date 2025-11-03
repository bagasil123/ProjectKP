@extends('layouts.admin')
@section('main-content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h3 mb-0 text-gray-800">Barang Dalam Perjalanan</h1>
        <a href="{{ route('transfergudang.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar Transfer
        </a>
    </div>
    
    <p class="mb-4">Daftar ini berisi semua barang yang telah dikirim dari gudang asal (stok sudah berkurang) tetapi belum diterima di gudang tujuan.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Transfer Dalam Perjalanan</h6>
        </div>
        <div class="card-body">
            @if($inTransitTransfers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Tanggal Kirim</th>
                                <th>Gudang Asal</th>
                                <th>Gudang Tujuan</th>
                                <th>Item</th>
                                <th class="text-end">Total Qty</th>
                                <th class="text-end">Total Netto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inTransitTransfers as $transfer)
                                <tr>
                                    <td><strong>{{ $transfer->trx_number ?? 'N/A' }}</strong></td>
                                    <td>
                                        @if($transfer->Trx_Date)
                                            {{ \Carbon\Carbon::parse($transfer->Trx_Date)->isoFormat('DD MMMM YYYY') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $transfer->gudangPengirim->WARE_Name ?? 'N/A' }}</td>
                                    <td>{{ $transfer->gudangPenerima->WARE_Name ?? 'N/A' }}</td>
                                    <td>
                                        @if($transfer->details && $transfer->details->count() > 0)
                                            <ul class="mb-0 pl-3">
                                                @foreach($transfer->details as $detail)
                                                    <li>
                                                        {{ $detail->produk->nama_produk ?? $detail->trx_prodname ?? $detail->Trx_ProdCode }} 
                                                        ({{ $detail->Trx_QtyTrx }} {{ $detail->trx_uom ?? 'PCS' }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">Tidak ada detail</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ $transfer->details ? $transfer->details->sum('Trx_QtyTrx') : 0 }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($transfer->netto_from_permintaan ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Tidak ada barang dalam perjalanan saat ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($inTransitTransfers->count() > 0)
        $('#dataTable').DataTable({
            "order": [[ 1, "desc" ]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });
    @endif
});
</script>
@endpush