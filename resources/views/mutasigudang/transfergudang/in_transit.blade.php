@extends('layouts.admin')
@section('main-content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h3 mb-0 text-gray-800">{{ ('Barang Dalam Perjalanan (Stok Menggantung)') }}</h1>
        <a href="{{ route('transfergudang.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Daftar Transfer</a>
    </div>
    
    <p class="mb-4">Daftar ini berisi semua barang yang telah dikirim dari gudang asal (stok sudah berkurang) tetapi belum diterima di gudang tujuan.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Transfer Dalam Perjalanan</h6>
        </div>
        <div class="card-body">
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
                        @forelse ($inTransitTransfers as $transfer)
                            <tr>
                                <td><strong>{{ $transfer->trx_number }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($transfer->Trx_Date)->isoFormat('DD MMMM YYYY') }}</td>
                                <td>{{ $transfer->gudangPengirim->WARE_Name ?? '-' }}</td>
                                <td>{{ $transfer->gudangPenerima->WARE_Name ?? '-' }}</td>
                                <td>
                                    <ul class="mb-0 pl-3">
                                        @foreach($transfer->details as $detail)
                                            <li>{{ $detail->produk->nama_produk ?? $detail->Trx_ProdCode }} ({{ $detail->Trx_QtyTrx }} {{ $detail->trx_uom }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="text-end fw-bold">{{ $transfer->details->sum('Trx_QtyTrx') }}</td>
                                <td class="text-end fw-bold">{{ number_format($transfer->netto_from_permintaan, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada barang dalam perjalanan saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "order": [[ 1, "desc" ]] // Urutkan berdasarkan tanggal (kolom ke-1)
    });
});
</script>
@endpush