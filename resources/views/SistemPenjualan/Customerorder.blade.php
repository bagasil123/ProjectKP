@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Pesanan Pelanggan</h1>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="mb-3">
        @php
        $currentRouteName = Route::currentRouteName();
        $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
        @endphp
        @can('tambah', $currentMenuSlug) 
        <button type="button" class="btn btn-primary" id="btnAddCustomerOrder">
            <i class="fas fa-plus fa-sm"></i> Tambah Pesanan Baru
        </button>
        @endcan
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Pelanggan</th>
                            <th class="text-center">No# Order</th>
                            <th class="text-center">PO Pelanggan</th>
                            <th class="text-center">Tgl. Kirim</th>
                            <th class="text-right">Netto</th>
                            <th class="text-center">Tgl. Pesan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerOrders as $index => $order)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $order->pelanggan->anggota ?? 'N/A' }}</td>
                                <td class="text-center">{{ $order->no_order }}</td>
                                <td>{{ $order->po_pelanggan ?? '-' }}</td>
                                <td class="text-center">{{ $order->tgl_kirim ? \Carbon\Carbon::parse($order->tgl_kirim)->format('d/m/Y') : '-' }}</td>
                                <td class="text-right">Rp{{ number_format($order->netto, 0, ',', '.') }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($order->tanggal_pesan)->format('d/m/Y') }}</td>
                                <td class="text-center">
                                     <span class="badge badge-pill badge-{{ strtolower($order->status) == 'selesai' ? 'success' : (strtolower($order->status) == 'batal' ? 'danger' : 'warning') }}">
                                        {{ $order->status ?? 'Draft' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn" title="Edit Pesanan"
                                        data-id="{{ $order->id }}"
                                        data-pelanggan_id="{{ $order->pelanggan_id }}"
                                        data-no_order="{{ $order->no_order }}"
                                        data-po_pelanggan="{{ $order->po_pelanggan }}"
                                        data-tgl_kirim="{{ $order->tgl_kirim ? \Carbon\Carbon::parse($order->tgl_kirim)->format('Y-m-d') : '' }}"
                                        data-bruto="{{ $order->bruto }}"
                                        data-disc="{{ $order->disc }}"
                                        data-pajak="{{ $order->pajak }}"
                                        data-netto="{{ $order->netto }}"
                                        data-tanggal_pesan="{{ \Carbon\Carbon::parse($order->tanggal_pesan)->format('Y-m-d') }}"
                                        data-status="{{ $order->status }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn" title="Hapus Pesanan"
                                        data-id="{{ $order->id }}"
                                        data-no_order="{{ $order->no_order }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Universal Modal for Add/Edit -->
<div class="modal fade" id="universalModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="mainForm" method="POST" class="modal-content"> 
            @csrf
            <input type="hidden" name="_method" value="POST">
            
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Pesanan Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pelanggan <span class="text-danger">*</span></label>
                            <select id="pelanggan_id" name="pelanggan_id" class="form-control" required>
                                <option value="">Pilih Pelanggan</option>
                                @foreach($pelanggans as $pelanggan)
                                    <option value="{{ $pelanggan->id }}">{{ $pelanggan->anggota }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>No# Order</label>
                            <input type="text" id="no_order" name="no_order" class="form-control" value="AUTO" readonly>
                        </div>
                        <div class="form-group">
                            <label>PO Pelanggan</label>
                            <input type="text" id="po_pelanggan" name="po_pelanggan" class="form-control">
                        </div>
                         <div class="form-group">
                            <label>Tanggal Pesan <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_pesan" name="tanggal_pesan" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Kirim</label>
                            <input type="date" id="tgl_kirim" name="tgl_kirim" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Bruto <span class="text-danger">*</span></label>
                            <input type="number" id="bruto" name="bruto" class="form-control" required step="any">
                        </div>
                        <div class="form-group">
                            <label>Disc (%)</label>
                            <input type="number" id="disc" name="disc" class="form-control" min="0" max="100" step="any">
                        </div>
                        <div class="form-group">
                            <label>Pajak</label>
                            <input type="number" id="pajak" name="pajak" class="form-control" step="any">
                        </div>
                        <div class="form-group">
                            <label>Netto <span class="text-danger">*</span></label>
                            <input type="number" id="netto" name="netto" class="form-control" required readonly step="any">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="Draft">Draft</option>
                                <option value="Dikirim">Dikirim</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Batal">Batal</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="modalSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    // --- Configuration ---
    // Standardized route names. Ensure these are defined in your routes/web.php.
    const storeUrl = "{{ route('customer-orders.store') }}";
    const updateUrlTpl = "{{ route('customer-orders.update', ':id') }}";
    const deleteUrlTpl = "{{ route('customer-orders.destroy', ':id') }}";
    const csrfToken = "{{ csrf_token() }}";

    const modal = $('#universalModal');
    const form = $('#mainForm');

    // --- Initialize DataTable ---
    $('#dataTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json" }
    });

    // --- Helper Functions ---
    function calculateNetto() {
        let bruto = parseFloat($('#bruto').val()) || 0;
        let discPercent = parseFloat($('#disc').val()) || 0;
        let pajak = parseFloat($('#pajak').val()) || 0;
        let discountAmount = bruto * (discPercent / 100);
        let netto = bruto - discountAmount + pajak;
        $('#netto').val(netto.toFixed(2));
    }

    // --- Event Handlers ---
    $('#bruto, #disc, #pajak').on('input', calculateNetto);

    $('#btnAddCustomerOrder').click(function() {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Pesanan Pelanggan Baru');
        $('#modalSubmit').text('Simpan');
        form.attr('action', storeUrl);
        $('input[name="_method"]').val('POST');
        $('#no_order').val('AUTO');
        $('#tanggal_pesan').val(new Date().toISOString().split('T')[0]);
        calculateNetto();
        modal.modal('show');
    });

    $('#dataTable').on('click', '.edit-btn', function() {
        let btn = $(this);
        let id = btn.data('id');
        
        form.trigger('reset');
        $('#modalTitle').text('Edit Pesanan Pelanggan');
        $('#modalSubmit').text('Simpan Perubahan');
        let actionUrl = updateUrlTpl.replace(':id', id);
        form.attr('action', actionUrl);
        $('input[name="_method"]').val('PUT');

        // Populate form fields
        $('#pelanggan_id').val(btn.data('pelanggan_id'));
        $('#no_order').val(btn.data('no_order'));
        $('#po_pelanggan').val(btn.data('po_pelanggan'));
        $('#tgl_kirim').val(btn.data('tgl_kirim'));
        $('#bruto').val(btn.data('bruto'));
        $('#disc').val(btn.data('disc'));
        $('#pajak').val(btn.data('pajak'));
        $('#tanggal_pesan').val(btn.data('tanggal_pesan'));
        $('#status').val(btn.data('status'));
        calculateNetto(); // Recalculate and set netto

        modal.modal('show');
    });

    form.on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#modalSubmit');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                modal.modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server.';
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Error', html: errorMessage });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    $('#dataTable').on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        let noOrder = $(this).data('no_order');

        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus pesanan <strong>${noOrder}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrlTpl.replace(':id', id),
                    method: 'POST',
                    data: {
                        '_method': 'DELETE',
                        '_token': csrfToken
                    },
                    success: function(response) {
                        Swal.fire('Terhapus!', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus pesanan.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
