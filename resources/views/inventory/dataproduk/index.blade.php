@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
@endphp

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Data Produk</h1>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @can('tambah', $currentMenuSlug)
    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-toggle="modal" id="btnAddDataProduk">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>
    @endcan
    <!-- Tabel data -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="2%" class="text-center align-middle">No</th>
                            <th width="5%" class="text-center align-middle">Kode</th>
                            <th width="15%" class="text-center align-middle">Nama Produk</th>
                            <th width="15%" class="text-center align-middle">Supplier</th>
                            <th width="2%" class="text-center align-middle">Qty</th>
                            <th width="15%" class="text-center align-middle">Harga Beli</th>
                            <th width="15%" class="text-center align-middle">Harga Jual</th>
                            <th width="2%" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($dtproduks as $index => $p)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $p->kode_produk }}</td>
                            <td>{{ $p->nama_produk }}</td>
                            <td>{{ $p->supplier->nama_supplier ?? '-' }}</td>
                            <td>{{ $p->qty }}</td>
                            <td>Rp{{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                            <td>
                                @can('ubah', $currentMenuSlug)
                                <button class="btn btn-sm btn-warning edit-btn"
                                    data-id="{{ $p->id }}"
                                    data-kode="{{ $p->kode_produk }}"
                                    data-nama="{{ $p->nama_produk }}"
                                    data-supplier="{{ $p->supplier_id }}"
                                    data-qty="{{ $p->qty }}"
                                    data-harga_beli="{{ $p->harga_beli }}"
                                    data-harga_jual="{{ $p->harga_jual }}"
                                    title="Edit Produk">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                
                                @can('hapus', $currentMenuSlug)
                                <button class="btn btn-sm btn-danger delete-btn"
                                    data-id="{{ $p->id }}"
                                    data-nama="{{ $p->nama_produk }}"
                                    title="Hapus Produk">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Tidak ada data produk</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Universal Modal for Add/Edit -->
@canany(['tambah', 'ubah'], $currentMenuSlug)
<div class="modal fade" id="universalModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="mainForm" method="POST" class="modal-content"> 
            @csrf
            <input type="hidden" id="id" name="id">
            <input type="hidden" name="_method" value="POST">
            
            <div class="modal-header py-2">
                <h5 class="modal-title h6" id="modalTitle">Tambah Produk</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-2">
                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small mb-0">Kode Produk <span class="text-danger">*</span></label>
                            <input type="text" id="kode_produk" name="kode_produk" class="form-control form-control-sm" required>
                        </div>
                        
                        <div class="form-group mb-2">
                            <label class="small mb-0">Qty <span class="text-danger">*</span></label>
                            <input type="number" id="qty" name="qty" class="form-control form-control-sm" required>
                        </div>
                        
                        <div class="form-group mb-2">
                            <label class="small mb-0">Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" id="harga_beli" name="harga_beli" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    
                    <!-- Kolom Kanan -->
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small mb-0">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" id="nama_produk" name="nama_produk" class="form-control form-control-sm" required>
                        </div>
                        
                        <div class="form-group mb-2">
                            <label class="small mb-0">Supplier <span class="text-danger">*</span></label>
                            <select id="supplier_id" name="supplier_id" class="form-control form-control-sm" required>
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group mb-2">
                            <label class="small mb-0">Harga Jual <span class="text-danger">*</span></label>
                            <input type="number" id="harga_jual" name="harga_jual" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm px-3" id="modalSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endcanany
@endsection

@push('styles')
<style>
    /* Atur vertikal tengah untuk semua sel */
    #dataTable th, 
    #dataTable td {
        vertical-align: middle !important;
    }
    
    /* Atur horizontal alignment */
    #dataTable th {
        text-align: center;
    }
    #dataTable td:nth-child(1), /* No */
    #dataTable td:nth-child(2), /* Kode */
    #dataTable td:nth-child(5), /* Qty */
    #dataTable td:nth-child(6), /* Harga Beli */
    #dataTable td:nth-child(7), /* Harga Jual */
    #dataTable td:nth-child(8)  /* Aksi */ {
        text-align: center;
    }
    
    /* Handle teks panjang di nama produk dan supplier */
    #dataTable td:nth-child(3),
    #dataTable td:nth-child(4) {
        white-space: normal !important;
        word-wrap: break-word;
    }
    
    /* Perbaikan tampilan untuk mobile */
    @media (max-width: 768px) {
        #dataTable td:nth-child(3),
        #dataTable td:nth-child(4) {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    const modal = $('#universalModal');
    const form = $('#mainForm');
    const baseUrl = "{{ route('dataproduk.store') }}";

    $('#dataTable').DataTable();

    // Reset & Open Add Modal
    $('#btnAddDataProduk').click(() => {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Produk Baru');
        $('#modalSubmit').text('Simpan');
        form.attr('action', baseUrl);
        form.find('input[name="_method"]').val('POST');
        $('#id').val('');
        modal.modal('show');
    });

    // Open Edit Modal
    $('#dataTable').on('click', '.edit-btn', function() {
        let btn = $(this);
        let id = btn.data('id');

        $('#modalTitle').text('Edit Produk');
        $('#modalSubmit').text('Simpan Perubahan');
        form.attr('action', `${baseUrl}/${id}`);
        form.find('input[name="_method"]').val('PUT');

        // Populate form fields
        $('#id').val(id);
        $('#kode_produk').val(btn.data('kode'));
        $('#nama_produk').val(btn.data('nama'));
        $('#supplier_id').val(btn.data('supplier'));
        $('#qty').val(btn.data('qty'));
        $('#harga_beli').val(btn.data('harga_beli'));
        $('#harga_jual').val(btn.data('harga_jual'));

        modal.modal('show');
    });

    // Submit Form with AJAX
    form.on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                modal.modal('hide');
                Swal.fire('Berhasil', response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.responseText);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let messages = '';
                    Object.values(errors).forEach(arr => arr.forEach(msg => messages += msg + '<br>'));
                    Swal.fire('Error', messages, 'error');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    Swal.fire('Error', xhr.responseJSON.message, 'error');
                } else {
                    Swal.fire('Error', 'Terjadi kesalahan pada server. Status: ' + xhr.status, 'error');
                }
            }
        });
    });

    // Delete Product
    $('#dataTable').on('click', '.delete-btn', function() {
        let btn = $(this);
        let id = btn.data('id');
        let nama = btn.data('nama');
        let row = btn.closest('tr');

        Swal.fire({
            title: 'Hapus Produk?',
            html: `Yakin ingin menghapus produk <strong>${nama}</strong>?`,
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
                    url: `${baseUrl}/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: 'json',
                    success: function(response) {
                        row.fadeOut(400, function() {
                            row.remove();
                        });
                        Swal.fire('Terhapus!', response.message, 'success');
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan pada server';
                        if (xhr.status === 404) {
                            message = 'Data produk tidak ditemukan';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush