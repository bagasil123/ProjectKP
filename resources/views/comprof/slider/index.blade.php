@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Setting Slider</h1>

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
        <button type="button" class="btn btn-primary" data-toggle="modal" id="btnAddSlider">
            <i class="fas fa-plus"></i> Tambah Slider
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
                            <th width="25%">Gambar</th>
                            <th width="40%">Judul</th>
                            <th width="15%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($sliders as $index => $slider)
                        <tr data-link="{{ $slider->link }}">
                            <td class="align-middle text-center">{{ $index + 1 }}</td>
                            <td class="align-middle text-center">
                                <img src="{{ $slider->image_url }}" alt="Slider Image" class="img-thumbnail" style="width: 250px; height: 125px; object-fit: cover;">
                            </td>
                            <td class="align-middle">
                                <div class="slider-title-preview">
                                    {!! $slider->clean_title !!}
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                {!! $slider->status_html !!}
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn"
                                        data-id="{{ $slider->id }}"
                                        data-title="{{ $slider->title }}"
                                        data-link="{{ $slider->link }}"
                                        data-image_url="{{ $slider->image_url }}"
                                        data-status="{{ $slider->status }}"
                                        title="Edit Slider">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan

                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn"
                                        data-id="{{ $slider->id }}"
                                        data-title="{{ $slider->title }}"
                                        title="Hapus Slider">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data slider</td>
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
                    <h5 class="modal-title" id="modalTitle">Tambah Slider</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-3">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small mb-1">Isi Slider <span class="text-danger">*</span></label>
                                <textarea id="title" name="title" class="form-control summernote-title" style="height: 120px;" required></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small mb-1">Tautan <span class="text-danger">*</span></label>
                                <input type="url" id="link" name="link" class="form-control" required>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small mb-1">Gambar Slider <span class="text-danger" id="imageRequired">*</span></label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">Format: jpeg, png, jpg | Maks: 2MB | Rasio: 16:9</small>
                                
                                <div id="imagePreview" class="mt-3 text-center">
                                    <img src="" alt="Preview" class="img-thumbnail" style="width: 100%; max-height: 150px; object-fit: contain; display: none;">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small mb-1">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
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
    .badge-active {
        background-color: #28a745;
    }
    .badge-inactive {
        background-color: #dc3545;
    }
    .slider-title-preview {
        display: none;
        visibility: hidden;
    }
    .modal-md {
        max-width: 600px;
    }
    
    /* Summernote Custom Styles */
    .note-editor.note-frame {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.25rem !important;
        margin-bottom: 0;
    }
    .note-toolbar {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
        padding: 0.25rem 0.5rem !important;
    }
    .note-btn-group {
        margin: 0 2px !important;
    }
    .note-btn {
        padding: 0.25rem 0.5rem !important;
        background: white !important;
        border-color: #dee2e6 !important;
    }
    .note-editable {
        min-height: 100px !important;
        max-height: 150px;
        overflow-y: auto;
        padding: 0.5rem !important;
    }
    .note-dropdown-menu {
        min-width: 150px !important;
    }
    .note-color-palette {
        line-height: 1 !important;
    }
    .note-popover .popover-content {
        padding: 0.5rem !important;
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
    const baseComprofUrl = "{{ url('comprof') }}";

    // Enhanced Summernote initialization
    $('.summernote-title').summernote({
        height: 150,
        toolbar: [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'fontname', 'fontsize', 'color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview', 'help']]
        ],
        fontNames: [
            'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
            'Helvetica', 'Impact', 'Tahoma', 'Times New Roman',
            'Verdana', 'Roboto', 'Open Sans'
        ],
        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36'],
        styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        disableDragAndDrop: true,
        shortcuts: false,
        followingToolbar: false,
        callbacks: {
            onPaste: function(e) {
                // Clean paste to remove links
                var bufferText = ((e.originalEvent || e).clipboardData.getData('Text'));
                e.preventDefault();
                document.execCommand('insertText', false, bufferText);
            },
            onKeydown: function(e) {
                // Prevent direct link insertion
                if (e.key === 'Enter' && window.getSelection().toString().match(/https?:\/\//)) {
                    e.preventDefault();
                }
            }
        }
    });

    // Add this click handler for the slider rows
    $('#dataTable').on('click', 'tr[data-link]', function(e) {
        // Don't trigger if clicking on action buttons
        if (!$(e.target).closest('.edit-btn, .delete-btn').length && $(this).data('link')) {
            window.open($(this).data('link'), '_blank');
        }
    });

    // Inisialisasi DataTables
    $('#dataTable').DataTable();

    // Preview gambar saat dipilih
    $('#image').change(function() {
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

    // Tambah Slider
    $('#btnAddSlider').click(() => {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Slider');
        $('#modalSubmit').text('Simpan');
        form.attr('action', `${baseComprofUrl}/slider`);
        $('#formMethod').val('POST');
        $('.summernote-title').summernote('reset');
        $('#imagePreview img').hide().removeData('existing');
        $('#imageRequired').show();
        modalInstance.show();
    });

    // Edit Slider
    $('#dataTable').on('click', '.edit-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        form.attr('action', `${baseComprofUrl}/slider/${id}`);
        
        $('#id').val(id);
        $('#title').summernote('code', btn.data('title'));
        $('#link').val(btn.data('link'));
        $('#status').val(btn.data('status'));
        
        const preview = $('#imagePreview img');
        const imageUrl = btn.data('image_url');
        
        if (imageUrl) {
            preview.attr('src', imageUrl)
                   .show()
                   .data('existing', imageUrl);
        } else {
            preview.hide().removeData('existing');
        }
        
        $('#modalTitle').text('Edit Slider');
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
        
        const titleContent = $('#title').summernote('code');
        formData.set('title', titleContent);
        
        if (!isEdit && !formData.get('image')) {
            Swal.fire({
                icon: 'error',
                title: 'Gambar Slider Diperlukan',
                text: 'Silakan pilih gambar untuk slider baru',
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
                } else if (errors && errors.error) {
                    message = errors.error;
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

    // Hapus Slider
    $('#dataTable').on('click', '.delete-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        const title = btn.data('title');
        const deleteUrl = `{{ route('comprof.slider.destroy', ':id') }}`.replace(':id', id);
        const row = btn.closest('tr');

        Swal.fire({
            title: 'Hapus Slider?',
            html: `Yakin ingin menghapus slider <strong>${title}</strong>?`,
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
                    url: deleteUrl,
                    type: 'DELETE',
                    data: { 
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        row.fadeOut(400, function() {
                            row.remove();
                            $('#dataTable tbody tr').each(function(index) {
                                $(this).find('td:first').text(index + 1);
                            });
                        });
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500 
                        });
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan pada server';

                        if (xhr.status === 404) {
                            message = 'Data slider tidak ditemukan';
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