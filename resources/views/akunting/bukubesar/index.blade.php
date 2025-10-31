@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Penjurnalan</h1>
    {{-- FORM FILTER --}}
<div class="card shadow mb-4">

    {{-- BAGIAN INI AKAN MENJADI TRIGGER (TOMBOL) UNTUK MEMBUKA/MENUTUP --}}
    <a href="#collapseFilter" class="d-block card-header py-3 text-decoration-none" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseFilter">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Filter Penjurnalan
            </h6>
            <i class="fa fa-chevron-down text-primary"></i>
        </div>
    </a>

    {{-- BAGIAN INI ADALAH KONTEN YANG BISA TERSEMBUNYI/TAMPIL --}}
    {{-- Class 'collapse' membuatnya tersembunyi secara default --}}
    <div class="collapse" id="collapseFilter">
        <div class="card-body">
            {{-- Form filter Anda yang sudah ada diletakkan di sini --}}
            <form action="{{ route('bukubesar.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">Dari Tanggal</label>
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>No. Referensi</label>
                            <input type="text" class="form-control" name="referensi" placeholder="Cari referensi..." value="{{ request('referensi') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>No. Rekening</label>
                            <input type="text" class="form-control" name="no_rekening" placeholder="Cari no. rekening..." value="{{ request('no_rekening') }}">
                        </div>
                    </div>
                     <div class="col-md-3">
                        <div class="form-group">
                            <label>Nama Perkiraan</label>
                            <input type="text" class="form-control" name="nama_perkiraan" placeholder="Cari nama perkiraan..." value="{{ request('nama_perkiraan') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('bukubesar.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Reset
                        </a>
                        <a href="{{ route('bukubesar.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                            <i class="fa fa-file-pdf"></i> Cetak PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Penjurnalan</h6>
                </div>
                <div class="card-body">
                    @if($jurnalEntries->isEmpty() && $totalDebetKeseluruhan == 0 && $totalKreditKeseluruhan == 0)
                        <p class="text-center">Tidak ada data penjurnalan.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th>Tanggal</th>
                                        <th>Referensi</th>
                                        <th>No. Rekening</th>
                                        <th>Nama Perkiraan</th>
                                        <th>Debet</th>
                                        <th>Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($jurnalEntries->isEmpty())
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data untuk ditampilkan pada halaman ini, namun ada total keseluruhan.</td>
                                        </tr>
                                    @else
                                        @php $no = $jurnalEntries->firstItem(); @endphp
                                        @foreach ($jurnalEntries as $entry)
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td>
                                                    @if($entry->header)
                                                        {{ \Carbon\Carbon::parse($entry->header->tanggal_buat)->format('d M Y') }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($entry->header)
                                                        {{ $entry->header->no_jurnal }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($entry->perkiraan)
                                                        {{ $entry->perkiraan->cls_kiraid }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($entry->perkiraan)
                                                        {{ $entry->perkiraan->cls_ina }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
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
                                            Selisih:
                                        </th>
                                        <th class="text-right font-weight-bold" colspan="2">
                                            @if ($selisihKeseluruhan == 0)
                                                <span class="badge bg-success px-2 py-1">Balance</span>
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
                        </div>
                        @if($jurnalEntries->hasPages())
                        <div class="mt-3">
                            {{ $jurnalEntries->links() }} {{-- Untuk navigasi paginasi --}}
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
var table = $('#dataTable').DataTable();

});
</script>
@endpush
