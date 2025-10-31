@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Role</h1>
    <p class="mb-4">Manajemen role untuk kontrol akses pengguna aplikasi.</p>

    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
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
        @can('tambah', $currentMenuSlug) {{-- Menggunakan slug dinamis --}}
        <button class="btn btn-primary" data-toggle="modal" id="addRoleButton">
            <i class="fas fa-plus"></i> Tambah Role
        </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Role</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Nama Role</th>
                            <th width="10%">Pengguna</th> {{-- Kolom ini --}}
                            <th width="25%">Tanggal Dibuat</th>
                            <th width="25%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $index => $role)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $role->name }}</td>
                                <td>
                                    {{-- Mengakses hitungan member melalui members_count --}}
                                    <span class="badge badge-info">{{ $role->members_count }}</span> 
                                </td>
                                <td>
                                    {{ $role->created_at->format('d M Y') }}<br>
                                </td>
                                <td>
                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}"
                                        data-toggle="modal"
                                        title="Edit Role">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}"
                                        data-toggle="modal"
                                        title="Hapus Role">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada role.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Role (Tidak ada perubahan di sini) -->
<div class="modal fade" id="codeModal" tabindex="-1" role="dialog" aria-labelledby="codeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="codeForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="role_id" id="kode_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="ModalLabel">Judul Modal Dinamis</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_name">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_name" name="name">
                        <small class="form-text text-muted">Contoh: admin, manager, staff, akunting</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveModalButton">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const storeUrl = "{{ route('keamanan.roles.store') }}";
    const updateUrlTpl = "{{ route('keamanan.roles.update', ':id') }}";
    const deleteUrlTpl = "{{ route('keamanan.roles.destroy', ':id') }}";
    const csrfToken = "{{ csrf_token() }}";

    // Inisialisasi DataTables
    $('#dataTable').DataTable();

    // Tampilkan modal tambah role
    $('#addRoleButton').on('click', function() {
        showRoleModal('Tambah Role Baru', storeUrl, 'POST', '', 'Simpan');
    });

    // Edit role
    $('#dataTable').on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        showRoleModal('Edit Role', updateUrlTpl.replace(':id', id), 'PUT', id, 'Perbarui', name);
    });

    // Fungsi untuk menampilkan modal role
    function showRoleModal(title, action, method, id = '', buttonText, name = '') {
        $('#ModalLabel').text(title); // Mengganti ID dari #codeModalLabel menjadi #ModalLabel
        $('#codeForm').attr('action', action);
        $('#formMethod').val(method);
        $('#kode_id').val(id);
        $('#modal_name').val(name);
        $('#saveModalButton').text(buttonText);
        $('#codeModal').modal('show');
    }

    // Handle form submission
    $('#codeForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const method = $('#formMethod').val();
        const formData = form.serialize();
        const isCreate = method === 'POST';
        
        // Tampilkan loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData + (method !== 'POST' ? '&_method=' + method : ''),
            success: function(response) {
                showAlert(
                    'success', 
                    isCreate ? 'Berhasil!' : 'Diperbarui!', 
                    response.message || (isCreate ? 'Role baru berhasil dibuat.' : 'Perubahan role berhasil disimpan.'),
                    true
                );
                $('#codeModal').modal('hide');
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Hapus role
    $('#dataTable').on('click', '.delete-btn', function(event) {
        event.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || 'Role ini';
        
        showConfirmDialog(
            'Apakah Anda yakin?',
            `Anda akan menghapus role: <strong>${name}</strong>.<br><small>Tindakan ini tidak dapat dibatalkan.</small>`,
            'warning',
            'Ya, hapus!',
            'Batal'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrlTpl.replace(':id', id),
                    type: 'POST', // Menggunakan POST untuk DELETE method override
                    data: {
                        _method: 'DELETE',
                        _token: csrfToken
                    },
                    success: function(response) {
                        showAlert('success', 'Terhapus!', response.message || 'Role berhasil dihapus.', true);
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr);
                    }
                });
            }
        });
    });

    // Fungsi untuk menampilkan alert (SweetAlert2)
    function showAlert(icon, title, text, reload = false) {
        Swal.fire({
            icon: icon,
            title: title,
            html: text,
            timer: 2000,
            showConfirmButton: false,
            willClose: () => {
                if (reload) location.reload();
            }
        });
    }

    // Fungsi untuk menampilkan dialog konfirmasi (SweetAlert2)
    function showConfirmDialog(title, html, icon, confirmText, cancelText) {
        return Swal.fire({
            title: title,
            html: html,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#3085d6', // Warna biru untuk konfirmasi
            cancelButtonColor: '#d33', // Warna merah untuk batal
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        });
    }

    // Fungsi untuk menangani error AJAX (Validasi, Konflik, Umum)
    function handleAjaxError(xhr) {
        let message = 'Terjadi kesalahan. Silakan coba lagi.';
        
        if (xhr.status === 422) { // Validasi gagal
            const errors = xhr.responseJSON.errors || {};
            message = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
            message = `<ul>${message}</ul>`;
            showAlert('error', 'Validasi Gagal', message);
        } 
        else if (xhr.status === 409) { // Konflik (misal duplikasi)
            message = xhr.responseJSON.message || 'Tidak ada perubahan data.';
            showAlert('info', 'Informasi', message);
        }
        else { // Error umum
            message = xhr.responseJSON?.message || message;
            showAlert('error', 'Error', message);
        }
    }
});
</script>
@endpush