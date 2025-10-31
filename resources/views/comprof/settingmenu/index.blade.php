@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Setting Menu</h1>

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
        <button type="button" class="btn btn-primary" id="btnAddMenu" data-toggle="modal">
            <i class="fas fa-plus"></i> Tambah Menu
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
                            <th>Nama Menu</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setmenus as $index => $menu)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $menu->nama_menu }}</td>
                                <td>{{ $menu->urutan }}</td>
                                <td>{!! $menu->status_html !!}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('ubah', $currentMenuSlug)
                                        <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="{{ $menu->id }}"
                                            data-nama="{{ $menu->nama_menu }}"
                                            data-urutan="{{ $menu->urutan }}"
                                            data-status="{{ $menu->status }}"
                                            title="Edit Menu">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcan

                                        @can('hapus', $currentMenuSlug)
                                        <button class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $menu->id }}"
                                            data-nama="{{ $menu->nama_menu }}"
                                            title="Hapus Menu">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data menu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Menu -->
    <div class="modal fade" id="universalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <form id="mainForm" method="POST" class="modal-content">
                @csrf
                @method('POST')
                <input type="hidden" id="id" name="id">

                <div class="modal-header py-2">
                    <h5 class="modal-title" id="modalTitle">Tambah Menu</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-2">
                    <div class="form-group mb-2">
                        <label class="small mb-0">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" id="nama_menu" name="nama_menu" class="form-control form-control-sm" required>
                    </div>

                    <div class="form-group mb-2">
                        <label class="small mb-0">Urutan <span class="text-danger">*</span></label>
                        <input type="number" id="urutan" name="urutan" class="form-control form-control-sm" required min="0">
                    </div>

                    <div class="form-group mb-2">
                        <label class="small mb-0">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-control form-control-sm" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
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

@push('scripts')
<script>
$(function () {
    const modalEl = document.getElementById('universalModal');
    const modalInstance = new bootstrap.Modal(modalEl);
    const form = $('#mainForm');
    const baseUrl = "{{ url('comprof') }}";

    $('#dataTable').DataTable();

    // Tambah
    $('#btnAddMenu').on('click', function () {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Menu');
        $('#modalSubmit').text('Simpan');
        form.attr('action', `${baseUrl}/settingmenu`);
        form.find('input[name="_method"]').val('POST');
        modalInstance.show();
    });

    // Edit
    $('#dataTable').on('click', '.edit-btn', function () {
        const btn = $(this);
        const id = btn.data('id');

        $('#id').val(id);
        $('#nama_menu').val(btn.data('nama'));
        $('#urutan').val(btn.data('urutan'));
        $('#status').val(btn.data('status'));

        form.attr('action', `${baseUrl}/settingmenu/${id}`);
        form.find('input[name="_method"]').val('PUT');
        $('#modalTitle').text('Edit Menu');
        $('#modalSubmit').text('Simpan Perubahan');
        modalInstance.show();
    });

    // Submit Form
    form.on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: form.attr('action'),
            method: form.find('input[name="_method"]').val(),
            data: form.serialize(),
            success: function (response) {
                modalInstance.hide();
                Swal.fire('Berhasil', response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function (xhr) {
                let message = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire('Error', message, 'error');
            }
        });
    });

    // Hapus
    $('#dataTable').on('click', '.delete-btn', function () {
        const btn = $(this);
        const id = btn.data('id');
        const nama = btn.data('nama');
        const deleteUrl = `{{ route('comprof.settingmenu.destroy', ':id') }}`.replace(':id', id);
        const row = btn.closest('tr');

        Swal.fire({
            title: 'Hapus Menu?',
            html: `Yakin ingin menghapus menu <strong>${nama}</strong>?`,
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
                    url: '/comprof/settingmenu/${id}',
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        row.fadeOut(400, () => row.remove());
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            html: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        let message = 'Terjadi kesalahan pada server';
                        if (xhr.status === 404) {
                            message = 'Data menu tidak ditemukan';
                        } else if (xhr.responseJSON?.message) {
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
