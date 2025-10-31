@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Kategori Album</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $currentRouteName = Route::currentRouteName();
        $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
    @endphp

    <div class="mb-3">
        @can('tambah', $currentMenuSlug)
        <button type="button" class="btn btn-primary" data-toggle="modal" id="btnAddKategori">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Kategori Album</th>
                            <th>Tampilkan Gallery</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $index => $kategori)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $kategori->kategori_album }}</td>
                                <td>{!! $kategori->tampil_gallery_html !!}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('ubah', $currentMenuSlug)
                                        <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="{{ $kategori->id }}"
                                            data-kategori="{{ $kategori->kategori_album }}"
                                            data-tampil-gallery="{{ $kategori->tampil_gallery }}"
                                            title="Edit Kategori">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcan

                                        @can('hapus', $currentMenuSlug)
                                        <button class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $kategori->id }}"
                                            data-kategori="{{ $kategori->kategori_album }}"
                                            title="Hapus Kategori">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada data kategori</td>
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
            <form id="mainForm" method="POST" class="modal-content">
                @csrf
                <input type="hidden" id="id" name="id">
                @method('POST')

                <div class="modal-header py-2">
                    <h5 class="modal-title" id="modalTitle">Tambah Kategori Album</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-2">
                    <div class="form-group mb-2">
                        <label class="small mb-0">Kategori Album <span class="text-danger">*</span></label>
                        <input type="text" id="kategori_album" name="kategori_album" class="form-control form-control-sm" required>
                    </div>

                    <div class="form-group mb-2">
                        <label class="small mb-0">Tampilkan Gallery <span class="text-danger">*</span></label>
                        <select id="tampil_gallery" name="tampil_gallery" class="form-control form-control-sm" required>
                            <option value="1">Tampil Gallery</option>
                            <option value="0">Tidak Tampil Gallery</option>
                        </select>
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
    .badge {
        background-color: transparent !important;
        color: inherit !important;
        padding: 0.25em 0.4em;
        font-size: 0.875em;
        border: none;
        font-weight: normal;
    }
    .d-flex.gap-2 {
        gap: 0.5rem;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .alert {
        border-left: 4px solid;
    }
    .modal-header {
        padding: 0.75rem 1.5rem;
    }
    .modal-footer {
        padding: 0.75rem 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    const modalEl = document.getElementById('universalModal');
    const modalInstance = new bootstrap.Modal(modalEl);
    const form = $('#mainForm');
    const baseComprofUrl = "{{ url('comprof') }}";

    // Inisialisasi DataTables
    $('#dataTable').DataTable();

    // Tambah Kategori
    $('#btnAddKategori').click(() => {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Kategori Album');
        $('#modalSubmit').text('Simpan');
        form.attr('action', `${baseComprofUrl}/kategorialbum`);
        form.find('input[name="_method"]').val('POST');
        modalInstance.show();
    });

    // Edit Kategori
    $('#dataTable').on('click', '.edit-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        form.attr('action', `${baseComprofUrl}/kategorialbum/${id}`);

        $('#id').val(id);
        $('#kategori_album').val(btn.data('kategori'));
        $('#tampil_gallery').val(btn.data('tampil-gallery'));
        $('#modalTitle').text('Edit Kategori Album');
        $('#modalSubmit').text('Simpan Perubahan');
        form.find('input[name="_method"]').val('PUT');

        modalInstance.show();
    });

    // Submit Form
    form.on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: form.attr('action'),
            method: form.find('input[name="_method"]').val(),
            data: form.serialize(),
            success: function(response) {
                modalInstance.hide();
                Swal.fire('Berhasil', response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let messages = '';
                    Object.values(errors).forEach(arr => arr.forEach(msg => messages += msg + '<br>'));
                    Swal.fire('Error', messages, 'error');
                } else {
                    Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
                }
            }
        });
    });

    // Hapus Kategori
    $('#dataTable').on('click', '.delete-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        const kategori = btn.data('kategori');
        const deleteUrl = `{{ route('comprof.kategorialbum.destroy', ':id') }}`.replace(':id', id);
        const row = btn.parents('tr');

        Swal.fire({
            title: 'Hapus Kategori?',
            html: `Yakin ingin menghapus kategori <strong>${kategori}</strong>?`,
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
                    url: '/comprof/kategorialbum/${id}',
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        row.fadeOut(400, function() {
                            row.remove();
                            // Re-number the table
                            $('#dataTable tbody tr').each(function(index) {
                                $(this).find('td:first').text(index + 1);
                            });
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            html: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan pada server';

                        if (xhr.status === 404) {
                            message = 'Data kategori tidak ditemukan';
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
