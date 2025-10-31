@extends('layouts.admin')

{{-- Tambahkan CSS untuk Leaflet.js (Peta) --}}
@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
@endsection

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Data Realtime Presensi</h1>
    <p class="mb-4">Manajemen Data Presensi untuk aplikasi.</p>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
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

        @can('tambah', $currentMenuSlug)
            <button id="btn-tambah-absensi" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Presensi Manual
            </button>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Presensi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Karyawan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Catatan</th>
                            <th>Foto</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Absensis as $index => $absensi)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                {{-- Menampilkan nama dari relasi 'employee' --}}
                                <td>{{ $absensi->TS_NAME }}</td>
                                <td>{{ \Carbon\Carbon::parse($absensi->TS_TANGGAL)->format('d M Y') }}</td>
                                <td>
                                    @php
                                        // Default status adalah Hadir jika TS_STATUS kosong atau null
                                        $status = $absensi->TS_STATUS ?: 'HADIR';
                                        $badgeClass = '';

                                        // Menggunakan strtolower untuk membuat perbandingan case-insensitive
                                        switch (strtolower($status)) {
                                            case 'hadir':
                                                $badgeClass = 'badge-success';
                                                break;
                                            case 'alpa':
                                                $badgeClass = 'badge-danger';
                                                break;
                                            case 'izin':
                                            case 'sakit':
                                            case 'cuti':
                                                $badgeClass = 'badge-warning';
                                                break;
                                            default:
                                                // Badge default jika ada status lain yang tidak terduga
                                                $badgeClass = 'badge-secondary';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ strtoupper($status) }}</span>
                                </td>
                                <td>{{ $absensi->TS_JAMIN ? \Carbon\Carbon::parse($absensi->TS_JAMIN)->format('H:i') : '-' }}</td>
                                <td>{{ $absensi->TS_JAMOUT ? \Carbon\Carbon::parse($absensi->TS_JAMOUT)->format('H:i') : '-' }}</td>
                                <td>{{ $absensi->TS_NOTE ?? '-' }}</td>
                                <td>
                                    @if($absensi->TS_FOTO)
                                        <a href="{{ asset('storage/absensi_fotos/' . $absensi->TS_FOTO) }}" target="_blank">
                                            <img src="{{ asset('storage/absensi_fotos/' . $absensi->TS_FOTO) }}" alt="Foto Absen" width="100" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;"/>
                                        </a>
                                    @else
                                        Tidak Ada
                                    @endif
                                </td>
                                <td>
                                    @if($absensi->TS_LATITUDE && $absensi->TS_LONGITUDE)
                                        <a href="https://maps.google.com/?q={{ $absensi->TS_LATITUDE }},{{ $absensi->TS_LONGITUDE }}" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fas fa-map-marker-alt"></i> Peta
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-view viewAbsensi" data-id="{{ $absensi->TS_AUTO }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-warning btn-sm btn-edit editAbsensi" data-id="{{ $absensi->TS_AUTO }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan

                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-danger btn-sm deleteAbsensi" data-id="{{ $absensi->TS_AUTO }}" data-nama="{{ $absensi->TS_NAME }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Rekap Laporan Absensi</h6>
        </div>
        <div class="card-body">
            <form id="rekapForm">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="rekap_start_date">Tanggal Awal</label>
                        <input type="date" id="rekap_start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="rekap_end_date">Tanggal Akhir</label>
                        <input type="date" id="rekap_end_date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="rekap_divisi">Divisi</label>
                        <select id="rekap_divisi" name="divisi_id" class="form-control">
                            <option value="">Semua</option>
                            @foreach($divisi as $div)
                                <option value="{{ $div->div_auto }}">{{ $div->Div_Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="rekap_gender">Gender</label>
                        <select id="rekap_gender" name="gender" class="form-control">
                            <option value="">Semua</option>
                            <option value="M">Laki-laki</option>
                            <option value="F">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="rekap_status">Status</label>
                        <select id="rekap_status" name="status" class="form-control">
                            <option value="">Semua</option>
                            <option value="HADIR">Hadir</option>
                            <option value="SAKIT">Sakit</option>
                            <option value="IZIN">Izin</option>
                            <option value="CUTI">Cuti</option>
                            <option value="ALPA">Alpa</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12 text-right">
                        <button type="button" id="btn-tampilkan-rekap" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan Laporan</button>
                        <button type="submit" id="btn-cetak-pdf" class="btn btn-danger"><i class="fas fa-print"></i> Cetak PDF</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Container untuk menampilkan hasil rekap --}}
    <div class="card shadow mb-4" id="rekap-result-card" style="display: none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Hasil Laporan</h6>
        </div>
        <div class="card-body">
            <div id="rekap-loading" class="text-center my-5" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Memproses Laporan...</p>
            </div>
            <div id="rekap-container" class="table-responsive"></div>
        </div>
    </div>
</div>


<!-- Modal View Absensi -->
<div class="modal fade" id="viewAbsensiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAbsensiModalLabel">Detail Absensi</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr><th width="40%">Nama Karyawan</th><td id="view_nama"></td></tr>
                            <tr><th>Tanggal</th><td id="view_tanggal"></td></tr>
                            <tr><th>Status</th><td id="view_status"></td></tr>
                            <tr><th>Jam Masuk</th><td id="view_jam_in"></td></tr>
                            <tr><th>Jam Pulang</th><td id="view_jam_out"></td></tr>
                            <tr><th>Catatan</th><td id="view_note"></td></tr>
                            <tr><th>Lokasi</th><td id="view_lokasi"></td></tr>
                            <tr><th>Dokumen</th><td id="view_dokumen"></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-center">
                        <strong>Foto Bukti Kehadiran</strong>
                        <div id="view_foto_container" class="mt-2">
                            {{-- Gambar akan dimuat di sini oleh JS --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Absensi -->
<div class="modal fade" id="absensiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="absensiForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="absensiModalLabel">Form Absensi</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="absensi_id">
                    <input type="hidden" name="delete_foto" id="delete_foto_input" value="0">
                    
                    <div class="row">
                        <div class="col-md-7">
                             <div class="form-group">
                                <label for="TS_EMP">Karyawan</label>
                                <select name="TS_EMP" id="TS_EMP" class="form-control" required>
                                    <option value="">Pilih Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->emp_Auto }}">{{ $employee->emp_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="TS_STATUS">Status</label>
                                <select name="TS_STATUS" id="TS_STATUS" class="form-control">
                                    <option disabled value="">Pilih</option>
                                    <option value="HADIR">Hadir</option>
                                    <option value="IZIN">Izin</option>
                                    <option value="SAKIT">Sakit</option>
                                    <option value="ALPA">Alpa</option>
                                </select>
                            </div>
                             <div class="form-group">
                                <label for="TS_TANGGAL">Tanggal</label>
                                <input type="date" name="TS_TANGGAL" id="TS_TANGGAL" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-6 form-group"><label for="TS_JAMIN">Jam Masuk</label><input type="time" name="TS_JAMIN" id="TS_JAMIN" class="form-control"></div>
                                <div class="col-6 form-group"><label for="TS_JAMOUT">Jam Pulang</label><input type="time" name="TS_JAMOUT" id="TS_JAMOUT" class="form-control"></div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label class="form-label d-block text-center">Foto Bukti</label>
                                <button type="button" id="remove-image-btn" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" style="display: none; z-index: 10;">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="position-relative mx-auto" style="max-width: 225px;">
                                    <!-- Menggunakan Bootstrap 5 Ratio Helper untuk rasio 9:16 -->
                                    <div class="ratio ratio-9x16 border rounded bg-light">
                                        <img id="image-preview" src="#" alt="Pratinjau Foto" class="img-fluid d-none" style="object-fit: cover;"/>
                                    </div>
                                </div>
                                <input type="file" name="TS_FOTO" id="TS_FOTO" class="form-control mt-2" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="TS_FILE_PENDUKUNG">Dokumen Pendukung (Izin/Sakit)</label>
                        <input type="file" name="TS_FILE_PENDUKUNG" id="TS_FILE_PENDUKUNG" class="form-control-file">
                        <small id="current-file-link"></small>
                    </div>
                    <div class="form-group">
                        <label for="TS_NOTE">Catatan</label>
                        <textarea name="TS_NOTE" id="TS_NOTE" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL BARU: Untuk Peta -->
<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Lokasi</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="height: 400px;">
                <div id="mapid" class="w-100 h-100"></div>
            </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btn-simpan-lokasi" class="btn btn-primary">Simpan Lokasi</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('#dataTable').DataTable({
        language: {
            emptyTable: "Tidak ada data yang tersedia di dalam tabel",
            zeroRecords: "Tidak ditemukan data yang sesuai",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(disaring dari _MAX_ total entri)",
            lengthMenu: "Tampilkan _MENU_ entri",
            search: "Cari:",
            paginate: {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            },
        }
    });

    function checkSchedule() {
        const employeeId = $('#TS_EMP').val();
        const tanggal = $('#TS_TANGGAL').val();
        const scheduleInfo = $('#schedule-info');

        if (!employeeId || !tanggal) {
            scheduleInfo.hide();
            return;
        }

        scheduleInfo.show().html('<i class="fas fa-spinner fa-spin"></i> Memeriksa jadwal...');

        $.ajax({
            url: '{{ route("jadwal.check") }}', // Anda perlu membuat route ini
            type: 'POST',
            data: {
                employee_id: employeeId,
                tanggal: tanggal
            },
            success: function(response) {
                if (response.shift_code) {
                    let infoText = `Jadwal: <strong>${response.shift_code}</strong> (${response.jam_in} - ${response.jam_out})`;
                    scheduleInfo.removeClass('alert-danger').addClass('alert-info').html(infoText);
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON.message || 'Tidak ada jadwal kerja pada tanggal ini.';
                scheduleInfo.removeClass('alert-info').addClass('alert-danger').html(errorMsg);
            }
        });
    }


    // --- SCRIPT UNTUK PETA LOKASI ---
    let map;
    let marker;
    const defaultLat = -6.2088; // Jakarta
    const defaultLng = 106.8456;

    $('#btn-pilih-lokasi').click(function() {
        $('#mapModal').modal('show');
    });

    $('#mapModal').on('shown.bs.modal', function() {
        if (!map) {
            map = L.map('mapid').setView([defaultLat, defaultLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        }
        setTimeout(() => map.invalidateSize(), 10);
    });

    $('#btn-simpan-lokasi').click(function() {
        const latlng = marker.getLatLng();
        $('#TS_LATITUDE').val(latlng.lat.toFixed(8));
        $('#TS_LONGITUDE').val(latlng.lng.toFixed(8));
        $('#location_display').val(`Lat: ${latlng.lat.toFixed(5)}, Long: ${latlng.lng.toFixed(5)}`);
        $('#mapModal').modal('hide');
    });

    

    // Panggil fungsi checkSchedule saat Karyawan atau Tanggal diubah
    $('#TS_EMP, #TS_TANGGAL').on('change', checkSchedule);

    $('body').on('click', '.viewAbsensi', function() {
    var absensiId = $(this).data('id');
    
    $.get("{{ url('presensi/absensi') }}/" + absensiId, function (data) {
        $('#viewAbsensiModalLabel').text('Detail Absensi - ' + (data.employee ? data.employee.emp_Name : data.TS_NAME));
        
        // --- PERBAIKAN DIMULAI DI SINI ---

        // 1. Tentukan status dan warna default
        let status = data.TS_STATUS || 'HADIR'; // Jika status null, anggap 'HADIR'
        let badgeClass = '';
        
        // 2. Buat perbandingan case-insensitive
        let lowerCaseStatus = status.toLowerCase();

        // 3. Tentukan kelas badge berdasarkan status
        if (lowerCaseStatus === 'hadir') {
            badgeClass = 'badge-success'; // Hijau
        } else if (['izin', 'dispensasi', 'cuti', 'sakit'].includes(lowerCaseStatus)) {
            badgeClass = 'badge-warning'; // Kuning
        } else if (['alpa', 'tidak hadir'].includes(lowerCaseStatus)) {
            badgeClass = 'badge-danger';  // Merah
        } else {
            badgeClass = 'badge-secondary'; // Warna default jika ada status lain
        }

        // Mengisi data teks
        $('#view_nama').text(data.employee ? data.employee.emp_Name : data.TS_NAME);
        $('#view_tanggal').text(new Date(data.TS_TANGGAL).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
        // Terapkan status dan warna yang sudah ditentukan
        $('#view_status').html(`<span class="badge ${badgeClass}">${status.toUpperCase()}</span>`);
        $('#view_jam_in').text(data.TS_JAMIN ? data.TS_JAMIN.substring(0,5) : '-');
        $('#view_jam_out').text(data.TS_JAMOUT ? data.TS_JAMOUT.substring(0,5) : '-');
        $('#view_note').text(data.TS_NOTE || '-');

        // --- PERBAIKAN SELESAI ---

        // Mengisi Lokasi
        if (data.TS_LATITUDE && data.TS_LONGITUDE) {
            $('#view_lokasi').html(`<a href="https://maps.google.com/?q=${data.TS_LATITUDE},${data.TS_LONGITUDE}" target="_blank">Lihat di Peta</a>`);
        } else {
            $('#view_lokasi').text('-');
            }

            // Mengisi Dokumen Pendukung
            if (data.TS_FILE_PENDUKUNG) {
                $('#view_dokumen').html(`<a href="/storage/dokumen_izin/${data.TS_FILE_PENDUKUNG}" target="_blank">Unduh Dokumen</a>`);
            } else {
                $('#view_dokumen').text('Tidak ada');
            }

            // Mengisi Foto
            if (data.TS_FOTO) {
                $('#view_foto_container').html(`<a href="/storage/absensi_fotos/${data.TS_FOTO}" target="_blank"><img src="/storage/absensi_fotos/${data.TS_FOTO}" class="img-fluid rounded" style="max-height: 250px;"/></a>`);
            } else {
                $('#view_foto_container').html('<p class="text-muted">Tidak ada foto</p>');
            }

            $('#viewAbsensiModal').modal('show');
        });
    });

    // --- SCRIPT PREVIEW & HAPUS GAMBAR ---
    $('#TS_FOTO').change(function(){
        const file = this.files[0];
        if (file){
            let reader = new FileReader();
            reader.onload = function(event){
                $('#image-preview').attr('src', event.target.result).removeClass('d-none');
                $('#remove-image-btn').show();
            }
            reader.readAsDataURL(file);
        }
    });

    $('#remove-image-btn').click(function(){
        $('#TS_FOTO').val('');
        $('#image-preview').attr('src', '#').addClass('d-none');
        $(this).hide();
        $('#delete_foto_input').val('1'); // Tandai untuk hapus di backend saat form disubmit
    });


    function resetAbsensiForm() {
        $('#absensiForm').trigger("reset");
        $('#absensi_id').val('');
        $('#location_display').val('');
        $('#image-preview').attr('src', '#');
        $('#remove-image-btn').hide();
        $('#delete_foto_input').val('0');
        $('#current-file-link').html('');
    }

    $('#btn-tambah-absensi').click(function () {
        resetAbsensiForm();
        $('#schedule-info').hide();
        $('#absensiModalLabel').text('Tambah Absensi Manual');
        $('#absensiModal').modal('show');
    });

    $('body').on('click', '.editAbsensi', function () {
        var id = $(this).data('id');
        
        // Menggunakan $.get dengan .fail() untuk menangani error
        $.get(`{{ url('presensi/absensi') }}/${id}/edit`, function (data) {
            resetAbsensiForm();
            $('#schedule-info').hide();
            $('#absensiModalLabel').text('Edit Absensi');
            $('#absensi_id').val(data.TS_AUTO);
            $('#TS_EMP').val(data.TS_EMP);
            $('#TS_STATUS').val(data.TS_STATUS);
            $('#TS_TANGGAL').val(data.TS_TANGGAL);
            $('#TS_JAMIN').val(data.TS_JAMIN ? data.TS_JAMIN.substring(0,5) : '');
            $('#TS_JAMOUT').val(data.TS_JAMOUT ? data.TS_JAMOUT.substring(0,5) : '');
            $('#TS_NOTE').val(data.TS_NOTE);
            $('#TS_LATITUDE').val(data.TS_LATITUDE);
            $('#TS_LONGITUDE').val(data.TS_LONGITUDE);
            $('#location_display').val(data.TS_LATITUDE ? `Lat: ${data.TS_LATITUDE}, Long: ${data.TS_LONGITUDE}` : '');

            if(data.TS_FOTO){
                const imageUrl = `/storage/absensi_fotos/${data.TS_FOTO}?t=${new Date().getTime()}`;
                $('#image-preview').attr('src', imageUrl).removeClass('d-none');
                $('#remove-image-btn').show();
            }
            
            if(data.TS_FILE_PENDUKUNG){
                $('#current-file-link').html(`<a href="/storage/dokumen_izin/${data.TS_FILE_PENDUKUNG}" target="_blank">Lihat file saat ini</a>`);
            }

            $('#absensiModal').modal('show');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            // Log error ke console untuk debugging
            console.error("AJAX Error:", textStatus, errorThrown);
            console.error("Response Text:", jqXHR.responseText);
            // Tampilkan pesan error ke pengguna
            Swal.fire('Error!', 'Gagal mengambil data untuk diedit. Cek console untuk detail.', 'error');
        });
    });

    $('#absensiForm').submit(function (e) {
        e.preventDefault();
        var id = $('#absensi_id').val();
        var url = id ? `{{ url('presensi/absensi') }}/${id}` : "{{ route('absensi.store') }}";
        
        var formData = new FormData(this);
        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url, type: "POST", data: formData,
            contentType: false, processData: false,
            success: (data) => {
                $('#absensiModal').modal('hide');
                Swal.fire('Sukses', data.success, 'success').then(() => location.reload());
            },
            error: function (xhr) {
                let errorHtml = '<ul class="text-left mb-0">';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value){
                        errorHtml += '<li>' + value[0] + '</li>';
                    });
                } else {
                    errorHtml += '<li>Terjadi kesalahan. Silakan coba lagi.</li>';
                }
                errorHtml += '</ul>';
                Swal.fire({ title: 'Gagal Menyimpan!', html: errorHtml, icon: 'error' });
            }
        });
    });

    

    // Tombol Hapus Absensi
    $('body').on('click', '.deleteAbsensi', function () {
        var id = $(this).data("id");
        var nama = $(this).data('nama'); // Sekarang ini akan mendapatkan nama dengan benar

        Swal.fire({
            title: 'Apakah Anda yakin?',
            html: `Anda akan menghapus absensi untuk <strong>${nama}</strong>.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE", // Gunakan method DELETE yang benar
                    url: `{{ url('presensi/absensi') }}/${id}`, // URL sesuai route resource
                    success: function (data) {
                        Swal.fire('Terhapus!', data.success, 'success').then(() => {
                            location.reload(); // Reload halaman untuk melihat perubahan
                        });
                    },
                    error: function (data) {
                        Swal.fire('Error!', 'Gagal menghapus data. Pastikan tidak ada relasi data lain.', 'error');
                    }
                });
            }
        });
    });

    // --- SCRIPT BARU UNTUK REKAP LAPORAN ---
    $('#btn-tampilkan-rekap').on('click', function() {
        if (!document.getElementById('rekapForm').checkValidity()) {
            Swal.fire('Peringatan', 'Tanggal awal dan akhir wajib diisi.', 'warning');
            return;
        }

        $('#rekap-result-card').show();
        $('#rekap-loading').show();
        $('#rekap-container').html('');

        $.ajax({
            url: '{{ route("rekap.generate") }}',
            type: 'POST',
            data: $('#rekapForm').serialize(),
            success: function(response) {
                renderRekapTable(response);
            },
            error: function(xhr) {
                Swal.fire('Error', 'Gagal memuat data rekapitulasi.', 'error');
            },
            complete: function() {
                $('#rekap-loading').hide();
            }
        });
    });

    function renderRekapTable(data) {
        if (data.length === 0) {
            $('#rekap-container').html('<p class="text-center text-muted">Tidak ada data yang cocok dengan filter Anda.</p>');
            return;
        }

        let tableHtml = '<table class="table table-bordered" id="rekapTable"><thead><tr><th>Nama Karyawan</th><th>Hadir</th><th>Sakit</th><th>Izin</th><th>Cuti</th><th>Alpa</th></tr></thead><tbody>';
        
        data.forEach(function(item) {
            tableHtml += `
                <tr>
                    <td>${item.employee.emp_Name}</td>
                    <td>${item.summary.Hadir}</td>
                    <td>${item.summary.Sakit}</td>
                    <td>${item.summary.Izin}</td>
                    <td>${item.summary.Cuti}</td>
                    <td>${item.summary.Alpa}</td>
                </tr>
            `;
        });

        tableHtml += '</tbody></table>';
        $('#rekap-container').html(tableHtml);
        $('#rekapTable').DataTable();
    }

    $('#rekapForm').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            Swal.fire('Peringatan', 'Tanggal awal dan akhir wajib diisi.', 'warning');
            return;
        }
        
        const formData = $(this).serialize();
        const pdfUrl = '{{ route("rekap.generate") }}?' + formData + '&print=true';
        window.open(pdfUrl, '_blank');
    });
});
</script>
@endpush
