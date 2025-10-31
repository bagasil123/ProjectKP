@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Data Sub-Divisi</h1>
    <p class="mb-4">Manajemen Data Sub-Divisi untuk aplikasi.</p>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Add New Sub-Divisi Button -->
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
        @endphp

        @can('tambah', $currentMenuSlug) 
        <button type="button" class="btn btn-primary" data-toggle="modal" id="addSubDivisiButton">
            <i class="fas fa-plus"></i> Tambah Sub-Divisi
        </button>
        @endcan
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Sub-Divisi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode Divisi</th>
                            <th width="10%">Kode Sub-Divisi</th>
                            <th width="20%">Nama Sub-Divisi</th>
                            <th width="15%">NIK</th>
                            <th width="5%">Entry ID</th>
                            <th width="12%">Entry Date</th>
                            <th width="5%">User ID</th>
                            <th width="12%">Last Update</th>
                            <th width="5%">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($SubDivisis as $index => $SubDivisi)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $SubDivisi->div_divcode }}</td>
                            <td>{{ $SubDivisi->Div_Code }}</td>
                            <td>{{ $SubDivisi->Div_Name }}</td>
                            <td>{{ $SubDivisi->DIV_NIK }}</td>
                            <td>{{ $SubDivisi->Div_EntryID }}</td>
                            <td>{{ $SubDivisi->Div_Entrydate }}</td>
                            <td>{{ $SubDivisi->Div_UserID }}</td>
                            <td>{{ $SubDivisi->Div_LastUpdate }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    @can('ubah', $currentMenuSlug) 
                                    <button type="button" class="btn btn-sm btn-warning edit-btn"
                                        data-subdivid="{{ $SubDivisi->div_auto }}"
                                        data-divid="{{ $SubDivisi->div_divcode }}"
                                        data-subdivcode="{{ $SubDivisi->Div_Code }}"
                                        data-subdivname="{{ $SubDivisi->Div_Name }}"
                                        data-subdivnik="{{ $SubDivisi->DIV_NIK }}"
                                        data-toggle="modal"
                                        data-target="#editSubDivModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    
                                    @can('hapus', $currentMenuSlug) 
                                    <button class="btn btn-sm btn-danger delete-btn"
                                        data-subdivid="{{ $SubDivisi->div_auto }}"
                                        data-subdivname="{{ $SubDivisi->Div_Name }}"
                                        data-toggle="modal" data-target="#deleteSubDivisiModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($SubDivisis) > 0)
            <!-- Tampilkan tabel -->
            @else
                <div class="alert alert-info">Tidak ada Data Sub-Divisi tersedia.</div>
            @endif
        </div>
    </div>
</div>

<!-- Universal Code Modal (untuk Add dan Edit) -->
<div class="modal fade" id="subdivModal" tabindex="-1" role="dialog" aria-labelledby="subdivModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subdivModalLabel">Data Sub-Divisi</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="subdivisiForm" method="POST" action=""> {{-- Action akan di-set oleh JS --}}
                @csrf
                <input type="hidden" name="_method" id="formMethod" value=""> {{-- Untuk method PUT/PATCH saat edit --}}
                <input type="hidden" name="div_auto" id="SubDivisi_Id" value="">

                <div class="modal-body">
                    <div id="modal-alert" class="alert alert-danger" style="display: none;">
                         <ul id="modal-error-list"></ul>
                    </div>
                    <div class="form-group row">
                        <label for="modal_div_auto" class="col-sm-3 col-form-label">ID Sub-Divisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_div_auto" name="div_auto">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_div_divcode" class="col-sm-3 col-form-label">Kode Divisi</label>
                        <div class="col-sm-9">
                            <select class="form-control bg-light small" id="modal_div_divcode" name="div_divcode">
                                <option selected value="">Pilih</option>
                                @foreach($Divisis as $Divisi)
                                <option value="{{ $Divisi->div_auto }}">
                                    {{ $Divisi->Div_Name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_Div_Code" class="col-sm-3 col-form-label">Kode Sub-Divisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_Div_Code" name="Div_Code">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_Div_Name" class="col-sm-3 col-form-label">Nama Sub-Divisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_Div_Name" name="Div_Name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_DIV_NIK" class="col-sm-3 col-form-label">NIK</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_DIV_NIK" name="DIV_NIK">
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
        const subdivModal = $('#subdivModal');
        const form        = $('#subdivisiForm');
        const formAction  = () => form.attr('action');
        const formMethod  = () => $('#formMethod').val() || 'POST';


        // Reset form
        function resetForm(){
            form.trigger('reset');
            $('#formMethod').val('');
            $('#SubDivisi_Id').val('');
            $('#modal_div_auto').val('').prop('disabled', false).prop('readonly', false);
        }

        // Tambah
        $('#addSubDivisiButton').click(function(){
            resetForm();
            $('#subdivModalLabel').text('Tambah Sub-Divisi');
            form.attr('action','/presensi/subdivisi');
            $('#formMethod').val('POST');
            $('#modal_div_auto').closest('.form-group').hide();
            $('#saveModalButton').text('Simpan');

            subdivModal.modal('show');
        });

        // Edit
        $(document).on('click','.edit-btn',function(){
            const id      = $(this).data('subdivid');
            const did     = $(this).data('divid');
            const code    = $(this).data('subdivcode');
            const name    = $(this).data('subdivname');
            const nik     = $(this).data('subdivnik');

            resetForm();
            $('#subdivModalLabel').text('Edit SubDivisi');
            form.attr('action', `/presensi/subdivisi/${id}`);
            $('#formMethod').val('PUT');
            $('#saveModalButton').text('Update');

            $('#modal_div_auto').closest('.form-group').show();
            $('#modal_div_auto').val(id).prop('disabled', true);


            $('#SubDivisi_Id').val(id);
            $('#modal_div_divcode').val(did);
            $('#modal_Div_Code').val(code);
            $('#modal_Div_Name').val(name);
            $('#modal_DIV_NIK').val(nik);

            subdivModal.modal('show');
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
                    html += `<li>${msg}</li>`;
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
            const id       = $(this).data('subdivid');
            const itemName = $(this).data('subdivname');
            const deleteUrl = `/presensi/subdivisi/${id}`;

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