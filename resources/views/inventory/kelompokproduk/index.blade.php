@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
@endphp

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Kelompok Produk</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tombol Tambah dengan Permission Check -->
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
        @endphp

        @can('tambah', $currentMenuSlug)
            <button type="button" class="btn btn-primary" data-toggle="modal" id="btnAddKelompok">
                <i class="fas fa-plus"></i> Tambah Kelompok
            </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="2%" class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Nama Kelompok</th>
                            <th width="10%" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelompokProduks as $index => $kelompok)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $kelompok->nama_kelompok }}</td>
                                <td class="text-center">
                                    @php
                                        $currentRouteName = Route::currentRouteName();
                                        $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
                                    @endphp

                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="{{ $kelompok->id }}"
                                            data-nama="{{ $kelompok->nama_kelompok }}"
                                            title="Edit Kelompok Produk">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan

                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $kelompok->id }}"
                                            data-nama="{{ $kelompok->nama_kelompok }}"
                                            title="Hapus Kelompok Produk">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Tidak ada data kelompok produk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Tambah/Edit -->
    <div class="modal fade" id="universalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <form id="mainForm" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" id="id" name="id">

                <div class="modal-header py-2">
                    <h5 class="modal-title" id="modalTitle">Tambah Kelompok Produk</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-2">
                    <div class="form-group mb-2">
                        <label class="small mb-0">Nama Kelompok <span class="text-danger">*</span></label>
                        <input type="text" id="nama_kelompok" name="nama_kelompok"
                               class="form-control form-control-sm" required>
                    </div>
                </div>

                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" id="modalSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Pastikan semua tombol terlihat */
    .btn {
        display: inline-block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Atur vertikal tengah untuk semua sel */
    #dataTable th,
    #dataTable td {
        vertical-align: middle !important;
    }

    /* Atur horizontal alignment */
    #dataTable th {
        text-align: center;
    }

    /* Kolom No dan Aksi di tengah */
    #dataTable td:first-child,
    #dataTable td:last-child {
        text-align: center;
    }

    /* Kolom Nama Kelompok rata kiri */
    #dataTable td:nth-child(2) {
        text-align: left;
    }

    /* Responsive design untuk mobile */
    @media (max-width: 768px) {
        #dataTable td:nth-child(2) {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    const modal = new bootstrap.Modal('#universalModal');
    const form = $('#mainForm');
    let dataTable;

    // Definisikan routes di awal
    const routes = {
        store: "{{ route('kelompokproduk.store') }}",
        update: "{{ route('kelompokproduk.update', ['kelompokproduk' => ':id']) }}",
        destroy: "{{ route('kelompokproduk.destroy', ['kelompokproduk' => ':id']) }}"
    };

    // Inisialisasi DataTable
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            dataTable.destroy();
        }
        dataTable = $('#dataTable').DataTable({
            "order": [],
            "columnDefs": [
                { "orderable": false, "targets": [0, 2] }
            ]
        });
    }
    initDataTable();

    // Tambah Data
    $('#btnAddKelompok').click(function() {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Kelompok Produk');
        $('#formMethod').val('POST');
        form.attr('action', routes.store);
        modal.show();
    });

    // Edit Data
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');

        $('#modalTitle').text('Edit Kelompok Produk');
        $('#formMethod').val('PUT');
        $('#id').val(id);
        $('#nama_kelompok').val(nama);

        // Set action URL dengan mengganti placeholder :id
        form.attr('action', routes.update.replace(':id', id));

        modal.show();
    });

    // Submit Form
    form.on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const url = form.attr('action');
        const method = $('#formMethod').val();

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                modal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = '';
                    $.each(errors, function(key, value) {
                        errorMessage += value[0] + '\n';
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMessage
                });
            }
        });
    });

    // Hapus Data
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const url = routes.destroy.replace(':id', id);
        const row = $(this).closest('tr');

        Swal.fire({
            title: 'Hapus Kelompok Produk?',
            html: `Yakin ingin menghapus <strong>${nama}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
$.ajax({
    url: `/inventory/kelompokproduk/${id}`,
    method: 'DELETE',
    dataType: 'json',
    data: {
        _token: "{{ csrf_token() }}"
    },
    success: function(response) {
        row.fadeOut(400, function() {
            row.remove();
        });
        Swal.fire({
            icon: 'success',
            title: 'Terhapus!',
            html: response.message,
            timer: 2000,
            showConfirmButton: false
        });

        // Hapus row dan perbarui nomor urut
        dataTable.row(row).remove().draw(false);

        // Perbarui nomor urut
        $('#dataTable tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    },
    error: function(xhr) {
        let message = 'Terjadi kesalahan pada server';

        if (xhr.status === 404) {
            message = 'Data kelompok produk tidak ditemukan';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }

        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            html: message,
            timer: 3000,
            showConfirmButton: true
        });
    }
});

            }
        });
    });
});
</script>
@endpush
