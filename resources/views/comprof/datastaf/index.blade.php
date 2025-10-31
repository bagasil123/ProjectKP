@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Data Staf</h1>

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
        <button type="button" class="btn btn-primary" data-toggle="modal" id="btnAddStaf">
            <i class="fas fa-plus"></i> Tambah Data Staf
        </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Profil Staf</th>
                            <th width="55%">Keterangan Staf</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffs as $index => $staff)
                            <tr>
                                <td class="align-middle text-center">{{ $index + 1 }}</td>
                                <td class="align-middle text-center">
                                    <img src="{{ asset('storage/' . $staff->profile_image) }}" alt="Profil Staf" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                </td>
                                <td class="align-middle">
                                    <div class="mb-2">
                                        <strong>Nama:</strong> {{ $staff->name }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Jabatan:</strong> {{ $staff->jabatan }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Pendidikan:</strong> {{ $staff->education }}
                                    </div>
                                    <div>
                                        <strong>Deskripsi:</strong>
                                        <div class="mt-1">
                                            {!! $staff->description !!}
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        @can('ubah', $currentMenuSlug)
                                        <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="{{ $staff->id }}"
                                            data-name="{{ $staff->name }}"
                                            data-jabatan="{{ $staff->jabatan }}"
                                            data-education="{{ $staff->education }}"
                                            data-description="{{ $staff->description }}"
                                            data-profile_image="{{ $staff->profile_image }}"
                                            data-status="{{ $staff->status }}"
                                            title="Edit Staf">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcan

                                        @can('hapus', $currentMenuSlug)
                                        <button class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $staff->id }}"
                                            data-name="{{ $staff->name }}"
                                            title="Hapus Staf">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada data staf</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Universal Modal for Add/Edit -->
    <div class="modal fade" id="universalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="mainForm" method="POST" class="modal-content" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="id" name="id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="modal-header py-2">
                    <h5 class="modal-title" id="modalTitle">Tambah Data Staf</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small mb-1">Jabatan <span class="text-danger">*</span></label>
                                <input type="text" id="jabatan" name="jabatan" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small mb-1">Pendidikan <span class="text-danger">*</span></label>
                                <textarea id="education" name="education" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small mb-1">Gambar Profil <span class="text-danger" id="imageRequired">*</span></label>
                                <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*">
                                <small class="text-muted">Format: jpeg, png, jpg | Maks: 2MB</small>

                                <div id="imagePreview" class="mt-3 text-center">
                                    <img src="" alt="Preview" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover; display: none;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="small mb-1">Deskripsi Staf <span class="text-danger">*</span></label>
                                <textarea id="description" name="description" class="form-control summernote" rows="5" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-3" id="modalSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editable {
        min-height: 150px !important;
    }
    .note-toolbar {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
    }
    #imagePreview img {
        max-width: 100%;
        max-height: 200px;
        object-fit: cover;
    }
    .table th, .table td {
        vertical-align: middle !important;
    }
    .d-flex.gap-2 {
        gap: 0.5rem;
    }
    .card-body {
        padding: 1.5rem;
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
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .img-thumbnail {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    .close {
        float: right;
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
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
$(function() {
    const modalEl = document.getElementById('universalModal');
    const modalInstance = new bootstrap.Modal(modalEl);
    const form = $('#mainForm');
    const baseUrl = "{{ route('comprof.datastaf.store') }}";

    // Inisialisasi Summernote
    $('.summernote').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ]
    });

    // Inisialisasi DataTables
    $('#dataTable').DataTable();

    // Preview gambar saat dipilih
    $('#profile_image').change(function() {
        const file = this.files[0];
        const preview = $('#imagePreview img');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        } else {
            const existingImg = preview.data('existing');
            if (existingImg) {
                preview.attr('src', existingImg).show();
            } else {
                preview.hide();
            }
        }
    });

    // Tambah Data Staf
    $('#btnAddStaf').click(() => {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Data Staf');
        $('#modalSubmit').text('Simpan');
        form.attr('action', baseUrl);
        $('#formMethod').val('POST');
        $('.summernote').summernote('reset');
        $('#imagePreview img').hide().removeData('existing');
        $('#imageRequired').show();
        modalInstance.show();
    });

    // Edit Data Staf
    $('#dataTable').on('click', '.edit-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        const editUrl = "{{ route('comprof.datastaf.update', ':id') }}".replace(':id', id);

        form.attr('action', editUrl);
        $('#id').val(id);
        $('#name').val(btn.data('name'));
        $('#jabatan').val(btn.data('jabatan'));
        $('#education').val(btn.data('education'));
        $('#description').summernote('code', btn.data('description'));

        // Tampilkan gambar yang sudah ada
        const preview = $('#imagePreview img');
        const profileImage = btn.data('profile_image');

        if (profileImage) {
            const imageUrl = "{{ asset('storage') }}/" + profileImage;
            preview.attr('src', imageUrl)
                   .show()
                   .data('existing', imageUrl);
        } else {
            preview.hide().removeData('existing');
        }

        $('#modalTitle').text('Edit Data Staf');
        $('#modalSubmit').text('Simpan Perubahan');
        $('#formMethod').val('PUT');
        $('#imageRequired').hide();
        modalInstance.show();
    });

    // Submit Form dengan AJAX
    form.on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const isEdit = $('#formMethod').val() === 'PUT';

        // Validasi gambar hanya untuk tambah data
        if (!isEdit && !formData.get('profile_image')) {
            Swal.fire({
                icon: 'error',
                title: 'Gambar Profil Diperlukan',
                text: 'Silakan pilih gambar profil untuk staf baru',
                confirmButtonText: 'OK'
            });
            return;
        }

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                modalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan pada server';
                let errors = xhr.responseJSON;

                if (xhr.status === 422 && errors && errors.errors) {
                    message = '';
                    Object.values(errors.errors).forEach(arr => {
                        arr.forEach(msg => message += msg + '<br>');
                    });
                } else if (errors && errors.message) {
                    message = errors.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error ' + xhr.status,
                    html: message,
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Hapus Data Staf
    $('#dataTable').on('click', '.delete-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');
        const deleteUrl = "{{ route('comprof.datastaf.destroy', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Hapus Data Staf?',
            html: `Yakin ingin menghapus data staf <strong>${name}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/comprof/datastaf/${id}',
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan pada server';

                        if (xhr.status === 404) {
                            message = 'Data staf tidak ditemukan';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
