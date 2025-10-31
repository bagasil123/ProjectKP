@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
@endphp

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Satuan Produk</h1>

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
            <button type="button" class="btn btn-primary" id="btnAddSatuan">
                <i class="fas fa-plus"></i> Tambah Satuan
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
                            <th class="text-center align-middle">Satuan Produk (UOM)</th>
                            <th width="10%" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($satuanProduks as $index => $satuan)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $satuan->UOM_Code }}</td>
                                <td class="text-center">
                                    @php
                                        $currentRouteName = Route::currentRouteName();
                                        $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
                                    @endphp
                                    
                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="{{ $satuan->UOM_Auto }}"
                                            data-uom_code="{{ $satuan->UOM_Code }}"
                                            title="Edit Satuan Produk">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    
                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $satuan->UOM_Auto }}"
                                            data-uom_code="{{ $satuan->UOM_Code }}"
                                            title="Hapus Satuan Produk">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Tidak ada data satuan produk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="universalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <form id="mainForm" class="modal-content">
                @csrf
                <input type="hidden" id="id" name="id" value="">

                <div class="modal-header py-2">
                    <h5 class="modal-title" id="modalTitle">Tambah Satuan Produk</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-2">
                    <div class="form-group mb-2">
                        <label for="UOM_Code" class="small mb-0">Kode Satuan (UOM) <span class="text-danger">*</span></label>
                        <input type="text" id="UOM_Code" name="UOM_Code" class="form-control form-control-sm" required>
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
$(function() {
    const modalEl = document.getElementById('universalModal');
    const modalInstance = new bootstrap.Modal(modalEl);
    let currentId = null;
    const baseInventoryUrl = "{{ url('inventory') }}";

    // Initialize DataTable
    $('#dataTable').DataTable();

    // Tambah tombol click
    $('#btnAddSatuan').click(function () {
        currentId = null;
        $('#modalTitle').text('Tambah Satuan Produk');
        $('#modalSubmit').text('Simpan');
        $('#mainForm')[0].reset();
        $('#UOM_Code').val('');
        modalInstance.show();
    });

    // Edit tombol click
    $('#dataTable').on('click', '.edit-btn', function () {
        currentId = $(this).data('id');
        const uomCode = $(this).data('uom_code');

        $('#modalTitle').text('Edit Satuan Produk');
        $('#modalSubmit').text('Simpan Perubahan');
        $('#UOM_Code').val(uomCode);
        modalInstance.show();
    });

    // Submit form add/edit
    $('#mainForm').submit(function (e) {
        e.preventDefault();

        const uomCode = $('#UOM_Code').val();
        const token = $('meta[name="csrf-token"]').attr('content');

        if (!uomCode || uomCode.trim() === '') {
            Swal.fire('Error', 'Kode Satuan wajib diisi', 'error');
            return;
        }

        let url = `${baseInventoryUrl}/satuanproduk`;
        let ajaxMethod = 'POST';
        let dataToSend = {
            _token: token,
            UOM_Code: uomCode
        };

        if (currentId !== null) {
            url = `${baseInventoryUrl}/satuanproduk/${currentId}`;
            ajaxMethod = 'POST';
            dataToSend._method = 'PUT';
        }

        $.ajax({
            url: url,
            type: ajaxMethod,
            data: dataToSend,
            success: function (response) {
                modalInstance.hide();
                Swal.fire('Sukses', response.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                let errorMsg = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        errorMsg = '';
                        $.each(xhr.responseJSON.errors, function (key, messages) {
                            errorMsg += messages.join('<br>') + '<br>';
                        });
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });

    // Hapus tombol click
    $('#dataTable').on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        const uomCode = $(this).data('uom_code');
        const token = $('meta[name="csrf-token"]').attr('content');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            html: `Hapus satuan produk <strong>${uomCode}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseInventoryUrl}/satuanproduk/${id}`,
                    type: 'POST',
                    data: {
                        _token: token,
                        _method: 'DELETE'
                    },
                    success: function (response) {
                        Swal.fire('Terhapus!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        let errorMsg = 'Terjadi kesalahan pada server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush