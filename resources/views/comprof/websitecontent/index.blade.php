@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Kelola Website</h1>

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
        <button type="button" class="btn btn-primary" data-toggle="modal" id="btnAddContent">
            <i class="fas fa-plus"></i> Tambah Konten
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
                            <th>Judul</th>
                            <th>Menu</th>
                            <th>Kategori Berita</th>
                            <th>Kategori Album</th>
                            <th>Status</th>
                            <th>Halaman Depan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contents as $index => $content)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $content->judul }}</td>
                                <td>{{ $content->submenu->nama_submenu ?? '-' }}</td>
                                <td>{{ $content->kategoriBerita->kategori_berita ?? '-' }}</td>
                                <td>{{ $content->kategoriAlbum->kategori_album ?? '-' }}</td>
                                <td>{!! $content->status ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Tidak Aktif</span>' !!}</td>
                                <td>{!! $content->halaman_depan ? '<span class="badge badge-info">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>' !!}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('ubah', $currentMenuSlug)
                                        <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="{{ $content->id }}"
                                            data-submenu_id="{{ $content->submenu_id }}"
                                            data-judul="{{ $content->judul }}"
                                            data-isi="{{ $content->isi }}"
                                            data-gambar="{{ $content->gambar }}"
                                            data-kategori_berita_id="{{ $content->kategori_berita_id }}"
                                            data-kategori_album_id="{{ $content->kategori_album_id }}"
                                            data-status="{{ $content->status }}"
                                            data-halaman_depan="{{ $content->halaman_depan }}"
                                            title="Edit Konten">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcan

                                        @can('hapus', $currentMenuSlug)
                                        <button class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $content->id }}"
                                            data-judul="{{ $content->judul }}"
                                            title="Hapus Konten">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada data konten</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="universalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="mainForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" id="id" name="id">
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="modal-header py-2">
                <h5 class="modal-title" id="modalTitle">Tambah Konten Website</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-2">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-0">Menu</label>
                            <select id="submenu_id" name="submenu_id" class="form-control form-control-sm">
                                <option value="">-- Pilih Menu --</option>
                                @foreach($submenus as $menu)
                                    <option value="{{ $menu->id }}">{{ $menu->nama_submenu }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-0">Judul <span class="text-danger">*</span></label>
                            <input type="text" id="judul" name="judul" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="small mb-0">Isi Konten <span class="text-danger">*</span></label>
                    <textarea id="isi" name="isi" class="form-control summernote" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-0">Kategori Berita</label>
                            <select id="kategori_berita_id" name="kategori_berita_id" class="form-control form-control-sm">
                                <option value="">-- Pilih Kategori Berita --</option>
                                @foreach($kategoriBeritas as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->kategori_berita }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-0">Tampilkan Album</label>
                            <select id="kategori_album_id" name="kategori_album_id" class="form-control form-control-sm">
                                <option value="">-- Pilih Kategori Album --</option>
                                @foreach($kategoriAlbums as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->kategori_album }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="small mb-0">Gambar</label>
                    <input type="file" id="gambar" name="gambar" class="form-control-file form-control-sm">
                    <div id="gambarPreview" class="mt-2"></div>
                    @can('ubah', $currentMenuSlug)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage">
                        <label class="form-check-label small" for="removeImage">
                            Hapus gambar saat disimpan
                        </label>
                    </div>
                    @endcan
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-0">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-control form-control-sm" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-0">Tampilkan di Halaman Depan <span class="text-danger">*</span></label>
                            <select id="halaman_depan" name="halaman_depan" class="form-control form-control-sm" required>
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                @can('ubah', $currentMenuSlug)
                <button type="submit" class="btn btn-primary btn-sm px-3" id="modalSubmit">Simpan</button>
                @endcan
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .badge {
        background-color: transparent !important;
        color: inherit !important;
        padding: 0.25em 0.4em;
        font-size: 0.875em;
        border: none;
        font-weight: normal;
    }
    .badge-success {
        color: #28a745 !important;
    }
    .badge-secondary {
        color: #6c757d !important;
    }
    .badge-info {
        color: #17a2b8 !important;
    }
    .img-thumbnail {
        max-width: 200px;
        max-height: 150px;
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
    .note-editor.note-frame {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.25rem !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize Summernote
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'fontname', 'fontsize', 'color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    uploadImage(files[0], $(this));
                }
            }
        });

        // Function to handle image upload in Summernote
        function uploadImage(file, editor) {
            const formData = new FormData();
            formData.append('image', file);
            
            $.ajax({
                url: '{{ route("comprof.websitecontent.upload-image") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    editor.summernote('insertImage', response.url);
                },
                error: function(xhr) {
                    console.error('Image upload failed:', xhr.responseText);
                    alert('Gagal mengunggah gambar. Silakan coba lagi.');
                }
            });
        }

        const modalEl = document.getElementById('universalModal');
        const modalInstance = new bootstrap.Modal(modalEl);
        const form = $('#mainForm');
        const baseUrl = '{{ url("comprof/websitecontent") }}';

        // Add New Content
        $('#btnAddContent').click(() => {
            form.trigger('reset');
            $('.summernote').summernote('reset');
            $('#modalTitle').text('Tambah Konten Website');
            $('#modalSubmit').text('Simpan');
            form.attr('action', baseUrl);
            $('#formMethod').val('POST');
            $('#gambarPreview').empty();
            $('#removeImage').prop('checked', false);
            modalInstance.show();
        });

        // Edit Content
        $('#dataTable').on('click', '.edit-btn', function() {
            const btn = $(this);
            const id = btn.data('id');
            form.attr('action', `${baseUrl}/${id}`);
            
            // Set values
            $('#id').val(id);
            $('#submenu_id').val(btn.data('submenu_id'));
            $('#judul').val(btn.data('judul'));
            $('.summernote').summernote('code', btn.data('isi'));
            $('#kategori_berita_id').val(btn.data('kategori_berita_id'));
            $('#kategori_album_id').val(btn.data('kategori_album_id'));
            $('#status').val(btn.data('status'));
            $('#halaman_depan').val(btn.data('halaman_depan'));
            
            // Gambar preview
            const gambar = btn.data('gambar');
            $('#gambarPreview').empty();
            if (gambar) {
                $('#gambarPreview').html(`<img src="{{ asset('storage') }}/${gambar}" alt="Gambar Konten" class="img-thumbnail">`);
            }
            $('#removeImage').prop('checked', false);
            
            $('#modalTitle').text('Edit Konten Website');
            $('#modalSubmit').text('Simpan Perubahan');
            $('#formMethod').val('PUT');
            
            modalInstance.show();
        });

        // Submit Form
        form.on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isiContent = $('.summernote').summernote('code');
            formData.set('isi', isiContent);
            
            // Tambahkan _method untuk PUT request
            if ($('#formMethod').val() === 'PUT') {
                formData.append('_method', 'PUT');
            }
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#modalSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
                },
                success: function(response) {
                    modalInstance.hide();
                    Swal.fire('Berhasil', response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan pada server';
                    let errors = xhr.responseJSON;

                    if (xhr.status === 419) {
                        message = 'Sesi telah berakhir. Silakan muat ulang halaman.';
                    } else if (xhr.status === 422 && errors && errors.errors) {
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
                },
                complete: function() {
                    $('#modalSubmit').prop('disabled', false).text('Simpan');
                }
            });
        });

        // Delete Content
        $('#dataTable').on('click', '.delete-btn', function() {
            const btn = $(this);
            const id = btn.data('id');
            const judul = btn.data('judul');
            const row = btn.parents('tr');

            Swal.fire({
                title: 'Hapus Konten?',
                html: `Yakin ingin menghapus konten <strong>${judul}</strong>?`,
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

                            if (xhr.status === 419) {
                                message = 'Sesi telah berakhir. Silakan muat ulang halaman.';
                            } else if (xhr.status === 404) {
                                message = 'Data konten tidak ditemukan';
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