@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Data Posisi</h1>
    <p class="mb-4">Manajemen Data Posisi untuk aplikasi.</p>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Add New Divisi Button -->
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
        @endphp

        @can('tambah', $currentMenuSlug) 
        <button type="button" class="btn btn-primary" data-toggle="modal" id="addPosisiButton">
            <i class="fas fa-plus"></i> Tambah Posisi
        </button>
        @endcan
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Posisi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Kode Posisi</th>
                            <th width="20%">Nama Posisi</th>
                            <th width="15%">User ID</th>
                            <th width="15%">Last Update</th>
                            <th width="5%">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Posisis as $index => $Posisi)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $Posisi->Pos_Code }}</td>
                            <td>{{ $Posisi->Pos_Name }}</td>
                            <td>{{ $Posisi->Pos_UserID }}</td>
                            <td>{{ $Posisi->Pos_LastUpdate }}</td>
                            <td>
                                @can('ubah', $currentMenuSlug) 
                                <button type="button" class="btn btn-sm btn-warning edit-btn"
                                    data-posid="{{ $Posisi->pos_auto }}"
                                    data-poscode="{{ $Posisi->Pos_Code }}"
                                    data-posname="{{ $Posisi->Pos_Name }}"
                                    data-toggle="modal"
                                    data-target="#editPosModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan

                                @can('hapus', $currentMenuSlug) 
                                <button class="btn btn-sm btn-danger delete-btn"
                                    data-posid="{{ $Posisi->pos_auto }}"
                                    data-posname="{{ $Posisi->Pos_Name }}"
                                    data-toggle="modal" data-target="#deletePosisiModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($Posisis) > 0)
            <!-- Tampilkan tabel -->
            @else
                <div class="alert alert-info">Tidak ada Data Posisi tersedia.</div>
            @endif
        </div>
    </div>
</div>

<!-- Universal Code Modal (untuk Add dan Edit) -->
<div class="modal fade" id="posModal" tabindex="-1" role="dialog" aria-labelledby="posModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="posModalLabel">Data Posisi</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="posisiForm" method="POST" action=""> {{-- Action akan di-set oleh JS --}}
                @csrf
                <input type="hidden" name="_method" id="formMethod" value=""> {{-- Untuk method PUT/PATCH saat edit --}}
                <input type="hidden" name="pos_auto" id="Posisi_Id" value="">

                <div class="modal-body">
                    <div id="modal-alert" class="alert alert-danger" style="display: none;">
                         <ul id="modal-error-list"></ul>
                    </div>
                    <div class="form-group row">
                        <label for="modal_pos_auto" class="col-sm-3 col-form-label">ID Posisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_pos_auto" name="pos_auto">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_Pos_Code" class="col-sm-3 col-form-label">Kode Posisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_Pos_Code" name="Pos_Code">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_Pos_Name" class="col-sm-3 col-form-label">Nama Posisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_Pos_Name" name="Pos_Name">
                        </div>
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
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#dataTable').DataTable();
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
        // Modal & form references
        const posModal    = $('#posModal');
        const form        = $('#posisiForm');
        const formAction  = () => form.attr('action');
        const formMethod  = () => $('#formMethod').val() || 'POST';

        // Reset form
        function resetForm(){
            form.trigger('reset');
            $('#formMethod').val('');
            $('#Posisi_Id').val('');
            $('#modal_pos_auto').val('').prop('disabled', false).prop('readonly', false);
        }

        // Tambah
        $('#addPosisiButton').click(function(){
            resetForm();
            $('#posModalLabel').text('Tambah Posisi');
            form.attr('action','/presensi/posisi');
            $('#formMethod').val('POST');
            $('#modal_pos_auto').closest('.form-group').hide();
            $('#saveModalButton').text('Simpan');
            posModal.modal('show');
        });

        // Edit
        $(document).on('click','.edit-btn',function(){
            const id      = $(this).data('posid');
            const code    = $(this).data('poscode');
            const name    = $(this).data('posname');

            resetForm();
            $('#posModalLabel').text('Edit Posisi');
            form.attr('action', `/presensi/posisi/${id}`);
            $('#formMethod').val('PUT');
            $('#saveModalButton').text('Update');

            $('#modal_pos_auto').closest('.form-group').show();
            $('#modal_pos_auto').val(id).prop('disabled', true);


            $('#Posisi_Id').val(id);
            $('#modal_Pos_Code').val(code);
            $('#modal_Pos_Name').val(name);

            posModal.modal('show');
        });

        // Submit (Tambah/Edit)
        form.on('submit', function(e){
            e.preventDefault();
            const url    = formAction();
            const method = formMethod();

            $.ajax({
                url: url,
                type: method === 'POST' ? 'POST' : 'PUT',
                data: form.serialize(),
                dataType: 'json',
            })
            .done(res => {
                Swal.fire({ 
                    icon:'success', 
                    title:'Berhasil', 
                    text:res.message, 
                    timer:1500, 
                    showConfirmButton:false })
                .then(()=> location.reload());
            })
            .fail(xhr => {
                let html = '';
                // Jika ada validasi errors, bentuk jadi <li>…</li>
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                html = '<ul class="text-left" style="list-style-type: none; padding-left: 0;">';
                Object.values(errors).flat().forEach(msg => {
                    html += `<li> ${msg}</li>`;
                });
                html += '</ul>';
                } else {
                // fallback ke message
                html = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                }
                Swal.fire({
                icon: 'error',
                title: 'Gagal menyimpan',
                html: html,
                });
            });
        });
        // Delete
        $(document).on('click','.delete-btn',function(){
            const id       = $(this).data('posid');
            const itemName = $(this).data('posname');
            const deleteUrl = `/presensi/posisi/${id}`;

            Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus: <strong>${itemName}</strong>.<br><small>Tindakan ini tidak dapat dibatalkan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: deleteUrl,
                    type: 'POST', // Laravel hanya support POST + _method
                    data: {
                        _method: 'DELETE',
                        _token: csrfToken
                    },
                    success: function(response) {
                        Swal.fire(
                            'Terhapus!',
                            response.message || 'Data berhasil dihapus.',
                            'success'
                        ).then(() => {
                            // reload untuk memperbarui tabel
                            location.reload();
                        });
                    },
                    error: function(jqXHR) {
                        let errorMsg = 'Gagal menghapus data.';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMsg = jqXHR.responseJSON.message;
                        }
                        Swal.fire(
                            'Gagal!',
                            errorMsg,
                            'error'
                        );
                    }
                });
            });   
        });
    });
</script>
@endpush