@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Manajemen Penjualan</h1>
    <p class="mb-4">Daftar transaksi penjualan dan pembuatan transaksi baru.</p>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    @endif

    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
        @endphp
        @can('tambah', $currentMenuSlug)
        <button type="button" class="btn btn-primary" id="btnTambahJualan">
            <i class="fas fa-plus fa-sm"></i> Buat Penjualan Baru
        </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Transaksi Penjualan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">No. Jualan</th>
                            <th>Pelanggan</th>
                            <th class="text-center">No. CO</th>
                            <th class="text-center">Tgl. Kirim</th>
                            <th class="text-center">Jatuh Tempo</th>
                            <th class="text-right">Netto</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jualans as $index => $jualan)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $jualan->no_jualan }}</td>
                            <td>{{ $jualan->pelanggan->anggota ?? 'N/A' }}</td>
                            <td class="text-center">{{ $jualan->customerOrder->no_order ?? 'N/A' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($jualan->tgl_kirim)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $jualan->jatuh_tempo ? \Carbon\Carbon::parse($jualan->jatuh_tempo)->format('d/m/Y') : '-' }}</td>
                            <td class="text-right">Rp{{ number_format($jualan->netto, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $jualan->status == 'Draft' ? 'secondary' : 'success' }}">{{ $jualan->status }}</span>
                            </td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data jualan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for creating a new sale -->
<div class="modal fade" id="jualanModal" tabindex="-1" role="dialog" aria-labelledby="jualanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="jualanModalLabel">Buat Jualan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formJualan" action="{{ route('penjualan.store') }}" method="POST">
                    @csrf
                    {{-- Form Header --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row"><label class="col-sm-4 col-form-label-sm">NO# Jualan</label><div class="col-sm-8"><input type="text" class="form-control form-control-sm" value="AUTO" readonly></div></div>
                            <div class="form-group row"><label class="col-sm-4 col-form-label-sm">Pelanggan <span class="text-danger">*</span></label><div class="col-sm-8"><select class="form-control form-control-sm" id="pelanggan_id" name="pelanggan_id" required><option value="" data-lama_bayar="0">--- Pilih Pelanggan ---</option>@foreach($pelanggans as $p)<option value="{{ $p->id }}" data-lama_bayar="{{ $p->lama_bayar ?? 0 }}">{{ $p->anggota }}</option>@endforeach</select></div></div>
                            <div class="form-group row"><label class="col-sm-4 col-form-label-sm">No# CO <span class="text-danger">*</span></label><div class="col-sm-8"><select class="form-control form-control-sm" id="customer_order_id" name="customer_order_id" required disabled><option>--- Pilih Pelanggan ---</option></select></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row"><label class="col-sm-4 col-form-label-sm">Tgl. Kirim <span class="text-danger">*</span></label><div class="col-sm-8"><input type="date" class="form-control form-control-sm" id="tgl_kirim" name="tgl_kirim" value="{{ date('Y-m-d') }}" required></div></div>
                            <div class="form-group row"><label class="col-sm-4 col-form-label-sm">Jatuh Tempo</label><div class="col-sm-8"><input type="date" class="form-control form-control-sm" id="jatuh_tempo" name="jatuh_tempo" readonly></div></div>
                            <div class="form-group row"><label class="col-sm-4 col-form-label-sm">PO Pelanggan</label><div class="col-sm-8"><input type="text" class="form-control form-control-sm" id="po_pelanggan" name="po_pelanggan" readonly></div></div>
                        </div>
                    </div>
                    <hr>
                    {{-- Item Details Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="jualanDetailTable">
                            <thead class="thead-light"><tr><th>Produk</th><th width="10%">Qty</th><th>Satuan</th><th>Harga</th><th width="8%">Disc(%)</th><th>Pajak</th><th>Nominal</th><th>Catatan</th></tr></thead>
                            <tbody><tr><td colspan="8" class="text-center text-muted">Pilih Customer Order untuk menampilkan data.</td></tr></tbody>
                        </table>
                    </div>
                    <hr>
                    {{-- Totals --}}
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="form-group row align-items-center"><label class="col-sm-4 col-form-label">Bruto</label><div class="col-sm-8"><input type="text" class="form-control text-right" id="bruto" readonly></div></div>
                            <div class="form-group row align-items-center"><label class="col-sm-4 col-form-label">Total Disc.</label><div class="col-sm-8"><input type="text" class="form-control text-right" id="total_disc" readonly></div></div>
                            <div class="form-group row align-items-center"><label class="col-sm-4 col-form-label">Total Pajak</label><div class="col-sm-8"><input type="text" class="form-control text-right" id="total_pajak" readonly></div></div>
                            <div class="form-group row font-weight-bold align-items-center"><label class="col-sm-4 col-form-label">Netto</label><div class="col-sm-8"><input type="text" class="form-control text-right" id="netto" readonly></div></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanJualan"><i class="fas fa-save mr-1"></i> Simpan Jualan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // --- Config ---
    const modal = $('#jualanModal');
    const form = $('#formJualan');
    // Correctly define API routes using Laravel's route() helper
    const outstandingOrdersUrlTpl = "{{ route('api.jualan.outstanding-orders', ':pelanggan') }}";
    const orderDetailsUrlTpl = "{{ route('api.jualan.order-details', ':customerOrder') }}";

    // --- Initialize DataTable ---
    $('#dataTable').DataTable({ "language": { "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json" } });

    // --- Helper Functions ---
    function formatCurrency(num) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num); }
    
    function calculateDueDate() {
        const tglKirim = $('#tgl_kirim').val();
        const lamaBayar = parseInt($('#pelanggan_id').find('option:selected').data('lama_bayar')) || 0;
        if (tglKirim) {
            let dueDate = new Date(tglKirim);
            dueDate.setDate(dueDate.getDate() + lamaBayar);
            $('#jatuh_tempo').val(dueDate.toISOString().split('T')[0]);
        } else {
            $('#jatuh_tempo').val('');
        }
    }

    function updateTotals() {
        let bruto = 0, totalDisc = 0, totalPajak = 0;
        $('#jualanDetailTable tbody tr').each(function() {
            if ($(this).find('td').length <= 1) return;
            const row = $(this);
            const qty = parseFloat(row.find('.input-qty').val()) || 0;
            const harga = parseFloat(row.find('.input-harga').val()) || 0;
            const discPercent = parseFloat(row.find('.input-disc').val()) || 0;
            const pajak = parseFloat(row.find('.input-pajak').val()) || 0;
            
            const totalHarga = qty * harga;
            const discAmount = totalHarga * (discPercent / 100);
            const nominal = totalHarga - discAmount + pajak;
            
            row.find('.input-nominal').val(nominal.toFixed(2));
            bruto += totalHarga;
            totalDisc += discAmount;
            totalPajak += pajak;
        });
        const netto = bruto - totalDisc + totalPajak;
        $('#bruto').val(formatCurrency(bruto));
        $('#total_disc').val(formatCurrency(totalDisc));
        $('#total_pajak').val(formatCurrency(totalPajak));
        $('#netto').val(formatCurrency(netto));
    }

    // --- Event Handlers ---
    $('#btnTambahJualan').on('click', function() {
        form.trigger('reset');
        $('#jualanModalLabel').text('Buat Jualan Baru');
        $('#customer_order_id').html('<option>--- Pilih Pelanggan ---</option>').prop('disabled', true);
        $('#jualanDetailTable tbody').html('<tr><td colspan="8" class="text-center text-muted">Pilih Customer Order.</td></tr>');
        $('#tgl_kirim').val(new Date().toISOString().split('T')[0]);
        calculateDueDate();
        updateTotals();
        modal.modal('show');
    });

    $('#btnSimpanJualan').on('click', () => form.submit());

    form.on('change', '#pelanggan_id, #tgl_kirim', calculateDueDate);

    form.on('input', '.input-qty, .input-disc, .input-pajak', updateTotals);

    form.on('change', '#pelanggan_id', function() {
        const customerId = $(this).val();
        const coSelect = $('#customer_order_id');
        $('#jualanDetailTable tbody').html('<tr><td colspan="8" class="text-center text-muted">Pilih Customer Order.</td></tr>');
        updateTotals();

        if (!customerId) {
            coSelect.html('<option>--- Pilih Pelanggan ---</option>').prop('disabled', true);
            return;
        }
        
        coSelect.html('<option>Memuat...</option>').prop('disabled', true);
        $.get(outstandingOrdersUrlTpl.replace(':pelanggan', customerId), function(orders) {
            coSelect.html('<option value="">--- Pilih CO ---</option>');
            if (orders.length > 0) {
                orders.forEach(o => coSelect.append(`<option value="${o.id}" data-po="${o.po_pelanggan || ''}">${o.no_order}</option>`));
                coSelect.prop('disabled', false);
            } else {
                coSelect.html('<option value="">--- Tidak ada CO ---</option>');
            }
        }).fail(() => coSelect.html('<option value="">--- Error ---</option>'));
    });

    form.on('change', '#customer_order_id', function() {
        const coId = $(this).val();
        const po = $(this).find('option:selected').data('po');
        const tableBody = $('#jualanDetailTable tbody');
        $('#po_pelanggan').val(po);

        if (!coId) {
            tableBody.html('<tr><td colspan="8" class="text-center text-muted">Pilih Customer Order.</td></tr>');
            updateTotals();
            return;
        }

        $.ajax({
            url: orderDetailsUrlTpl.replace(':customerOrder', coId),
            beforeSend: () => tableBody.html('<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>'),
            success: function(details) {
                tableBody.empty();
                if (details.length > 0) {
                    details.forEach(item => {
                        // Ensure product is not null
                        const productName = item.product ? item.product.nama : 'Produk tidak ditemukan';
                        const productSatuan = item.product ? item.product.satuan : 'N/A';
                        
                        tableBody.append(`
                            <tr>
                                <td><input type="text" value="${productName}" class="form-control form-control-sm" readonly></td>
                                <td><input type="number" name="items[${item.id}][qty]" value="${item.qty}" class="form-control form-control-sm text-right input-qty" step="any"></td>
                                <td><input type="text" value="${productSatuan}" class="form-control form-control-sm" readonly></td>
                                <td><input type="number" value="${item.harga}" class="form-control form-control-sm text-right input-harga" readonly step="any"></td>
                                <td><input type="number" name="items[${item.id}][disc]" value="${item.disc || 0}" class="form-control form-control-sm text-right input-disc" step="any"></td>
                                <td><input type="number" name="items[${item.id}][pajak]" value="${item.pajak || 0}" class="form-control form-control-sm text-right input-pajak" step="any"></td>
                                <td><input type="number" name="items[${item.id}][nominal]" class="form-control form-control-sm text-right input-nominal" readonly step="any"></td>
                                <td><input type="text" name="items[${item.id}][catatan]" value="${item.catatan || ''}" class="form-control form-control-sm"></td>
                            </tr>
                        `);
                    });
                    updateTotals();
                } else {
                    tableBody.html('<tr><td colspan="8" class="text-center text-muted">Tidak ada item detail di CO ini.</td></tr>');
                }
            },
            error: () => tableBody.html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data.</td></tr>')
        });
    });

    form.on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#btnSimpanJualan');
        const originalHtml = submitBtn.html();

        if (!$('#pelanggan_id').val() || !$('#customer_order_id').val()) {
            Swal.fire('Error!', 'Pelanggan dan Customer Order wajib diisi.', 'error');
            return;
        }
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            beforeSend: () => submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...'),
            success: function(response) {
                modal.modal('hide');
                Swal.fire('Berhasil!', response.message, 'success').then(() => location.reload());
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan.';
                if (xhr.responseJSON) {
                    errorMsg = xhr.responseJSON.message || (xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : errorMsg);
                }
                Swal.fire('Error!', errorMsg, 'error');
            },
            complete: () => submitBtn.prop('disabled', false).html(originalHtml)
        });
    });
});
</script>
@endpush
