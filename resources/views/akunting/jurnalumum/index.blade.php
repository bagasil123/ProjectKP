@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Jurnal Umum</h1>
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
        @endphp
        @can('tambah', $currentMenuSlug)
        <button type="button" class="btn btn-primary" id="btnTambahJurnal">
            <i class="fas fa-plus"></i> Tambah Jurnal
        </button>
        @endcan
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Jurnal Umum</h6>
        </div>
        <div class="card-body">
             {{-- Alert success/error --}}
             <div id="jurnal-alert" class="alert" style="display: none;"></div>

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="10%">Tanggal</th>
                            <th>No. Jurnal</th>
                            <th width="5%">Referensi</th>
                            <th>Lokasi</th>
                            <th width="15%">Catatan</th>
                            <th>Nominal</th>
                            <th width="5%">Pengguna</th>
                            <th width="10%">Tanggal Edit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($jurnals) && count($jurnals) > 0)
                            @foreach ($jurnals as $jurnal)
                            <tr id="row-jurnal-{{ $jurnal->id }}">
                                <td>{{ $jurnal->tanggal_buat->format('d-m-Y') }}</td>
                                <td>{{ $jurnal->no_jurnal }}</td>
                                <td>{{ $jurnal->referensi }}</td>
                                <td>{{ $jurnal->lokasi_nama ?? '-' }}</td>
                                <td>{{ Str::limit($jurnal->catatan, 50) }}</td>
                                <td class="text-end">{{ number_format($jurnal->nominal, 2, ',', '.') }}</td>
                                <td>{{ $jurnal->user->Mem_UserName ?? 'N/A' }}</td>
                                <td>{{ $jurnal->tanggal_edit->format('d-m-Y H:i') }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-view" data-id="{{ $jurnal->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $jurnal->id }}" title="Edit Jurnal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $jurnal->id }}" data-no="{{ $jurnal->no_jurnal }}" title="Hapus Jurnal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        {{-- Jika $jurnals kosong, biarkan <tbody> ini kosong.
                            DataTables akan menangani sisanya. --}}
                    </tbody>
                </table>
            </div>
            {{-- Pagination Links --}}
            <div class="d-flex justify-content-center">
                {{ $jurnals->links() }}
            </div>
        </div>
    </div>
</div>
<!-- Modal View Jurnal Detail -->
<div class="modal fade" id="viewJurnalModal" tabindex="-1" aria-labelledby="viewJurnalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewJurnalModalLabel">Detail Jurnal</h5>
            </div>
            <div class="modal-body">
                {{-- Informasi Header Jurnal --}}
                <div class="row mb-2">
                    <div class="col-md-3"><strong>No. Jurnal:</strong> <span id="viewNoJurnal"></span></div>
                    <div class="col-md-3"><strong>Tgl. Transaksi:</strong> <span id="viewTanggalTransaksi"></span></div>
                    <div class="col-md-3"><strong>Lokasi:</strong> <span id="viewLokasi"></span></div>
                    <div class="col-md-3"><strong>Referensi:</strong> <span id="viewReferensi"></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Catatan Header:</strong>
                        <p id="viewCatatanHeader" class="mt-1" style="white-space: pre-wrap; min-height: 20px;"></p>
                    </div>
                </div>
                <hr>
                {{-- Tabel Detail Penjurnalan --}}
                <h5 class="mb-3">Detail Penjurnalan</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="viewDetailTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 20%;">Kode Perkiraan</th>
                                <th style="width: 25%;">Nama Perkiraan</th>
                                <th style="width: 15%;" class="text-end">Debet</th>
                                <th style="width: 15%;" class="text-end">Kredit</th>
                                <th style="width: 20%;">Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="viewDetailTableBody">
                            {{-- Baris detail akan diisi oleh JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold" id="viewTotalDebet">0.00</td>
                                <td class="text-end fw-bold" id="viewTotalKredit">0.00</td>
                                <td>
                                    <span id="viewBalanceStatus" class="badge"></span>
                                </td>
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
<!-- Modal Tambah/Edit Jurnal -->
<div class="modal fade" id="jurnalModal" tabindex="-1" aria-labelledby="jurnalModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl"> {{-- Ukuran modal besar --}}
        <div class="modal-content">
            <form id="jurnalForm">
                @csrf {{-- CSRF Token --}}
                <input type="hidden" name="_method" id="formMethod" value="POST"> {{-- Untuk metode PUT saat edit --}}
                <input type="hidden" name="jurnal_id" id="jurnalId" value=""> {{-- ID jurnal untuk edit --}}

                <div class="modal-header">
                    <h5 class="modal-title" id="jurnalModalLabel">Tambah Jurnal Baru</h5>
                </div>
                <div class="modal-body">
                     {{-- Alert untuk error di dalam modal --}}
                    <div id="modal-alert" class="alert alert-danger" style="display: none;">
                         <ul id="modal-error-list"></ul>
                    </div>

                    {{-- Header Jurnal --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="no_jurnal_display" class="form-label">No. Jurnal</label>
                            <input type="text" class="form-control" id="no_jurnal_display" readonly disabled>
                        </div>
                        <div class="col-md-3">
                            <label for="tanggal_buat" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_buat" name="tanggal_buat" required>
                        </div>
                         <div class="col-md-3">
                            <label for="lokasi_nama" class="form-label">Lokasi</label>
                            <select class="form-control" id="lokasi_nama" name="lokasi_nama">
                                <option value="" selected>-- Pilih Lokasi --</option>
                                @if(isset($warehouses))
                                    @foreach($warehouses as $warehouse)
                                        {{-- Value dari option adalah NAMA LOKASI, bukan ID --}}
                                        <option value="{{ $warehouse->WARE_Name }}">{{ $warehouse->WARE_Name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                         <div class="col-md-3">
                            <label for="referensi" class="form-label">Referensi</label>
                            <input type="text" class="form-control" id="referensi" name="referensi" placeholder="No. Faktur, dll">
                        </div>
                    </div>
                     <div class="row mb-3">
                         <div class="col-md-12">
                             <label for="catatan_header" class="form-label">Catatan Header</label>
                             <textarea class="form-control" id="catatan_header" name="catatan_header" rows="2"></textarea>
                         </div>
                     </div>

                    <hr>

                    {{-- Detail Jurnal --}}
                    <h5 class="mb-3">Detail Penjurnalan</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="detailTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 18%;">Kode Perkiraan <span class="text-danger">*</span></th>
                                    <th style="width: 22%;">Nama Perkiraan</th>
                                    <th style="width: 15%;">Debet <span class="text-danger">*</span></th>
                                    <th style="width: 15%;">Kredit <span class="text-danger">*</span></th>
                                    <th style="width: 20%;">Catatan</th>
                                    <th style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody">
                                {{-- Baris detail akan ditambahkan oleh JavaScript --}}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold" id="totalDebet">0.00</td>
                                    <td class="text-end fw-bold" id="totalKredit">0.00</td>
                                    <td colspan="2">
                                        <span id="balanceStatus" class="badge bg-success">Balance</span>
                                    </td>
                                </tr>
                                <tr>
                                     <td colspan="7">
                                         <button type="button" class="btn btn-success btn-sm" id="addDetailRow">
                                             <i class="fas fa-plus"></i> Tambah Baris
                                         </button>
                                     </td>
                                 </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    {{-- Tombol ini yang seharusnya menutup modal --}}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanJurnal">
                        <i class="fas fa-save"></i> Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template Baris Detail (untuk diclone) -->
<template id="detailRowTemplate">
    <tr>
        <td class="row-number text-center"></td>
        <td>
            <select class="form-select form-select-sm select-perkiraan" name="details[][acc_kira_id]" required>
                <option value="" selected disabled>-- Pilih Kode --</option>
                @foreach ($perkiraan as $item)
                    <option value="{{ $item->id }}" data-nama="{{ $item->cls_ina }}">{{ $item->cls_kiraid }} - {{ $item->cls_ina }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm nama-perkiraan" readonly disabled></td>
        <td><input type="text" class="form-control form-control-sm text-end input-debet" name="details[][debet]" value="0.00" required></td>
        <td><input type="text" class="form-control form-control-sm text-end input-kredit" name="details[][kredit]" value="0.00" required></td>
        <td><input type="text" class="form-control form-control-sm" name="details[][catatan_detail]"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm delete-row"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>
@endsection

@push('scripts') {{-- Sesuaikan dengan stack script di layout Anda --}}

<script>
$(document).ready(function() {
var table = $('#dataTable').DataTable();

    const jurnalModal = new bootstrap.Modal(document.getElementById('jurnalModal'));
    const viewJurnalModal = new bootstrap.Modal(document.getElementById('viewJurnalModal')); // <-- TAMBAHKAN INI
    const detailTableBody = $('#detailTableBody');
    const detailRowTemplate = document.getElementById('detailRowTemplate').content;
    const perkiraanOptions = @json($perkiraan->pluck('cls_ina', 'id')); // Untuk get nama cepat
    const perkiraanFull = @json($perkiraan->keyBy('id')); // Data lengkap perkiraan by ID


    // -------------------------
    // UTILITIES & HELPER FUNCTIONS
    // -------------------------

    // Format Angka ke Rupiah (atau format standar)
     function formatCurrency(number) {
        if (isNaN(number)) return "0.00";
         // Menggunakan Intl.NumberFormat untuk format yang lebih baik
         return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number);
        // return parseFloat(number).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'); // Koma ribuan, titik desimal
    }

     // Parse Currency String to Number
     function parseCurrency(currencyString) {
        if (!currencyString) return 0;
        // Hapus karakter non-digit kecuali koma desimal jika ada, lalu ganti koma desimal jadi titik
        let numberString = String(currencyString).replace(/[^\d,-]/g, '').replace(',', '.');
        // Hapus pemisah ribuan (titik) jika ada SEBELUM mengganti koma desimal
        numberString = numberString.replace(/\.(?=.*\.)/g, ''); // Hapus titik ribuan
        return parseFloat(numberString) || 0;
    }

     // Update Nomor Baris di Tabel Detail
    function updateRowNumbers() {
        detailTableBody.find('tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
            // Update name attribute index
            $(this).find('select, input').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    // Ganti index di dalam name (e.g., details[0][acc_kira_id] -> details[1][acc_kira_id])
                    let newName = name.replace(/\[\d*\]/, `[${index}]`);
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // Hitung Total Debet & Kredit dan Update Status Balance
    function calculateTotals() {
        let totalDebet = 0;
        let totalKredit = 0;

        detailTableBody.find('tr').each(function() {
            const debet = parseCurrency($(this).find('.input-debet').val());
            const kredit = parseCurrency($(this).find('.input-kredit').val());
            totalDebet += debet;
            totalKredit += kredit;
        });

        $('#totalDebet').text(formatCurrency(totalDebet));
        $('#totalKredit').text(formatCurrency(totalKredit));

        const balanceStatus = $('#balanceStatus');
        if (Math.abs(totalDebet - totalKredit) < 0.001) { // Toleransi floating point
            balanceStatus.text('Balance').removeClass('bg-danger').addClass('bg-success');
        } else {
            balanceStatus.text('Not Balance').removeClass('bg-success').addClass('bg-danger');
        }
    }
    // Tambah Baris Detail Baru
    function addDetailRow(data = null) {
        const newRow = $(detailRowTemplate.cloneNode(true));

        // Apply input mask SEGERA setelah elemen dibuat
        const debetInput = newRow.find('.input-debet');
        const kreditInput = newRow.find('.input-kredit');

        debetInput.inputmask({
            alias: 'numeric',
            groupSeparator: '.', // Pemisah ribuan untuk tampilan
            radixPoint: ',',     // Pemisah desimal untuk tampilan
            digits: 2,
            autoGroup: true,
            prefix: '',
            rightAlign: false,
            allowMinus: false,
            digitsOptional: false,
            placeholder: '0,00',
            // Penting: unmaskAsNumber bisa membantu saat mengambil nilai,
            // tapi untuk set, kita set nilai numerik langsung.
            // unmaskAsNumber: true
        });
        kreditInput.inputmask({ // Konfigurasi yang sama untuk kredit
            alias: 'numeric',
            groupSeparator: '.',
            radixPoint: ',',
            digits: 2,
            autoGroup: true,
            prefix: '',
            rightAlign: false,
            allowMinus: false,
            digitsOptional: false,
            placeholder: '0,00',
            // unmaskAsNumber: true
        });


        // Isi data jika ini untuk edit
        if (data) {
            newRow.find('.select-perkiraan').val(data.acc_kira_id);
            if(data.perkiraan) {
                newRow.find('.nama-perkiraan').val(data.perkiraan.cls_ina);
            } else {
                newRow.find('.nama-perkiraan').val(perkiraanOptions[data.acc_kira_id] || 'N/A');
            }

            // --- PERUBAHAN KRUSIAL DI SINI ---
            // Set nilai MENTAH (numerik) ke field. Inputmask akan memformatnya.
            // Pastikan data.debet dan data.kredit dari server adalah angka (float/decimal).
            const debetValue = parseFloat(data.debet) || 0;
            const kreditValue = parseFloat(data.kredit) || 0;

            debetInput.val(debetValue); // Berikan angka mentah
            kreditInput.val(kreditValue); // Berikan angka mentah
            // Inputmask akan otomatis mengambil nilai ini dan memformatnya sesuai aturan (misal, 1000000 menjadi "1.000.000,00")

            // Jika Anda perlu memicu event change agar inputmask memproses ulang (kadang diperlukan)
            // debetInput.trigger('input'); // atau 'change'
            // kreditInput.trigger('input'); // atau 'change'
            // Namun, biasanya .val() sudah cukup untuk inputmask yang sudah diinisialisasi.

            newRow.find('[name$="[catatan_detail]"]').val(data.catatan); // Sesuaikan 'data.catatan' atau 'data.catatan_detail'
        } else {
            newRow.find('.nama-perkiraan').val('');
            // Untuk baris baru, nilai default 0.00 sudah diatur di template atau oleh placeholder inputmask
            // Jika tidak, Anda bisa set di sini:
            // debetInput.val(0);
            // kreditInput.val(0);
        }

        detailTableBody.append(newRow);
        updateRowNumbers();
        calculateTotals(); // Hitung ulang total
    }


     // Reset Form Modal
     function resetModalForm() {
        console.log("resetModalForm dipanggil"); // Untuk debugging
        $('#jurnalForm')[0].reset();
        $('#formMethod').val('POST');
        $('#jurnalId').val('');
        $('#jurnalModalLabel').text('Tambah Jurnal Baru');
        console.log("resetModalForm: Sebelum detailTableBody.empty(), baris:", detailTableBody.children('tr').length);
        detailTableBody.empty(); // Kosongkan tabel detail SAJA
        console.log("resetModalForm: Setelah detailTableBody.empty(), baris:", detailTableBody.children('tr').length);
        $('#modal-alert').hide();
        $('#modal-error-list').empty();
        $('#no_jurnal_display').val('Otomatis');
        $('#btnSimpanJurnal').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Jurnal');
        // JANGAN TAMBAHKAN addDetailRow() DI SINI LAGI
        calculateTotals(); // Hitung total (seharusnya jadi 0 karena tabel kosong)
    }

    // Tampilkan Error Validasi di Modal
     function displayModalErrors(errors) {
        const errorList = $('#modal-error-list');
        errorList.empty(); // Kosongkan list error sebelumnya

        $.each(errors, function(key, value) {
            // Handle error array (seperti dari validasi balance atau server)
            if ($.isArray(value)) {
                $.each(value, function(index, message) {
                     errorList.append('<li>' + message + '</li>');
                });
            } else {
                // Handle error tunggal (jika ada)
                 errorList.append('<li>' + value + '</li>');
            }
            // Highlight field yang error jika memungkinkan (opsional)
            // Contoh: $('[name="' + key + '"]').addClass('is-invalid');
            // Perlu penanganan khusus untuk error array 'details.*.field'
        });

        $('#modal-alert').fadeIn(); // Tampilkan alert
    }

     // Tampilkan alert utama (di luar modal)
     function showMainAlert(type, message) {
        const alertDiv = $('#jurnal-alert');
        alertDiv.removeClass('alert-success alert-danger alert-warning alert-info'); // Hapus kelas sebelumnya
        alertDiv.addClass(`alert-${type}`); // Tambah kelas baru
        alertDiv.html(message); // Set pesan
        alertDiv.fadeIn(); // Tampilkan

        // Auto hide setelah beberapa detik
        setTimeout(() => {
            alertDiv.fadeOut();
        }, 5000); // 5 detik
    }


    // -------------------------
    // EVENT LISTENERS
    // -------------------------

    // Tombol Tambah Jurnal di Klik
    $('#btnTambahJurnal').on('click', function() {
        console.log("Tombol Tambah Jurnal diklik");
        resetModalForm(); // Reset dulu formnya (sekarang hanya membersihkan)

        // Tambahkan baris default KHUSUS untuk mode Tambah Baru
        console.log("Menambahkan 2 baris default untuk jurnal baru");
        addDetailRow();
        addDetailRow();
        // calculateTotals() akan dipanggil oleh addDetailRow terakhir

        jurnalModal.show();
    });

     // Tombol Edit Jurnal di Klik
     $('.btn-edit').on('click', function() {
        const id = $(this).data('id');
        resetModalForm(); // Panggil reset (yang sekarang HANYA membersihkan)

        $('#jurnalModalLabel').text('Edit Jurnal');
        $('#formMethod').val('PUT');
        $('#jurnalId').val(id);

        const url = `{{ url('akunting/jurnal-umum') }}/${id}/edit`;
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        console.log("Mengambil data edit untuk ID:", id, "URL:", url); // Debugging

        $.get(url, function(response) {
            console.log("Data edit diterima:", response); // Debugging
            const data = response.jurnal;
            if (data) {
                $('#no_jurnal_display').val(data.no_jurnal);
                $('#tanggal_buat').val(data.tanggal_buat.split('T')[0]);
                $('#lokasi_nama').val(data.lokasi_nama);
                $('#referensi').val(data.referensi);
                $('#catatan_header').val(data.catatan);

                // Pastikan tabel detail sudah kosong oleh resetModalForm()
                console.log("HTML tbody sebelum loop detail:", detailTableBody.html()); // Debugging

                if (data.details && data.details.length > 0) {
                    console.log(`Ada ${data.details.length} detail, melooping...`); // Debugging
                    data.details.forEach((detail, index) => {
                        console.log(`Menambahkan detail ke-${index}:`, detail); // Debugging
                        addDetailRow(detail); // Ini akan menambahkan detail yang ada
                    });
                } else {
                    console.warn('Jurnal yang diedit tidak memiliki detail. Menambahkan 2 baris kosong default untuk edit (opsional).');
                    // Jika Anda ingin jurnal yang KOSONG saat diedit tetap memiliki 2 baris:
                    // addDetailRow();
                    // addDetailRow();
                    // Namun, biasanya, jika tidak ada detail, biarkan kosong saja.
                }
                // calculateTotals() dipanggil oleh setiap addDetailRow, jadi total akhir sudah benar.
                // Atau panggil sekali di sini jika addDetailRow tidak memanggilnya.
                // calculateTotals();
                jurnalModal.show();
            } else {
                showMainAlert('danger', 'Gagal memuat data jurnal.');
            }
        }).fail(function(jqXHR) {
            let errorMsg = 'Gagal memuat data jurnal. Coba lagi nanti.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                errorMsg = jqXHR.responseJSON.error;
            }
            showMainAlert('danger', errorMsg);
        }).always(function() {
            $('.btn-edit[data-id="' + id + '"]').prop('disabled', false).html('<i class="fas fa-edit"></i>');
        });
    });

     // Tombol Lihat Detail di Klik (Contoh: Tampilkan di console atau modal read-only)
     $('.btn-view').on('click', function() {
        const id = $(this).data('id');
        const url = `{{ url('akunting/jurnal-umum') }}/${id}`; // Route show

         // Tampilkan loading
         const button = $(this);
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

        // Kosongkan konten modal lihat sebelumnya
        $('#viewJurnalModalLabel').text('Detail Jurnal');
        $('#viewNoJurnal').text('-');
        $('#viewTanggalTransaksi').text('-');
        $('#viewLokasi').text('-');
        $('#viewReferensi').text('-');
        $('#viewCatatanHeader').text('-');
        $('#viewDetailTableBody').empty();
        $('#viewTotalDebet').text(formatCurrency(0));
        $('#viewTotalKredit').text(formatCurrency(0));
        $('#viewBalanceStatus').text('').removeClass('bg-success bg-danger');

        $.get(url, function(response) {
            // Sesuaikan 'data' berdasarkan struktur respons Anda.
            // Jika endpoint show mengembalikan {jurnal: {...}}, gunakan response.jurnal
            // Jika langsung objek jurnal, gunakan response
            const dataJurnal = response.jurnal || response;

            if (dataJurnal) {
                // Isi Header Modal Lihat
                $('#viewJurnalModalLabel').text('Detail Jurnal: ' + (dataJurnal.no_jurnal || 'N/A'));
                $('#viewNoJurnal').text(dataJurnal.no_jurnal || '-');
                try {
                     $('#viewTanggalTransaksi').text(dataJurnal.tanggal_buat ? new Date(dataJurnal.tanggal_buat).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-');
                } catch(e) { console.error("Error formatting date:", e); $('#viewTanggalTransaksi').text(dataJurnal.tanggal_buat || '-');}
                $('#viewLokasi').text(dataJurnal.lokasi_nama || '-');
                $('#viewReferensi').text(dataJurnal.referensi || '-');
                $('#viewCatatanHeader').text(dataJurnal.catatan || '-');

                // Isi Detail Tabel Modal Lihat
                const viewDetailTableBody = $('#viewDetailTableBody');
                viewDetailTableBody.empty(); // Pastikan kosong
                let totalDebetView = 0;
                let totalKreditView = 0;

                if (dataJurnal.details && dataJurnal.details.length > 0) {
                    dataJurnal.details.forEach((detail, index) => {
                        // Mengambil nama dan kode perkiraan
                        // Perlu penyesuaian jika struktur 'detail.perkiraan' berbeda atau tidak ada
                        let kodePerkiraanText = 'N/A';
                        let namaPerkiraanText = 'N/A';

                        if (detail.perkiraan) { // Jika ada relasi 'perkiraan' di objek detail
                            kodePerkiraanText = detail.perkiraan.cls_kiraid || 'N/A';
                            namaPerkiraanText = detail.perkiraan.cls_ina || 'N/A';
                        } else if (perkiraanFull && perkiraanFull[detail.acc_kira_id]) {
                            // Fallback menggunakan objek perkiraanFull yang sudah Anda punya
                            kodePerkiraanText = perkiraanFull[detail.acc_kira_id].cls_kiraid || 'N/A';
                            namaPerkiraanText = perkiraanFull[detail.acc_kira_id].cls_ina || 'N/A';
                        }

                        const rowHtml = `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${kodePerkiraanText}</td>
                                <td>${namaPerkiraanText}</td>
                                <td class="text-end">${formatCurrency(detail.debet)}</td>
                                <td class="text-end">${formatCurrency(detail.kredit)}</td>
                                <td>${detail.catatan_detail || detail.catatan || ''}</td>
                            </tr>
                        `;
                        viewDetailTableBody.append(rowHtml);
                        totalDebetView += parseFloat(detail.debet || 0);
                        totalKreditView += parseFloat(detail.kredit || 0);
                    });
                } else {
                    viewDetailTableBody.append('<tr><td colspan="6" class="text-center">Tidak ada detail jurnal.</td></tr>');
                }

                // Update Total dan Status Balance di Modal Lihat
                $('#viewTotalDebet').text(formatCurrency(totalDebetView));
                $('#viewTotalKredit').text(formatCurrency(totalKreditView));
                const balanceStatusView = $('#viewBalanceStatus');
                if (Math.abs(totalDebetView - totalKreditView) < 0.001) {
                    balanceStatusView.text('Balance').removeClass('bg-danger').addClass('bg-success');
                } else {
                    balanceStatusView.text('Not Balance').removeClass('bg-success').addClass('bg-danger');
                }

                viewJurnalModal.show(); // Tampilkan modal lihat detail
            } else {
                showMainAlert('danger', 'Gagal memuat data detail jurnal.');
            }
        }).fail(function(jqXHR) {
            let errorMsg = 'Gagal memuat detail jurnal. Coba lagi nanti.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                errorMsg = jqXHR.responseJSON.error;
            } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMsg = jqXHR.responseJSON.message;
            }
            showMainAlert('danger', errorMsg);
        }).always(function() {
            button.prop('disabled', false).html('<i class="fas fa-eye"></i>');
        });
    });


    // Tombol Hapus Jurnal di Klik
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const noJurnal = $(this).data('no');
        const url = `{{ url('akunting/jurnal-umum') }}/${id}`;

        Swal.fire({
            title: 'Anda Yakin?',
            text: `Jurnal ${noJurnal} akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                 // Kirim request DELETE
                 $.ajax({
                    url: url,
                    type: 'POST', // Laravel memerlukan POST untuk method spoofing DELETE
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}' // Jangan lupa token
                    },
                    success: function(response) {
                        Swal.fire(
                            'Terhapus!',
                            response.success || 'Jurnal berhasil dihapus.',
                            'success'
                        );
                        // Hapus baris dari tabel atau reload halaman
                        // $('#row-jurnal-' + id).remove(); // Hapus baris
                        location.reload(); // Cara termudah: reload halaman
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        let errorMsg = 'Gagal menghapus jurnal.';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                            errorMsg = jqXHR.responseJSON.error;
                        }
                        Swal.fire(
                            'Gagal!',
                            errorMsg,
                            'error'
                        );
                    }
                });
            }
        })
    });


    // Tambah Baris Detail
    $('#addDetailRow').on('click', function() {
        addDetailRow();
    });

    // Hapus Baris Detail
    detailTableBody.on('click', '.delete-row', function() {
        if (detailTableBody.find('tr').length > 2) { // Jaga minimal 2 baris (opsional)
            $(this).closest('tr').remove();
            updateRowNumbers();
            calculateTotals();
        } else {
            Swal.fire('Oops!', 'Minimal harus ada 2 baris detail.', 'warning');
        }
    });

     // Saat Kode Perkiraan Dipilih -> Isi Nama Perkiraan
     detailTableBody.on('change', '.select-perkiraan', function() {
        const selectedOption = $(this).find('option:selected');
        const namaPerkiraan = selectedOption.data('nama');
        const row = $(this).closest('tr');
        row.find('.nama-perkiraan').val(namaPerkiraan || ''); // Isi field nama perkiraan
    });

    // Saat Input Debet/Kredit Berubah -> Hitung Total
    detailTableBody.on('input change', '.input-debet, .input-kredit', function() {
         const row = $(this).closest('tr');
         const debetInput = row.find('.input-debet');
         const kreditInput = row.find('.input-kredit');
         const debetValue = parseCurrency(debetInput.val());
         const kreditValue = parseCurrency(kreditInput.val());

         // Jika debet diisi > 0, kosongkan kredit
         if ($(this).hasClass('input-debet') && debetValue > 0 && kreditValue > 0) {
             kreditInput.val(formatCurrency(0)).trigger('input'); // Set kredit ke 0 dan trigger event
         }
          // Jika kredit diisi > 0, kosongkan debet
         else if ($(this).hasClass('input-kredit') && kreditValue > 0 && debetValue > 0) {
            debetInput.val(formatCurrency(0)).trigger('input'); // Set debet ke 0 dan trigger event
         }

        calculateTotals(); // Selalu hitung ulang total
    });

     // Apply InputMask saat baris baru ditambahkan (sudah di dalam addDetailRow)
     // Kita juga perlu apply mask ke input yang mungkin sudah ada saat edit
     function applyMaskToExistingRows() {
        $('#detailTableBody .input-debet, #detailTableBody .input-kredit').each(function() {
            if (!$(this).data('inputmask')) { // Hanya apply jika belum ada
                 $(this).inputmask({
                     alias: 'numeric',
                     groupSeparator: '.',
                     radixPoint: ',',
                     digits: 2,
                     autoGroup: true,
                     prefix: '',
                     rightAlign: false,
                     allowMinus: false,
                      digitsOptional: false,
                     placeholder: '0,00',
                     onBeforeMask: function (value, opts) {
                        if(value && isNaN(parseCurrency(value))){
                            return "0,00";
                        }
                        return value;
                     },
                     // unmaskAsNumber: true
                 });
             }
         });
     }

     // Panggil applyMaskToExistingRows saat modal edit dibuka (setelah data terload)
     // Modifikasi bagian .btn-edit .get success callback:
     /*
      $.get(url, function(response) {
            // ... (kode load data) ...
            applyMaskToExistingRows(); // <<< Panggil ini setelah loop detail
            calculateTotals();
            jurnalModal.show();
       })...
     */


    // Submit Form Tambah/Edit Jurnal
    $('#jurnalForm').on('submit', function(e) {
    e.preventDefault();

    // ... (Validasi frontend minimal 2 baris & balance - SUDAH ADA) ...
    if (detailTableBody.find('tr').length < 2) { /* ... */ return; }
    const totalDebet = parseCurrency($('#totalDebet').text());
    const totalKredit = parseCurrency($('#totalKredit').text());
    if (Math.abs(totalDebet - totalKredit) >= 0.01) { /* ... */ return; }


    // 1. Kumpulkan data header
    let headerData = {};
    // Ambil semua field KECUALI detail asli dari form
    $(this).serializeArray().forEach(item => {
        if (!item.name.startsWith('details[')) {
            headerData[item.name] = item.value;
        }
    });
    // Pastikan _token dan _method (jika PUT) ada di headerData

    // 2. Kumpulkan data detail yang sudah diproses (unmasked) - SUDAH ADA
    const details = [];
    detailTableBody.find('tr').each(function(index) {
        const row = $(this);
        const debetInput = row.find('.input-debet');
        const kreditInput = row.find('.input-kredit');
        const debetValue = parseCurrency(debetInput.inputmask('unmaskedvalue') || debetInput.val());
        const kreditValue = parseCurrency(kreditInput.inputmask('unmaskedvalue') || kreditInput.val());

        details.push({
             acc_kira_id: row.find('.select-perkiraan').val(),
             debet: debetValue,
             kredit: kreditValue,
             catatan_detail: row.find('[name$="[catatan_detail]"]').val()
         });
    });

    // 3. Gabungkan menjadi satu objek data
    const finalData = {
        ...headerData, // Masukkan semua field header (_token, tanggal_buat, dll)
        details: details  // Tambahkan key 'details' dengan value berupa array objek detail
    };

    // Tentukan URL dan Method (SAMA SEPERTI SEBELUMNYA)
    const jurnalId = $('#jurnalId').val();
    const method = $('#formMethod').val();
    let url = "{{ route('jurnalumum.store') }}";
    if (method === 'PUT' && jurnalId) {
        url = `{{ url('akunting/jurnal-umum') }}/${jurnalId}`;
         // Pastikan _method=PUT ada di finalData (biasanya sudah dari serializeArray jika field hidden ada)
         if(!finalData['_method'] && method === 'PUT'){
            finalData['_method'] = 'PUT'; // Tambahkan manual jika perlu
         }
    }
     // Pastikan _token ada
     if(!finalData['_token']){
        finalData['_token'] = $('meta[name="csrf-token"]').attr('content'); // Ambil dari meta tag
     }


    // Disable tombol simpan... (SUDAH ADA)
    const submitButton = $('#btnSimpanJurnal');
    submitButton.prop('disabled', true).html(/* Loading spinner */);
    $('#modal-alert').hide();

    // Kirim data via AJAX
    $.ajax({
         url: url,
         type: 'POST', // Selalu POST, method asli (PUT) ada di _method dalam data
         data: finalData, // <<---- KIRIM OBJEK finalData
         dataType: 'json',
         success: function(response) {
             jurnalModal.hide();
             showMainAlert('success', response.success);
             location.reload();
         },
         error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 422) { // Validation Error
                // Tambahkan log untuk melihat struktur error dari Laravel
                console.error("Validation Errors:", jqXHR.responseJSON.errors);
                displayModalErrors(jqXHR.responseJSON.errors);
            } else {
                // Server error atau error lainnya
                let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                 if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.server) {
                    errorMsg = jqXHR.responseJSON.errors.server[0];
                } else if(jqXHR.responseJSON && jqXHR.responseJSON.message) {
                     errorMsg = jqXHR.responseJSON.message;
                }
                 displayModalErrors({ 'server': [errorMsg] });
                 console.error("Server/AJAX Error:", jqXHR.responseText); // Log response error mentah
            }
             // Re-enable tombol simpan
             submitButton.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Jurnal');
         },
         complete: function() {
             // Pastikan tombol simpan enabled kembali jika error tidak terduga
             if (submitButton.prop('disabled')) {
                 submitButton.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Jurnal');
             }
         }
    });
});

}); // End document ready
</script>
@endpush
