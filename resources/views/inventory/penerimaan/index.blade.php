@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
@endphp

@section('main-content')
    <div class="container-fluid">
        @if(isset($penerimaans))
            {{-- TAMPILAN INDEX/LIST --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Penerimaan Barang</h6>
                    @can('tambah', $currentMenuSlug)
                    <a href="{{ route('penerimaan.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Penerimaan
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No. Penerimaan</th>
                                    <th>Supplier</th>
                                    <th>No. PO</th>
                                    <th>Tgl. Terima</th>
                                    <th>Gudang</th>
                                    <th>Faktur</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penerimaans as $penerimaan)
                                    <tr>
                                        <td>{{ $penerimaan->no_penerimaan }}</td>
                                        <td>{{ $penerimaan->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $penerimaan->purchaseOrder->po_number ?? '-' }}</td>
                                        <td>{{ $penerimaan->tgl_terima->format('d M Y') }}</td>
                                        <td>{{ $penerimaan->gudang }}</td>
                                        <td>{{ $penerimaan->faktur }}</td>
                                        <td>{{ $penerimaan->jatuh_tempo->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $penerimaan->status === 'draft' ? 'warning' : 'success' }} text-white">
                                                {{ $penerimaan->status === 'draft' ? 'DRAFT' : 'PUBLISHED' }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('ubah', $currentMenuSlug)
                                            <a href="{{ route('penerimaan.edit', $penerimaan->penerimaan_id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            
                                            <a href="{{ route('penerimaan.show', $penerimaan->penerimaan_id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if ($penerimaan->status === 'draft')
                                                @can('hapus', $currentMenuSlug)
                                                <button class="btn btn-sm btn-danger delete-penerimaan-btn" 
                                                    data-id="{{ $penerimaan->penerimaan_id }}"
                                                    data-name="{{ $penerimaan->no_penerimaan }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif(isset($header))
            @if(!isset($showMode))
                {{-- TAMPILAN EDIT --}}
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Form Penerimaan Barang</h6>
                        <span class="badge bg-{{ $header->status === 'draft' ? 'warning' : 'success' }} text-white">
                            {{ $header->status === 'draft' ? 'DRAFT' : 'PUBLISHED' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <form id="headerForm">@csrf
                            <input type="hidden" id="penerimaanId" value="{{ $header->penerimaan_id }}">

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">No. Penerimaan</label>
                                <div class="col-sm-4">
                                    <input type="text" id="noPenerimaan" name="no_penerimaan" class="form-control"
                                        value="{{ $header->no_penerimaan }}" readonly>
                                </div>

                                <label class="col-sm-2 col-form-label">Supplier</label>
                                <div class="col-sm-4">
                                    <select name="supplier_id" id="supplier_id" class="form-control" 
                                        {{ $header->status !== 'draft' ? 'disabled' : '' }} required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}"
                                                {{ $header->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->nama_supplier }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">No. PO</label>
                                <div class="col-sm-4">
                                    <select name="po_id" id="po_id" class="form-control" 
                                        {{ $header->status !== 'draft' ? 'disabled' : '' }} required>
                                        <option value="">Pilih Purchase Order</option>
                                        @foreach($purchaseOrders as $po)
                                            <option value="{{ $po->po_id }}"
                                                {{ $header->po_id == $po->po_id ? 'selected' : '' }}
                                                data-supplier="{{ $po->supplier_id }}">
                                                {{ $po->po_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <label class="col-sm-2 col-form-label">Gudang</label>
                                <div class="col-sm-4">
                                    <select name="gudang" id="gudang" class="form-control" 
                                        {{ $header->status !== 'draft' ? 'disabled' : '' }} required>
                                        <option value="">Pilih Gudang</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location }}"
                                                {{ $header->gudang == $location ? 'selected' : '' }}>
                                                {{ $location }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Tgl. Terima</label>
                                <div class="col-sm-4">
                                    <input type="date" name="tgl_terima" id="tgl_terima" class="form-control"
                                        value="{{ old('tgl_terima', $header->tgl_terima?->format('Y-m-d')) }}" 
                                        {{ $header->status !== 'draft' ? 'readonly' : '' }} required>
                                </div>
                                
                                <label class="col-sm-2 col-form-label">Status</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ strtoupper($header->status) }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Faktur</label>
                                <div class="col-sm-4">
                                    <input type="text" name="faktur" id="faktur" class="form-control"
                                        value="{{ $header->faktur }}" 
                                        {{ $header->status !== 'draft' ? 'readonly' : '' }} required>
                                </div>

                                <label class="col-sm-2 col-form-label">Jatuh Tempo</label>
                                <div class="col-sm-4">
                                    <input type="date" name="jatuh_tempo" id="jatuh_tempo" class="form-control"
                                        value="{{ old('jatuh_tempo', $header->jatuh_tempo?->format('Y-m-d')) }}" 
                                        {{ $header->status !== 'draft' ? 'readonly' : '' }} required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Catatan</label>
                                <div class="col-sm-10">
                                    <textarea name="catatan" id="catatan" class="form-control" rows="2"
                                        {{ $header->status !== 'draft' ? 'readonly' : '' }}>{{ $header->catatan }}</textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- TOMBOL ACTION --}}
                @if($header->status === 'draft')
                <div class="mb-3 d-flex">
                    @can('tambah', $currentMenuSlug)
                    <button type="button" id="addDetailButton" class="btn btn-primary mr-2" data-bs-toggle="modal"
                        data-bs-target="#dtlModal">
                        <i class="fas fa-plus"></i> Tambah Barang
                    </button>
                    @endcan
                    
                    @can('ubah', $currentMenuSlug)
                    <button id="btnPublish" class="btn btn-success mr-2">
                        <i class="fas fa-floppy-disk"></i> Simpan Penerimaan
                    </button>
                    @endcan
                    
                    @can('hapus', $currentMenuSlug)
                    <button id="btnCancelDraft" class="btn btn-danger">
                        <i class="fas fa-times"></i> Batalkan
                    </button>
                    @endcan
                </div>
                @endif

                {{-- TABEL DETAIL --}}
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive pb-3">
                            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width='10%'>Kode</th>
                                        <th width='20%'>Nama Produk</th>
                                        <th width='5%'>Qty</th>
                                        <th width='5%'>Satuan</th>
                                        <th width='10%'>Harga Beli</th>
                                        <th width='5%'>Disc (%)</th>
                                        <th width='5%'>Pajak (%)</th>
                                        <th width='10%'>Nominal</th>
                                        <th width='20%'>Catatan</th>
                                        <th width='10%'>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($header->details as $detail)
                                        <tr>
                                            <td>{{ $detail->product->kode_produk }}</td>
                                            <td>{{ $detail->product->nama_produk }}</td>
                                            <td class="text-right">{{ number_format($detail->qty) }}</td>
                                            <td>{{ $detail->uom->UOM_Code }}</td>
                                            <td class="text-right">{{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($detail->diskon_persen, 2) }}</td>
                                            <td class="text-right">{{ number_format($detail->pajak_persen, 2) }}</td>
                                            <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                            <td>{{ $detail->catatan }}</td>
                                            <td>
                                                @if($header->status === 'draft')
                                                    @can('ubah', $currentMenuSlug)
                                                    <button class="btn btn-sm btn-warning edit-btn" 
                                                        data-id="{{ $detail->detail_id }}"
                                                        data-product_id="{{ $detail->product_id }}"
                                                        data-uom_id="{{ $detail->uom_id }}"
                                                        data-qty="{{ $detail->qty }}"
                                                        data-harga_beli="{{ $detail->harga_beli }}"
                                                        data-pajak_persen="{{ $detail->pajak_persen }}"
                                                        data-diskon_persen="{{ $detail->diskon_persen }}"
                                                        data-catatan="{{ $detail->catatan }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @endcan
                                                    
                                                    @can('hapus', $currentMenuSlug)
                                                    <button class="btn btn-sm btn-danger delete-btn" 
                                                        data-id="{{ $detail->detail_id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @endcan
                                                @else
                                                <span class="text-muted">Locked</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="font-weight-bold">
                                    <tr>
                                        <td colspan="7" class="text-right">TOTAL</td>
                                        <td class="text-right">
                                            @php
                                                $grandTotal = $header->details->sum('subtotal');
                                            @endphp
                                            {{ number_format($grandTotal, 0, ',', '.') }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- MODAL DETAIL --}}
                @if($header->status === 'draft')
                <div class="modal fade" id="dtlModal" tabindex="-1">
                    <div class="modal-dialog">
                        <form id="dtlForm" onsubmit="return false;">@csrf
                            <div class="modal-content">
                                <div class="modal-header py-2">
                                    <h5 class="modal-title">Tambah Barang</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Produk</label>
                                        <div class="col-sm-9">
                                            <select name="product_id" id="product_id" class="form-control" required>
                                                <option value="">Pilih Produk</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                        data-kode="{{ $product->kode_produk }}"
                                                        data-nama="{{ $product->nama_produk }}">
                                                        {{ $product->kode_produk }} - {{ $product->nama_produk }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Kode</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="product_code" class="form-control" readonly>
                                        </div>
                                        <label class="col-sm-3 col-form-label">Nama</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="product_name" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Satuan</label>
                                        <div class="col-sm-3">
                                            <select name="uom_id" id="uom_id" class="form-control" required>
                                                <option value="">Pilih Satuan</option>
                                                @foreach($uoms as $uom)
                                                    <option value="{{ $uom->UOM_Auto }}">{{ $uom->UOM_Code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <label class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-3">
                                            <input type="number" min="1" name="qty" id="qty" class="form-control calc-trigger" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Harga Beli</label>
                                        <div class="col-sm-3">
                                            <input type="number" min="1000" step="100" name="harga_beli" id="harga_beli" class="form-control calc-trigger" required>
                                        </div>
                                        
                                        <label class="col-sm-3 col-form-label">Disc (%)</label>
                                        <div class="col-sm-3">
                                            <input type="number" min="0" max="100" step="0.1" name="diskon_persen" id="diskon_persen" class="form-control calc-trigger" value="0">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Pajak (%)</label>
                                        <div class="col-sm-3">
                                            <input type="number" min="0" max="100" step="0.1" name="pajak_persen" id="pajak_persen" class="form-control calc-trigger" value="0">
                                        </div>
                                        
                                        <label class="col-sm-3 col-form-label">Nominal</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="nominal" class="form-control" readonly>
                                            <input type="hidden" id="subtotal" name="subtotal">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Catatan</label>
                                        <div class="col-sm-9">
                                            <textarea name="catatan" id="dtl_catatan" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button id="dtlSave" type="button" class="btn btn-primary">
                                        <i class="fas fa-check"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            @else
                {{-- TAMPILAN SHOW --}}
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Penerimaan: {{ $header->no_penerimaan }}</h6>
                        <span class="badge bg-{{ $header->status === 'draft' ? 'warning' : 'success' }} text-white">
                            {{ $header->status === 'draft' ? 'DRAFT' : 'PUBLISHED' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Supplier</th>
                                        <td>{{ $header->supplier->nama_supplier }}</td>
                                    </tr>
                                    <tr>
                                        <th>No. PO</th>
                                        <td>{{ $header->purchaseOrder->po_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gudang</th>
                                        <td>{{ $header->gudang }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Tanggal Terima</th>
                                        <td>{{ $header->tgl_terima->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Faktur</th>
                                        <td>{{ $header->faktur }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jatuh Tempo</th>
                                        <td>{{ $header->jatuh_tempo->format('d M Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Nama Produk</th>
                                                <th>Qty</th>
                                                <th>Satuan</th>
                                                <th>Harga Beli</th>
                                                <th>Disc (%)</th>
                                                <th>Pajak (%)</th>
                                                <th>Nominal</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($header->details as $detail)
                                                <tr>
                                                    <td>{{ $detail->product->kode_produk }}</td>
                                                    <td>{{ $detail->product->nama_produk }}</td>
                                                    <td class="text-right">{{ number_format($detail->qty) }}</td>
                                                    <td>{{ $detail->uom->UOM_Code }}</td>
                                                    <td class="text-right">{{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                                                    <td class="text-right">{{ number_format($detail->diskon_persen, 2) }}</td>
                                                    <td class="text-right">{{ number_format($detail->pajak_persen, 2) }}</td>
                                                    <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                    <td>{{ $detail->catatan }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="font-weight-bold">
                                            <tr>
                                                <td colspan="7" class="text-right">TOTAL</td>
                                                <td class="text-right">
                                                    @php
                                                        $grandTotal = $header->details->sum('subtotal');
                                                    @endphp
                                                    {{ number_format($grandTotal, 0, ',', '.') }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('penerimaan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            @if ($header->status === 'draft')
                                <div>
                                    @can('ubah', $currentMenuSlug)
                                    <a href="{{ route('penerimaan.edit', $header->penerimaan_id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .table tbody tr td {
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .badge.bg-warning { background-color: #f6c23e !important; }
        .badge.bg-success { background-color: #1cc88a !important; }
        .modal-header .close {
            font-size: 1.5rem;
            line-height: 1;
            opacity: 0.75;
            background: none;
            border: none;
            padding: 0;
            margin-top: -4px;
        }
        .modal-header .close:hover {
            opacity: 1;
        }
        .modal-header.py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .close {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: #000;
            text-shadow: 0 1px 0 #fff;
            opacity: .5;
            background: transparent;
            border: 0;
        }
        .close:hover {
            color: #000;
            text-decoration: none;
            opacity: .75;
        }
    </style>
@endpush

@push('scripts')
    @if(isset($header) && !isset($showMode))
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function() {
            const headerId = $('#penerimaanId').val();
            const modal = $('#dtlModal');
            const form = $('#dtlForm');
            let editMode = false,
                currentDetailId = null;

            // Auto-calculate nominal in modal
            $('.calc-trigger').on('input', calculateNominal);

            function calculateNominal() {
                const qty = parseFloat($('#qty').val()) || 0;
                const hargaBeli = parseFloat($('#harga_beli').val()) || 0;
                const diskonPersen = parseFloat($('#diskon_persen').val()) || 0;
                const pajakPersen = parseFloat($('#pajak_persen').val()) || 0;

                // Calculate subtotal (price * qty)
                const subtotal = qty * hargaBeli;

                // Apply discount and tax percentages
                const diskonAmount = subtotal * (diskonPersen / 100);
                const afterDiskon = subtotal - diskonAmount;
                const pajakAmount = afterDiskon * (pajakPersen / 100);

                // Calculate final price
                const total = afterDiskon + pajakAmount;
                $('#nominal').val(total.toLocaleString('id-ID'));
                $('#subtotal').val(total);
            }

            // Update product code and name when product is selected
            $('#product_id').change(function() {
                const selectedOption = $(this).find('option:selected');
                $('#product_code').val(selectedOption.data('kode'));
                $('#product_name').val(selectedOption.data('nama'));
            });

            // Sync PO and Supplier selection
            $('#po_id').change(function() {
                const selectedOption = $(this).find('option:selected');
                const supplierId = selectedOption.data('supplier');
                if (supplierId) {
                    $('#supplier_id').val(supplierId).trigger('change');
                }
            });

            // Header update
            $('#headerForm').on('change', 'input, select, textarea', function() {
                const data = $('#headerForm').serialize();
                $.ajax({
                    url: `/inventory/penerimaan/${headerId}/update-header`,
                    type: 'PUT',
                    data: data,
                    success: function() {
                        showToast('success', 'Header berhasil diperbarui');
                    },
                    error: function(xhr) {
                        showToast('error', 'Gagal memperbarui header: ' + xhr.responseText);
                    }
                });
            });

            // Open modal for new detail
            $('#addDetailButton').click(function() {
                editMode = false;
                currentDetailId = null;
                form[0].reset();
                $('#product_code').val('');
                $('#product_name').val('');
                $('#nominal').val('');
                $('#subtotal').val('');
                modal.modal('show');
            });

            // Open modal for edit detail
            $('#dataTable').on('click', '.edit-btn', function() {
                editMode = true;
                currentDetailId = $(this).data('id');
                
                // Fill form
                const productId = $(this).data('product_id');
                $('#product_id').val(productId);
                const selectedProduct = $('#product_id option[value="' + productId + '"]');
                $('#product_code').val(selectedProduct.data('kode'));
                $('#product_name').val(selectedProduct.data('nama'));
                $('#uom_id').val($(this).data('uom_id'));
                $('#qty').val($(this).data('qty'));
                $('#harga_beli').val($(this).data('harga_beli'));
                $('#pajak_persen').val($(this).data('pajak_persen'));
                $('#diskon_persen').val($(this).data('diskon_persen'));
                $('#dtl_catatan').val($(this).data('catatan'));
                
                // Calculate nominal
                calculateNominal();
                
                modal.modal('show');
            });

            // Save detail
            $('#dtlSave').click(function() {
                const data = form.serialize();
                const url = editMode 
                    ? `/inventory/penerimaan/${headerId}/details/${currentDetailId}`
                    : `/inventory/penerimaan/${headerId}/details`;

                const method = editMode ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    success: function() {
                        modal.modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        showToast('error', 'Gagal menyimpan detail: ' + xhr.responseText);
                    }
                });
            });

            // Delete detail
            $('#dataTable').on('click', '.delete-btn', function() {
                const btn = $(this);
                const detailId = btn.data('id');
                const row = btn.closest('tr');
                const productName = row.find('td:eq(1)').text();
                
                Swal.fire({
                    title: 'Hapus Item Penerimaan?',
                    html: `Yakin ingin menghapus produk <strong>${productName}</strong> dari penerimaan?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/inventory/penerimaan/${headerId}/details/${detailId}`,
                            type: 'DELETE',
                            success: function() {
                                row.fadeOut(400, function() {
                                    row.remove();
                                    // Hitung ulang total
                                    recalculateGrandTotal();
                                });
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: 'Item berhasil dihapus dari penerimaan',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                const errorMsg = JSON.parse(xhr.responseText).error;
                                showToast('error', 'Gagal menghapus item: ' + errorMsg);
                            }
                        });
                    }
                });
            });

            // Publish Penerimaan
            $('#btnPublish').click(function() {
                Swal.fire({
                    title: 'Simpan Penerimaan?',
                    text: "Pastikan data sudah benar sebelum disimpan",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/inventory/penerimaan/${headerId}/publish`,
                            type: 'POST',
                            success: function() {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Penerimaan berhasil disimpan',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href = '/inventory/penerimaan';
                                });
                            },
                            error: function(xhr) {
                                const errorMsg = JSON.parse(xhr.responseText).error;
                                showToast('error', 'Gagal menyimpan penerimaan: ' + errorMsg);
                            }
                        });
                    }
                });
            });

            // Cancel draft
            $('#btnCancelDraft').click(function() {
                Swal.fire({
                    title: 'Batalkan draft?',
                    text: "Semua data akan dihapus secara permanen",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, batalkan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/inventory/penerimaan/${headerId}/cancel`,
                            type: 'DELETE',
                            success: function() {
                                window.location.href = '/inventory/penerimaan';
                            },
                            error: function(xhr) {
                                showToast('error', 'Gagal membatalkan draft: ' + xhr.responseText);
                            }
                        });
                    }
                });
            });

            // Helper function to show toast notifications
            function showToast(type, message) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                
                Toast.fire({
                    icon: type,
                    title: message
                });
            }

            // Recalculate grand total after item deletion
            function recalculateGrandTotal() {
                let grandTotal = 0;
                $('#dataTable tbody tr').each(function() {
                    const totalCell = $(this).find('td:eq(7)');
                    const totalValue = parseFloat(totalCell.text().replace(/\./g, ''));
                    if (!isNaN(totalValue)) {
                        grandTotal += totalValue;
                    }
                });
                $('#dataTable tfoot td:eq(1)').text(grandTotal.toLocaleString('id-ID'));
            }
        });
    </script>
    @endif

    @if(isset($penerimaans))
    <script>
        $(document).ready(function() {
            // Hapus Penerimaan dari halaman index
            $(document).on('click', '.delete-penerimaan-btn', function() {
                const btn = $(this);
                const penerimaanId = btn.data('id');
                const penerimaanNumber = btn.data('name');
                const row = btn.closest('tr');
                
                Swal.fire({
                    title: 'Hapus Penerimaan?',
                    html: `Yakin ingin menghapus penerimaan <strong>${penerimaanNumber}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/inventory/penerimaan/${penerimaanId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                row.fadeOut(400, function() {
                                    row.remove();
                                    // Refresh DataTable jika digunakan
                                    if ($.fn.DataTable.isDataTable('#dataTable')) {
                                        $('#dataTable').DataTable().draw(false);
                                    }
                                });
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    html: response.message || 'Penerimaan berhasil dihapus',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                let message = 'Terjadi kesalahan pada server';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    html: message,
                                    showConfirmButton: true
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endif
@endpush