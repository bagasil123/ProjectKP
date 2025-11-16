@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Stok Gudang</h1>
        <a href="{{ route('warehouse.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar Gudang
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.stock_report') }}">
                <div class="form-row">
                    <div class="col-md-5">
                        <label for="WARE_Auto">Gudang</label>
                        <select name="WARE_Auto" id="WARE_Auto" class="form-control">
                            
                            @if($isSuperAdmin || count($userWarehouses) > 1)
                                <option value="all" {{ $selectedWarehouse == 'all' ? 'selected' : '' }}>
                                    Semua Gudang
                                </option>
                            @endif
                            
                            @foreach($userWarehouses as $warehouse)
                                <option 
                                    value="{{ $warehouse->WARE_Auto }}"
                                    {{ $selectedWarehouse == $warehouse->WARE_Auto ? 'selected' : '' }}>
                                    {{ $warehouse->WARE_Name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Stok Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Gudang</th>
                            <th>Supplier</th>
                            <th>Qty (Stok)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $index => $stock)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $stock->kode_produk }}</td>
                            <td>{{ $stock->nama_produk }}</td>
                            <td>{{ $stock->warehouse->WARE_Name ?? '-' }}</td>
                            <td>{{ $stock->supplier->nama_supplier ?? '-' }}</td>
                            <td class="text-center">{{ $stock->qty }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                Data stok tidak ditemukan (atau tidak ada produk di gudang yang Anda akses).
                            </td>
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
    $('#dataTable').DataTable();
});
</script>
@endpush