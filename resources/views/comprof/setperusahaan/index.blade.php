@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Setting Perusahaan</h1>

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

    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="setPerusahaanForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $setting->id ?? '' }}">

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-1">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" 
                                   value="{{ $setting->company_name ?? '' }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small mb-1">Alamat <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control summernote-address" 
                                      style="height: 150px;" required>{{ $setting->address ?? '' }}</textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" 
                                           value="{{ $setting->phone ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">WhatsApp</label>
                                    <input type="text" name="whatsapp" class="form-control" 
                                           value="{{ $setting->whatsapp ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" 
                                           value="{{ $setting->email ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Website</label>
                                    <input type="url" name="website" class="form-control" 
                                           value="{{ $setting->website ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small mb-1">Tagline</label>
                            <input type="text" name="tagline" class="form-control" 
                                   value="{{ $setting->tagline ?? '' }}">
                        </div>

                        <div class="form-group mb-3">
                            <label class="small mb-1">Peta Lokasi (Link Google Maps)</label>
                            <input type="url" name="map_location" class="form-control" 
                                   value="{{ $setting->map_location ?? '' }}">
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Logo Perusahaan</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <small class="text-muted">Format: jpeg, png, jpg | Maks: 2MB | Rekomendasi: 200x100px</small>
                                    @if(isset($setting) && $setting->logo)
                                        <div class="mt-2">
                                            <img src="{{ $setting->logo_url }}" alt="Logo" class="company-logo img-thumbnail">
                                            @can('ubah', $currentMenuSlug)
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_logo" id="removeLogo">
                                                <label class="form-check-label small" for="removeLogo">
                                                    Hapus logo saat disimpan
                                                </label>
                                            </div>
                                            @endcan
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Icon (Favicon)</label>
                                    <input type="file" name="icon" class="form-control" accept="image/*,.ico">
                                    <small class="text-muted">Format: ico, png | Maks: 1MB | Ukuran: 32x32 atau 16x16 px</small>
                                    
                                    @if(isset($setting) && $setting->icon)
                                        <div class="mt-2">
                                            <img src="{{ $setting->icon_url }}" alt="Icon" class="img-thumbnail" style="max-height: 32px;">
                                            @can('ubah', $currentMenuSlug)
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_icon" id="removeIcon">
                                                <label class="form-check-label small" for="removeIcon">
                                                    Hapus icon saat disimpan
                                                </label>
                                            </div>
                                            @endcan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Settings Section -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h5 class="mb-3">Pengaturan Email</h5>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Akun Email</label>
                                    <input type="email" name="email_account" class="form-control" 
                                           value="{{ $setting->email_account ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Password Email</label>
                                    <input type="password" name="email_password" class="form-control" 
                                           value="{{ $setting->email_password ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Host Email</label>
                                    <input type="text" name="email_host" class="form-control" 
                                           value="{{ $setting->email_host ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small mb-1">Port SMTP</label>
                                    <input type="text" name="smtp_port" class="form-control" 
                                           value="{{ $setting->smtp_port ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @can('ubah', $currentMenuSlug)
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
                @endcan
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .company-logo {
        max-width: 200px;
        max-height: 100px;
        width: auto;
        height: auto;
        display: block;
        margin-bottom: 10px;
        object-fit: contain;
    }

    .img-thumbnail {
        padding: 4px;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

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
    .img-thumbnail {
        max-width: 100%;
        height: auto;
        border: 1px solid #dee2e6;
    }
    .card-body {
        padding: 1.5rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .small {
        font-size: 0.875rem;
    }
    .form-check {
        margin-top: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Summernote for address field with proper configuration
    $('.summernote-address').summernote({
        height: 150,
        toolbar: [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'fontname', 'fontsize', 'color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video']],
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
        followingToolbar: false,
        callbacks: {
            onInit: function() {
                // Ensure the editor is properly initialized
                console.log('Summernote initialized');
            },
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
            url: '{{ route("comprof.setperusahaan.upload-image") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                editor.summernote('insertImage', response.url);
            },
            error: function(xhr) {
                console.error('Image upload failed:', xhr.responseText);
                alert('Gagal mengunggah gambar. Silakan coba lagi.');
            }
        });
    }

    // Handle form submission
    $('#setPerusahaanForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Get Summernote content
        const addressContent = $('.summernote-address').summernote('code');
        formData.set('address', addressContent);
        
        // Add checkbox values
        formData.append('remove_logo', $('#removeLogo').is(':checked') ? 1 : 0);
        formData.append('remove_icon', $('#removeIcon').is(':checked') ? 1 : 0);
        
        $.ajax({
            url: '{{ route("comprof.setperusahaan.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#setPerusahaanForm button[type="submit"]').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
            },
            success: function(response) {
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
            },
            complete: function() {
                $('#setPerusahaanForm button[type="submit"]').prop('disabled', false)
                    .html('<i class="fas fa-save"></i> Simpan Perubahan');
            }
        });
    });
});
</script>
@endpush