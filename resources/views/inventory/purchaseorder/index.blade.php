@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
@endphp

@section('main-content')
    <div class="container-fluid">
        @if(isset($purchaseOrders))
            {{-- TAMPILAN INDEX/LIST --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Purchase Order</h6>
                    @can('tambah', $currentMenuSlug)
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Buat PO Baru
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No. PO</th>
                                    <th>Supplier</th>
                                    <th>Tipe</th>
                                    <th>Tgl Kirim</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseOrders as $po)
                                    <tr>
                                        <td>{{ $po->po_number }}</td>
                                        <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ ucfirst($po->purchase_type) }}</td>
                                        <td>{{ $po->delivery_date->format('d M Y') }}</td>
                                        <td>{{ $po->location_id }}</td>
                                        <td>
                                            <span class="badge bg-{{ $po->status === 'draft' ? 'warning' : 'success' }} text-white">
                                                {{ $po->status === 'draft' ? 'DRAFT' : 'PUBLISHED' }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('ubah', $currentMenuSlug)
                                            <a href="{{ route('purchase-orders.edit', $po->po_id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            
                                            <a href="{{ route('purchase-orders.show', $po->po_id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if ($po->status === 'draft')
                                                @can('hapus', $currentMenuSlug)
                                                <button class="btn btn-sm btn-danger delete-po-btn" 
                                                    data-id="{{ $po->po_id }}"
                                                    data-name="{{ $po->po_number }}">
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
                        <h6 class="m-0 font-weight-bold text-primary">Form Pesanan ke Supplier</h6>
                        <span class="badge bg-{{ $header->status === 'draft' ? 'warning' : 'success' }} text-white">
                            {{ $header->status === 'draft' ? 'DRAFT' : 'PUBLISHED' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <form id="headerForm">@csrf
                            <input type="hidden" id="poId" value="{{ $header->po_id }}">

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">No. PO</label>
                                <div class="col-sm-4">
                                    <input type="text" id="poNumber" name="po_number" class="form-control"
                                        value="{{ $header->po_number }}" readonly>
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
                                <label class="col-sm-2 col-form-label">Pembelian</label>
                                <div class="col-sm-4">
                                    <select name="purchase_type" id="purchase_type" class="form-control" 
                                        {{ $header->status !== 'draft' ? 'disabled' : '' }} required>
                                        <option value="langsung" {{ $header->purchase_type == 'langsung' ? 'selected' : '' }}>Langsung</option>
                                        <option value="konsinyasi" {{ $header->purchase_type == 'konsinyasi' ? 'selected' : '' }}>Konsinyasi</option>
                                    </select>
                                </div>

                                <label class="col-sm-2 col-form-label">Lokasi</label>
                                <div class="col-sm-4">
                                    <select name="location_id" id="location_id" class="form-control" 
                                        {{ $header->status !== 'draft' ? 'disabled' : '' }} required>
                                        <option value="">Pilih Lokasi</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location }}"
                                                {{ $header->location_id == $location ? 'selected' : '' }}>
                                                {{ $location }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Tgl Kirim</label>
                                <div class="col-sm-4">
                                    <input type="date" name="delivery_date" id="delivery_date" class="form-control"
                                        value="{{ old('delivery_date', $header->delivery_date?->format('Y-m-d')) }}" 
                                        {{ $header->status !== 'draft' ? 'readonly' : '' }} required>
                                </div>
                                
                                <label class="col-sm-2 col-form-label">Status</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ strtoupper($header->status) }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Catatan</label>
                                <div class="col-sm-10">
                                    <textarea name="note" id="note" class="form-control" rows="2"
                                        {{ $header->status !== 'draft' ? 'readonly' : '' }}>{{ $header->note }}</textarea>
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
                        <i class="fas fa-plus"></i> Tambah Pesanan
                    </button>
                    @endcan
                    
                    @can('ubah', $currentMenuSlug)
                    <button id="btnPublish" class="btn btn-success mr-2">
                        <i class="fas fa-floppy-disk"></i> Simpan PO
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
                                        @php
                                            $subtotal = $detail->qty * $detail->unit_price;
                                            $discount = $subtotal * ($detail->discount_percent / 100);
                                            $tax = ($subtotal - $discount) * ($detail->tax_percent / 100);
                                            $total = $subtotal - $discount + $tax;
                                        @endphp
                                        <tr>
                                            <td>{{ $detail->product->kode_produk }}</td>
                                            <td>{{ $detail->product->nama_produk }}</td>
                                            <td class="text-right">{{ number_format($detail->qty) }}</td>
                                            <td>{{ $detail->uom->UOM_Code }}</td>
                                            <td class="text-right">{{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($detail->discount_percent, 2) }}</td>
                                            <td class="text-right">{{ number_format($detail->tax_percent, 2) }}</td>
                                            <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
                                            <td>{{ $detail->note }}</td>
                                            <td>
                                                @if($header->status === 'draft')
                                                    @can('ubah', $currentMenuSlug)
                                                    <button class="btn btn-sm btn-warning edit-btn" 
                                                        data-id="{{ $detail->detail_id }}"
                                                        data-product_id="{{ $detail->product_id }}"
                                                        data-uom_id="{{ $detail->uom_id }}"
                                                        data-qty="{{ $detail->qty }}"
                                                        data-unit_price="{{ $detail->unit_price }}"
                                                        data-tax_percent="{{ $detail->tax_percent }}"
                                                        data-discount_percent="{{ $detail->discount_percent }}"
                                                        data-note="{{ $detail->note }}">
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
                                                $grandTotal = 0;
                                                foreach($header->details as $detail) {
                                                    $subtotal = $detail->qty * $detail->unit_price;
                                                    $discount = $subtotal * ($detail->discount_percent / 100);
                                                    $tax = ($subtotal - $discount) * ($detail->tax_percent / 100);
                                                    $grandTotal += $subtotal - $discount + $tax;
                                                }
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
                                    <h5 class="modal-title">Tambah Pesanan</h5>
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
                                            <input type="number" min="1000" step="100" name="unit_price" id="unit_price" class="form-control calc-trigger" required>
                                        </div>
                                        
                                        <label class="col-sm-3 col-form-label">Disc (%)</label>
                                        <div class="col-sm-3">
                                            <input type="number" min="0" max="100" step="0.1" name="discount_percent" id="discount_percent" class="form-control calc-trigger" value="0">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Pajak (%)</label>
                                        <div class="col-sm-3">
                                            <input type="number" min="0" max="100" step="0.1" name="tax_percent" id="tax_percent" class="form-control calc-trigger" value="0">
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
                                            <textarea name="note" id="dtl_note" class="form-control" rows="2"></textarea>
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
                        <h6 class="m-0 font-weight-bold text-primary">Purchase Order: {{ $header->po_number }}</h6>
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
                                        <th>Tipe Pembelian</th>
                                        <td>{{ ucfirst($header->purchase_type) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Lokasi</th>
                                        <td>{{ $header->location_id }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Tanggal Pengiriman</th>
                                        <td>{{ $header->delivery_date->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>{{ strtoupper($header->status) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Catatan</th>
                                        <td>{{ $header->note }}</td>
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
                                                @php
                                                    $subtotal = $detail->qty * $detail->unit_price;
                                                    $discount = $subtotal * ($detail->discount_percent / 100);
                                                    $tax = ($subtotal - $discount) * ($detail->tax_percent / 100);
                                                    $total = $subtotal - $discount + $tax;
                                                @endphp
                                                <tr>
                                                    <td>{{ $detail->product->kode_produk }}</td>
                                                    <td>{{ $detail->product->nama_produk }}</td>
                                                    <td class="text-right">{{ number_format($detail->qty) }}</td>
                                                    <td>{{ $detail->uom->UOM_Code }}</td>
                                                    <td class="text-right">{{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                                    <td class="text-right">{{ number_format($detail->discount_percent, 2) }}</td>
                                                    <td class="text-right">{{ number_format($detail->tax_percent, 2) }}</td>
                                                    <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
                                                    <td>{{ $detail->note }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="font-weight-bold">
                                            <tr>
                                                <td colspan="7" class="text-right">TOTAL</td>
                                                <td class="text-right">
                                                    @php
                                                        $grandTotal = 0;
                                                        foreach($header->details as $detail) {
                                                            $subtotal = $detail->qty * $detail->unit_price;
                                                            $discount = $subtotal * ($detail->discount_percent / 100);
                                                            $tax = ($subtotal - $discount) * ($detail->tax_percent / 100);
                                                            $grandTotal += $subtotal - $discount + $tax;
                                                        }
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
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            @if ($header->status === 'draft')
                                <div>
                                    @can('ubah', $currentMenuSlug)
                                    <a href="{{ route('purchase-orders.edit', $header->po_id) }}" class="btn btn-warning">
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
            const headerId = $('#poId').val();
            const modal = $('#dtlModal');
            const form = $('#dtlForm');
            let editMode = false,
                currentDetailId = null;

            // Auto-calculate nominal in modal
            $('.calc-trigger').on('input', calculateNominal);

            function calculateNominal() {
                const qty = parseFloat($('#qty').val()) || 0;
                const unitPrice = parseFloat($('#unit_price').val()) || 0;
                const discountPercent = parseFloat($('#discount_percent').val()) || 0;
                const taxPercent = parseFloat($('#tax_percent').val()) || 0;

                // Calculate subtotal (price * qty)
                const subtotal = qty * unitPrice;

                // Apply discount and tax percentages
                const discountAmount = subtotal * (discountPercent / 100);
                const afterDiscount = subtotal - discountAmount;
                const taxAmount = afterDiscount * (taxPercent / 100);

                // Calculate final price
                const total = afterDiscount + taxAmount;
                $('#nominal').val(total.toLocaleString('id-ID'));
                $('#subtotal').val(total);
            }

            // Update product code and name when product is selected
            $('#product_id').change(function() {
                const selectedOption = $(this).find('option:selected');
                $('#product_code').val(selectedOption.data('kode'));
                $('#product_name').val(selectedOption.data('nama'));
            });

            // Header update
            $('#headerForm').on('change', 'input, select, textarea', function() {
                const data = $('#headerForm').serialize();
                $.ajax({
                    url: `/inventory/purchase-orders/${headerId}/update-header`,
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
                $('#unit_price').val($(this).data('unit_price'));
                $('#tax_percent').val($(this).data('tax_percent'));
                $('#discount_percent').val($(this).data('discount_percent'));
                $('#dtl_note').val($(this).data('note'));
                
                // Calculate nominal
                calculateNominal();
                
                modal.modal('show');
            });

            // Save detail
            $('#dtlSave').click(function() {
                const data = form.serialize();
                const url = editMode 
                    ? `/inventory/purchase-orders/${headerId}/details/${currentDetailId}`
                    : `/inventory/purchase-orders/${headerId}/details`;

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
                    title: 'Hapus Item PO?',
                    html: `Yakin ingin menghapus produk <strong>${productName}</strong> dari PO?`,
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
                            url: `/inventory/purchase-orders/${headerId}/details/${detailId}`,
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
                                    text: 'Item berhasil dihapus dari PO',
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

            // Publish PO
            $('#btnPublish').click(function() {
                Swal.fire({
                    title: 'Simpan Purchase Order?',
                    text: "Pastikan data sudah benar sebelum disimpan",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/inventory/purchase-orders/${headerId}/publish`,
                            type: 'POST',
                            success: function() {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'PO berhasil disimpan',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href = '/inventory/purchase-orders';
                                });
                            },
                            error: function(xhr) {
                                const errorMsg = JSON.parse(xhr.responseText).error;
                                showToast('error', 'Gagal menyimpan PO: ' + errorMsg);
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
                            url: `/inventory/purchase-orders/${headerId}/cancel`,
                            type: 'DELETE',
                            success: function() {
                                window.location.href = '/inventory/purchase-orders';
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

    @if(isset($purchaseOrders))
    <script>
        $(document).ready(function() {
            // Hapus PO dari halaman index
            $(document).on('click', '.delete-po-btn', function() {
                const btn = $(this);
                const poId = btn.data('id');
                const poNumber = btn.data('name');
                const row = btn.closest('tr');
                
                Swal.fire({
                    title: 'Hapus Purchase Order?',
                    html: `Yakin ingin menghapus PO <strong>${poNumber}</strong>?`,
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
                            url: `/inventory/purchase-orders/${poId}`,
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
                                    html: response.message || 'PO berhasil dihapus',
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