@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Kode Akuntansi</h1>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif


    <!-- Add New Code Button -->
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
        @endphp
        @can('tambah', $currentMenuSlug)
        <button type="button" class="btn btn-primary" data-toggle="modal" id="addCodeButton">
            <i class="fas fa-plus"></i> Tambah Kode
        </button>
        @endcan
    </div>

    <!-- Accounting Code Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kode Akuntansi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Akun</th>
                            <th width="20%">Perkiraan</th>
                            <th width="15%">Klasifikasi</th>
                            <th width="15%">Sub Klasifikasi</th>
                            <th width="10%">Cash / Bank</th>
                            <th width="5%">D / K</th>
                            <th width="10%">Pengguna</th>
                            <th width="10%">Tanggal</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($kodes as $index => $kode)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $kode->cls_kiraid }}</td>
                        <td>{{ $kode->cls_ina }}</td>
                        <td>{{ $kode->accClass->cls_ina }}</td>
                        <td>{{ $kode->accSubclass->cls_ina }}</td>
                        <td>{{ $kode->status }}</td>
                        <td>{{ $kode->d_k }}</td>
                        <td>{{ Auth::user()->name ?? 'System' }}</td>
                        <td>{{ \Carbon\Carbon::parse($kode->tanggal)->format('d M Y') }}</td>
                        <td>
                        <div class="d-flex gap-2">
                            @can('ubah', $currentMenuSlug)
                            <button class="btn btn-sm btn-warning edit-btn"
                                data-id="{{ $kode->id }}"
                                data-kiraid="{{ $kode->cls_kiraid }}"
                                data-ina="{{ $kode->cls_ina }}"
                                data-status="{{ $kode->status }}"
                                data-dk="{{ $kode->d_k }}"
                                data-clsid="{{ $kode->accClass->cls_id ?? '' }}"
                                data-clssubid="{{ $kode->accSubclass->cls_subid ?? '' }}"
                                data-tanggal="{{ $kode->tanggal }}"
                                data-toggle="modal" data-target="#editCodeModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endcan
                            @can('hapus', $currentMenuSlug)
                            <button class="btn btn-sm btn-danger delete-btn"
                                data-id="{{ $kode->id }}"
                                data-kiraid="{{ $kode->cls_kiraid }}"
                                data-toggle="modal" data-target="#deleteCodeModal">
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
            @if(count($kodes) > 0)
            <!-- Tampilkan tabel -->
            @else
                <div class="alert alert-info">Tidak ada kode akuntansi tersedia.</div>
            @endif
        </div>
    </div>
</div>

<!-- Universal Code Modal (untuk Add dan Edit) -->
<div class="modal fade" id="codeModal" tabindex="-1" role="dialog" aria-labelledby="codeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="codeModalLabel">Judul Modal Dinamis</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="codeForm" method="POST" action=""> {{-- Action akan di-set oleh JS --}}
                @csrf
                <input type="hidden" name="_method" id="formMethod"> {{-- Untuk method PUT/PATCH saat edit --}}
                <input type="hidden" name="kode_id" id="kode_id"> {{-- Untuk menyimpan ID saat edit (opsional, bisa juga dari URL) --}}

                <div class="modal-body">
                    <div class="form-group row">
                        <label for="modal_cls_id" class="col-sm-3 col-form-label">Klasifikasi</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="modal_cls_id" name="cls_id" required>
                                <option value="">-- Pilih Klasifikasi --</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->cls_id }}">{{ $class->cls_ina }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_cls_subid" class="col-sm-3 col-form-label">Sub Klasifikasi</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="modal_cls_subid" name="cls_subid" required>
                                <option value="">-- Pilih Sub Klasifikasi --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_cls_ina" class="col-sm-3 col-form-label">Perkiraan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="modal_cls_ina" name="cls_ina" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_status" class="col-sm-3 col-form-label">Status</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="modal_status" name="status" required>
                                <option value="umum">Umum</option>
                                <option value="cash/bank">Cash/Bank</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="modal_d_k" class="col-sm-3 col-form-label">Debet/Kredit</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="modal_d_k" name="d_k" required>
                                <option value="debet">Debet</option>
                                <option value="kredit">Kredit</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tambahkan input field lain jika ada yang spesifik untuk edit, misal cls_kiraid (biasanya readonly) --}}
                    <div class="form-group row" id="kiraid_group_edit" style="display: none;"> {{-- Hanya tampil saat edit --}}
                        <label for="modal_cls_kiraid" class="col-sm-3 col-form-label">Kode Akun</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="modal_cls_kiraid" name="cls_kiraid_display" readonly> {{-- Hanya display, tidak di-submit dengan nama ini --}}
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

    // --- Fungsi Helper untuk memuat Sub Klasifikasi ---
    function loadSubclasses(classId, subClassSelectId, subClassToSelectId = null) {
    console.log("loadSubclasses dipanggil dengan:", classId, subClassSelectId, subClassToSelectId); // Debug

    const $subClassSelect = $(subClassSelectId);
    $subClassSelect.empty().append('<option value="">-- Pilih Sub Klasifikasi --</option>');

    if (classId) {
        $.ajax({
            url: '{{ url("akunting/kodeakunting/get-subclasses") }}/' + classId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log("AJAX success! Data diterima:", data); // Debug
                if (data && data.length > 0) { // Pastikan data tidak null/undefined dan ada isinya
                    $.each(data, function(key, value) {
                        // Pastikan value.cls_subid dan value.cls_ina ada
                        if (value.hasOwnProperty('cls_subid') && value.hasOwnProperty('cls_ina')) {
                            $subClassSelect.append('<option value="' + value.cls_subid + '">' + value.cls_subid + ' - ' + value.cls_ina + '</option>');
                        } else {
                            console.error("Data subklasifikasi tidak memiliki properti cls_subid atau cls_ina:", value);
                        }
                    });
                    if (subClassToSelectId) {
                        console.log("Mencoba memilih subClassToSelectId:", subClassToSelectId); // Debug
                        $subClassSelect.val(subClassToSelectId);
                    }
                } else {
                    console.log("Tidak ada data subklasifikasi yang diterima atau data kosong.");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) { // Tambahkan parameter untuk detail error
                console.error('Gagal memuat sub klasifikasi.');
                console.error("Status AJAX Error:", textStatus, "Error:", errorThrown); // Debug
                console.error("Response Teks:", jqXHR.responseText); // Ini SANGAT PENTING untuk melihat error dari server
            }
        });
    } else {
        console.log("classId kosong, tidak melakukan AJAX request."); // Debug
    }
}

    function getTodayDateString() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // Bulan dimulai dari 0
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    // --- Event Handler untuk Tombol TAMBAH ---
    $('#addCodeButton').on('click', function() { // Asumsikan tombol tambah punya ID 'addCodeButton'
        $('#codeModalLabel').text('Tambah Kode Akuntansi'); // Set judul modal
        $('#codeForm').attr('action', '{{ route("kodeakunting.store") }}'); // Set action ke rute store
        $('#formMethod').val('POST'); // Method POST untuk store
        $('#codeForm')[0].reset(); // Reset form
        $('#modal_cls_subid').empty().append('<option value="">-- Pilih Sub Klasifikasi --</option>'); // Reset subklasifikasi
        $('#kode_id').val(''); // Kosongkan ID
        $('#kiraid_group_edit').hide(); // Sembunyikan field Kode Akun
        $('#saveModalButton').text('Simpan'); // Teks tombol
        $('#modal_tanggal').val(getTodayDateString());


        $('#codeModal').modal('show'); // Tampilkan modal
    });

    // --- Event Handler untuk Tombol EDIT (Delegated) ---
    $('#dataTable tbody').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var kiraid = $(this).data('kiraid');
        var ina = $(this).data('ina');
        var status = $(this).data('status');
        var dk = $(this).data('dk');
        var clsid = $(this).data('clsid');
        var clssubid = $(this).data('clssubid');
        var tanggal = $(this).data('tanggal');

        $('#codeModalLabel').text('Edit Kode Akuntansi'); // Set judul modal
        var updateUrl = '{{ route("kodeakunting.update", ":id") }}'.replace(':id', id);
        $('#codeForm').attr('action', updateUrl); // Set action ke rute update
        $('#formMethod').val('PUT'); // Atau 'PATCH', sesuaikan dengan rute Anda
        $('#codeForm')[0].reset(); // Reset form dulu
        $('#kode_id').val(id); // Set ID

        if (tanggal) {
            $('#modal_tanggal').val(tanggal.split(' ')[0]); // Ambil YYYY-MM-DD
        } else {
            $('#modal_tanggal').val(getTodayDateString()); // Jika tanggal dari data kosong, gunakan hari ini
        }
        // Isi form dengan data
        $('#modal_cls_kiraid').val(kiraid); // Untuk display
        $('#kiraid_group_edit').show();     // Tampilkan field Kode Akun

        $('#modal_cls_ina').val(ina);
        $('#modal_status').val(status);
        $('#modal_d_k').val(dk);
        if (tanggal) {
            $('#modal_tanggal').val(tanggal.split(' ')[0]);
        } else {
            $('#modal_tanggal').val('');
        }

        // Load dan pilih Klasifikasi & Sub Klasifikasi
        if (clsid !== '' && clsid !== null && typeof clsid !== 'undefined') {
            $('#modal_cls_id').val(clsid);
            loadSubclasses(clsid, '#modal_cls_subid', clssubid);
        } else {
            $('#modal_cls_id').val('');
            $('#modal_cls_subid').empty().append('<option value="">-- Pilih Sub Klasifikasi --</option>');
        }
        $('#saveModalButton').text('Perbarui'); // Teks tombol

        $('#codeModal').modal('show'); // Tampilkan modal
    });

    // Handle class change untuk MODAL (sekarang hanya satu)
    $('#modal_cls_id').change(function() {
        var classId = $(this).val();
        // Saat klasifikasi diubah di modal (baik tambah maupun edit),
        // kita tidak otomatis memilih sub-klasifikasi tertentu untuk 'edit' jika baru diubah,
        // hanya memuat ulang opsinya. Pengguna harus memilih lagi.
        // Untuk 'tambah', ini adalah perilaku yang diinginkan.
        loadSubclasses(classId, '#modal_cls_subid');
    });

    // ... (fungsi loadSubclasses Anda tidak perlu banyak berubah,
    //      pastikan selector targetnya '#modal_cls_subid') ...
});
    // Handle delete button click
$('#dataTable tbody').on('click', '.delete-btn', function(event) {
    event.preventDefault();

    const $button = $(this);
    const id = $button.data('id');
    const itemName = $button.data('kiraid') || 'item ini';
    const deleteUrl = '{{ route("kodeakunting.destroy", ":id") }}'.replace(':id', id);
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!id) {
        console.error("Tombol delete tidak memiliki data-id.");
        Swal.fire('Error!', 'Tidak dapat menemukan ID item untuk dihapus.', 'error');
        return;
    }

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
        if (result.isConfirmed) {
            $.ajax({
                url: deleteUrl,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: csrfToken
                },
                success: function(response) {
                    Swal.fire(
                        'Terhapus!',
                        response.message || 'Data berhasil dihapus.',
                        'success'
                    );
                    location.reload();
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
        }
    });
});


</script>
@endpush
