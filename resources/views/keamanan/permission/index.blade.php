@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Manajemen Permission</h1>
    <p class="mb-4">Kelola hak akses untuk setiap role terhadap menu dan submenu aplikasi.</p>

    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-left-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-left-danger" role="alert">
            <ul class="pl-4 my-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Role</h6>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="role_select">Pilih Role:</label>
                <select id="role_select" class="form-control" onchange="window.location.href = '?role_id=' + this.value">
                    <option value="">-- Pilih Role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ ($selectedRole && $selectedRole->id == $role->id) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($selectedRole)
            <hr>
            <h6 class="m-0 font-weight-bold text-primary mb-3">Daftar Permission untuk Role: {{ $selectedRole->name }}</h6>
            <form id="permissionForm" action="{{ route('keamanan.permission.updateMenuAccess') }}" method="POST">
                @csrf
                <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

                <div class="table-responsive">
                    <table class="table table-bordered" id="permissionTable" width="100%" cellspacing="0"> 
                        <thead class="thead-light">
                            <tr>
                                <th>Menu</th>
                                <th class="text-center">Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($menus->whereNull('parent_id')->sortBy('order') as $mainMenu)
                                <tr>
                                    <td><strong>{{ $mainMenu->name }}</strong></td>
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               name="selected_menus[]" 
                                               value="{{ $mainMenu->id }}" 
                                               class="form-check-input permission-checkbox"
                                               {{ $currentPermissions->contains($mainMenu->id) ? 'checked' : '' }}>
                                    </td>
                                </tr>
                                @php
                                    $submenus = $menus->where('parent_id', $mainMenu->id)->sortBy('order');
                                @endphp

                                @foreach ($submenus as $subMenuItem)
                                    <tr>
                                        <td style="padding-left: 30px;">↳ {{ $subMenuItem->name }}</td>
                                        <td class="text-center">
                                            <input type="checkbox" 
                                                   name="selected_menus[]" 
                                                   value="{{ $subMenuItem->id }}" 
                                                   class="form-check-input permission-checkbox"
                                                   {{ $currentPermissions->contains($subMenuItem->id) ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @can('ubah', 'keamanan.permission')
                <button type="submit" id="savePermissionsButton" class="btn btn-primary mt-3">
                    <i class="fas fa-save"></i> Simpan Akses Menu
                </button>
                @endcan
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>

<script>
    $(document).ready(function() {
        function markCheckedRows() {
            $('#permissionTable tbody tr').removeClass('active-permission');
            $('#permissionTable .permission-checkbox').each(function() {
                if ($(this).is(':checked')) {
                    $(this).closest('tr').addClass('active-permission');
                }
            });
        }

        markCheckedRows();
        $('#permissionTable').on('change', '.permission-checkbox', function() {
            markCheckedRows();
        });
        $('#permissionForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();
            
            const submitBtn = $('#savePermissionsButton');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    showAlert('success', 'Berhasil!', response.message || 'Permission berhasil diperbarui.', true); // Tambahkan true untuk reload
                },
                error: function(xhr) {
                    handleAjaxError(xhr);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        function showAlert(icon, title, text, reload = false) {
            Swal.fire({
                icon: icon,
                title: title,
                html: text, 
                timer: 2000, 
                showConfirmButton: false, 
                didClose: () => { 
                    if (reload) location.reload(); 
                }
            });
        }

        function handleAjaxError(xhr) {
            let message = 'Terjadi kesalahan. Silakan coba lagi.';
            
            if (xhr.status === 422) { 
                const errors = xhr.responseJSON.errors || {};
                message = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
                message = `<ul>${message}</ul>`;
                showAlert('error', 'Validasi Gagal', message);
            } 
            else if (xhr.status === 409) {
                message = xhr.responseJSON.message || 'Tidak ada perubahan data.';
                showAlert('info', 'Informasi', message);
            }
            else { 
                message = xhr.responseJSON?.message || message;
                showAlert('error', 'Error', message);
            }
        }

        $('#dataTable').DataTable();
    });
</script>
@endpush


@push('css')
<style>
    .table .active-permission {
        background-color: #fff3cd !important;
    }

    .table .active-permission:hover {
        background-color: #ffeeb2 !important;
    }
    .table .active-permission td {
        /* color: #856404; */
        /* border-color: #ffeeba; */
    }
</style>
@endpush