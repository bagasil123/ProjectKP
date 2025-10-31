@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Data Divisi</h1>
    <p class="mb-4">Manajemen Data Divisi untuk aplikasi.</p>
    
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
        <button type="button" class="btn btn-primary" data-toggle="modal" id="addDivisiButton">
            <i class="fas fa-plus"></i> Tambah Divisi
        </button>
        @endcan
    </div>

    <!-- Divisi Card -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Divisi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="2%">Kode Divisi</th>
                            <th width="12%">Nama Divisi</th>
                            <th width="12%">NIK</th>
                            <th width="5%">Shift (Y/N)</th>
                            <th width="5%">Biaya</th>
                            <th width="5%">Entry ID</th>
                            <th width="12%">Entry Date</th>
                            <th width="5%">User ID</th>
                            <th width="12%">Last Update</th>
                            <th width="15%">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Divisis as $index => $Divisi)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $Divisi->Div_Code }}</td>
                            <td>{{ $Divisi->Div_Name }}</td>
                            <td>{{ $Divisi->DIV_NIK }}</td>
                            <td>{{ $Divisi->DIV_SHIFTYN }}</td>
                            <td>{{ $Divisi->DIV_BIAYA }}</td>
                            <td>{{ $Divisi->Div_EntryID }}</td>
                            <td>{{ \Carbon\Carbon::parse($Divisi->Div_Entrydate)->format('d-m-Y H:i') }}</td>
                            <td>{{ $Divisi->Div_UserID }}</td>
                            <td>{{ $Divisi->Div_LastUpdate }}</td>
                            <td>
                                <button class="btn btn-info btn-sm btn-view" data-id="{{ $Divisi->div_auto }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @can('ubah', $currentMenuSlug) 
                                <button type="button" class="btn btn-sm btn-warning edit-btn"
                                    data-divid="{{ $Divisi->div_auto }}"
                                    data-divcode="{{ $Divisi->Div_Code }}"
                                    data-divname="{{ $Divisi->Div_Name }}"
                                    data-divnik="{{ $Divisi->DIV_NIK }}"
                                    data-divshift="{{ $Divisi->DIV_SHIFTYN }}"
                                    data-divbiaya="{{ $Divisi->DIV_BIAYA }}"
                                    data-toggle="modal"
                                    data-target="#editDivModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan

                                @can('hapus', $currentMenuSlug)
                                <button class="btn btn-sm btn-danger delete-btn"
                                    data-divid="{{ $Divisi->div_auto }}"
                                    data-divname="{{ $Divisi->Div_Name }}"
                                    data-toggle="modal" data-target="#deleteDivisiModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($Divisis) > 0)
            <!-- Tampilkan tabel -->
            @else
                <div class="alert alert-info">Tidak ada Data Divisi tersedia.</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal View Divisi Detail -->
<div class="modal fade" id="viewDivisiModal" tabindex="-1" aria-labelledby="viewDivisiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDivisiModalLabel">Data Divisi</h5>
            </div>
            <div class="modal-body">
                {{-- Informasi Divisi --}}
                <div class="row mb-2">
                    <div class="col-md-3"><strong>ID Divisi:</strong> <span id="viewDivAuto"></span></div>
                    <div class="col-md-3"><strong>Kode Divisi:</strong> <span id="viewDivCode"></span></div>
                    <div class="col-md-3"><strong>Nama Divisi:</strong> <span id="viewDivName"></span></div>
                    <div class="col-md-3"><strong>NIK:</strong> <span id="viewDivNIK"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Shift (Y/N):</strong> <span id="viewDivShift"></span></div>
                    <div class="col-md-3"><strong>Biaya:</strong> <span id="viewDivBiaya"></span></div>
                </div>
                <hr>
                {{-- Tabel Sub-Divisi --}}
                <h5 class="mb-3">Data Sub-Divisi</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="viewSubDivTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 20%;">Kode Sub-Divisi</th>
                                <th style="width: 20%;">Nama Sub-Divisi</th>
                                <th style="width: 25%;">NIK</th>
                            </tr>
                        </thead>
                        <tbody id="viewSubDivTableBody">
                            {{-- Baris detail akan diisi oleh JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 20%;">Kode Sub-Divisi</th>
                                <th style="width: 20%;">Nama Sub-Divisi</th>
                                <th style="width: 25%;">NIK</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<!-- Universal Code Modal (untuk Add dan Edit) -->
<div class="modal fade" id="divModal" tabindex="-1" role="dialog" aria-labelledby="divModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="divModalLabel">Data Divisi</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="divisiForm" method="POST" action=""> {{-- Action akan di-set oleh JS --}}
                @csrf
                <input type="hidden" name="_method" id="formMethod" value=""> {{-- Untuk method PUT/PATCH saat edit --}}
                <input type="hidden" name="div_auto" id="Divisi_Id" value="">

                <div class="modal-body">
                    <div id="modal-alert" class="alert alert-danger" style="display: none;">
                         <ul id="modal-error-list"></ul>
                    </div>
                    <div class="form-group row">
                        <label for="modal_div_auto" class="col-sm-3 col-form-label">ID Divisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_div_auto" name="div_auto">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_Div_Code" class="col-sm-3 col-form-label">Kode Divisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control bg-light small" id="modal_Div_Code" name="Div_Code">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_Div_Name" class="col-sm-3 col-form-label">Nama Divisi</label>
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
                    <div class="form-group row">
                        <label for="modal_DIV_SHIFTYN" class="col-sm-3 col-form-label">Shift (Y/T)</label>
                        <div class="col-sm-9">
                            <select class="form-control bg-light small" id="modal_DIV_SHIFTYN" name="DIV_SHIFTYN">
                                <option selected value="T">Tidak</option>
                                <option value="Y">Ya</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_DIV_BIAYA" class="col-sm-3 col-form-label">Biaya</label>
                        <div class="col-sm-9">
                            <select class="form-control bg-light small" id="modal_DIV_BIAYA" name="DIV_BIAYA">
                                <option selected value="">Pilih</option>
                                <option value="T">Tidak</option>
                                <option value="Y">Ya</option>
                            </select>
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
        // DataTable
        var table = $('#dataTable').DataTable();
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
        // Modal & form references
        const viewModal = new bootstrap.Modal($('#viewDivisiModal'));
        const divModal    = $('#divModal');
        const form        = $('#divisiForm');
        const formAction  = () => form.attr('action');
        const formMethod  = () => $('#formMethod').val() || 'POST';

        // Reset form state
        function resetForm() {
            form.trigger('reset');
            $('#formMethod').val('');
            $('#Divisi_Id').val('');
            $('#modal_div_auto').val('').prop('disabled', false).prop('readonly', false);
        }


        // tombol “View” klik
        $(document).on('click', '.btn-view', function() {
            const id = $(this).data('id');

            $.ajax({
                url: `/presensi/divisi/${id}`,
                method: 'GET',
                dataType: 'json',
            })
            .done(divisi => {
                // isi field dasar
                $('#viewDivAuto').text(divisi.div_auto);
                $('#viewDivCode').text(divisi.Div_Code);
                $('#viewDivName').text(divisi.Div_Name);
                $('#viewDivNIK').text(divisi.DIV_NIK || '-');
                $('#viewDivShift').text(divisi.DIV_SHIFTYN);
                $('#viewDivBiaya').text(divisi.DIV_BIAYA || '-');

                // kosongkan dulu tabel Sub-Divisi
                const $tbody = $('#viewSubDivTableBody').empty();

                // ambil relasi SubDivisi
                const subs = divisi.sub_divisi || [];

                if (subs.length) {
                    subs.forEach((sub, idx) => {
                        const row = `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td>${sub.Div_Code || '-'}</td>
                                <td>${sub.Div_Name || '-'}</td>
                                <td>${sub.DIV_NIK || '-'}</td>
                            </tr>
                        `;
                        $tbody.append(row);
                    });
                } else {
                    $tbody.append(`
                        <tr>
                            <td colspan="4" class="text-center">– Belum ada Sub-Divisi –</td>
                        </tr>
                    `);
                }

                // tampilkan modal
                viewModal.show();
            })
            .fail(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Tidak dapat memuat data divisi.',
                });
            });
        });

        // Tambah
        $('#addDivisiButton').click(function(){
            resetForm();
            $('#divModalLabel').text('Tambah Divisi');
            form.attr('action','/presensi/divisi');
            $('#formMethod').val('POST');
            $('#modal_div_auto').closest('.form-group').hide();
            $('#saveModalButton').text('Simpan');
            
            divModal.modal('show');
        });

        // Edit
        $(document).on('click','.edit-btn',function(){
            const id      = $(this).data('divid');
            const code    = $(this).data('divcode');
            const name    = $(this).data('divname');
            const nik     = $(this).data('divnik');
            const shift   = $(this).data('divshift');
            const biaya   = $(this).data('divbiaya');

            resetForm();
            $('#divModalLabel').text('Edit Divisi');
            form.attr('action', `/presensi/divisi/${id}`);
            $('#formMethod').val('PUT');
            $('#saveModalButton').text('Update');

            $('#modal_div_auto').closest('.form-group').show();
            $('#modal_div_auto').val(id).prop('disabled', true);

            $('#Divisi_Id').val(id);
            $('#modal_Div_Code').val(code);
            $('#modal_Div_Name').val(name);
            $('#modal_DIV_NIK').val(nik);
            $('#modal_DIV_SHIFTYN').val(shift);
            $('#modal_DIV_BIAYA').val(biaya);
            
            divModal.modal('show');
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
            const id       = $(this).data('divid');
            const itemName = $(this).data('divname');
            const deleteUrl = `/presensi/divisi/${id}`;

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
