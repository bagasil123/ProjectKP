{{-- resources/views/akunting/kaskeluar/index.blade.php --}}
@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Kas Keluar</h1>
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
        @endphp
        @can('tambah', $currentMenuSlug)
        <button type="button" class="btn btn-primary" id="btnTambahKasKeluar">
            <i class="fas fa-plus"></i> Tambah Kas Keluar
        </button>
        @endcan
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kas Keluar</h6>
        </div>
        <div class="card-body">
             <div id="kaskeluar-alert" class="alert" style="display: none;"></div>

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="10%">Tanggal</th>
                            <th>No. Jurnal</th> {{-- No Jurnal menjadi No Bukti --}}
                            <th width="5%">Referensi</th>
                            <th width="15%">Rekening Sumber (Kredit)</th> {{-- Diubah --}}
                            <th width="15%">Catatan</th>
                            <th>Nominal</th>
                            <th width="5%">Pengguna</th>
                            <th width="10%">Tanggal Edit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($jurnals) && count($jurnals) > 0)
                        @forelse ($jurnals as $jurnal)
                        @php
                            $rekeningAsalText = '-';
                            if ($jurnal->details->isNotEmpty()) {
                                $kreditDetail = $jurnal->details->firstWhere('kredit', '>', 0);
                                if ($kreditDetail && $kreditDetail->perkiraan) {
                                    $rekeningAsalText = $kreditDetail->perkiraan->cls_kiraid . ' - ' . $kreditDetail->perkiraan->cls_ina;
                                } elseif ($kreditDetail) {
                                    $pk = \App\Models\Akuntansi\AccKira::find($kreditDetail->acc_kira_id);
                                    if ($pk) $rekeningAsalText = $pk->cls_kiraid . ' - ' . $pk->cls_ina;
                                }
                            }
                        @endphp
                        <tr id="row-kaskeluar-{{ $jurnal->id }}">
                            <td>{{ $jurnal->tanggal_buat->format('d-m-Y') }}</td>
                            <td>{{ $jurnal->no_jurnal }}</td>
                            <td>{{ $jurnal->referensi }}</td>
                            <td>{{ $rekeningAsalText }}</td>
                            <td>{{ Str::limit($jurnal->catatan, 50) }}</td>
                            <td class="text-end">{{ number_format($jurnal->nominal, 2, ',', '.') }}</td>
                            <td>{{ $jurnal->user->name ?? 'N/A' }}</td>
                            <td>{{ $jurnal->tanggal_edit->format('d-m-Y H:i') }}</td>
                            <td>
                                <button class="btn btn-info btn-sm btn-view" data-id="{{ $jurnal->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @can('ubah', $currentMenuSlug)
                                <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $jurnal->id }}" title="Edit Kas Keluar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('hapus', $currentMenuSlug)
                                <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $jurnal->id }}" data-no="{{ $jurnal->no_jurnal }}" title="Hapus Kas Keluar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data kas keluar.</td>
                        </tr>
                        @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $jurnals->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal View Kas Keluar Detail -->
<div class="modal fade" id="viewKasKeluarModal" tabindex="-1" aria-labelledby="viewKasKeluarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewKasKeluarModalLabel">Detail Kas Keluar</h5>
            </div>
            <div class="modal-body">
                {{-- Sama seperti Kas Masuk, karena menampilkan jurnal umum --}}
                <div class="row mb-2">
                    <div class="col-md-3"><strong>No. Bukti:</strong> <span id="viewNoBukti"></span></div>
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
                        <tbody id="viewDetailTableBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold" id="viewTotalDebet">0.00</td>
                                <td class="text-end fw-bold" id="viewTotalKredit">0.00</td>
                                <td><span id="viewBalanceStatus" class="badge"></span></td>
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

<!-- Modal Tambah/Edit Kas Keluar -->
<div class="modal fade" id="kasKeluarModal" tabindex="-1" aria-labelledby="kasKeluarModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="kasKeluarForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="kaskeluar_id" id="kasKeluarId" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="kasKeluarModalLabel">Tambah Kas Keluar Baru</h5>
                </div>
                <div class="modal-body">
                    <div id="modal-alert" class="alert alert-danger" style="display: none;">
                         <ul id="modal-error-list"></ul>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="no_bukti_display" class="form-label">No. Jurnal</label>
                            <input type="text" class="form-control" id="no_bukti_display" readonly disabled>
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
                            <label for="rekening_asal_id" class="form-label">Rekening Sumber (Diambil Dari) <span class="text-danger">*</span></label>
                            <div class="col-md-12">
                            <select class="form-control" id="rekening_asal_id" name="rekening_asal_id" required>
                                <option value="" selected disabled>-- Pilih Rekening Sumber --</option>
                                @foreach ($perkiraan as $item) {{-- Variabel dari controller KK --}}
                                    <option value="{{ $item->id }}" data-nama="{{ $item->cls_ina }}">{{ $item->cls_kiraid }} - {{ $item->cls_ina }}</option>
                                @endforeach
                            </select>
                        </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                         <div class="col-md-4">
                            <label for="referensi" class="form-label">Referensi</label>
                            <input type="text" class="form-control" id="referensi" name="referensi" placeholder="No. Faktur, dll">
                        </div>
                         <div class="col-md-8">
                             <label for="catatan_header" class="form-label">Catatan Header</label>
                             <textarea class="form-control" id="catatan_header" name="catatan_header" rows="1"></textarea>
                         </div>
                     </div>
                    <hr>
                    <h5 class="mb-3">Detail Pengeluaran (Untuk Biaya)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="detailKasKeluarTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Kode Perkiraan <span class="text-danger">*</span></th>
                                    <th style="width: 30%;">Nama Perkiraan</th>
                                    <th style="width: 20%;">Debet <span class="text-danger">*</span></th> {{-- Diubah --}}
                                    <th style="width: 15%;">Catatan</th>
                                    <th style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detailKasKeluarTableBody"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total Debet</td> {{-- Diubah --}}
                                    <td class="text-end fw-bold" id="totalDebet">0.00</td> {{-- Diubah --}}
                                    <td colspan="2">
                                        {{-- Rekening Asal (Kredit) akan = Total Debet --}}
                                    </td>
                                </tr>
                                <tr>
                                     <td colspan="6">
                                         <button type="button" class="btn btn-success btn-sm" id="addDetailRowKasKeluar">
                                             <i class="fas fa-plus"></i> Tambah Baris Pengeluaran
                                         </button>
                                     </td>
                                 </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info" id="btnPreviewJurnal">
                        <i class="fas fa-file-alt"></i> Preview Jurnal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanKasKeluar">
                        <i class="fas fa-save"></i> Simpan Kas Keluar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview Jurnal -->
<div class="modal fade" id="previewJurnalModal" tabindex="-1" aria-labelledby="previewJurnalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewJurnalModalLabel">Preview Jurnal Kas Keluar</h5>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>No. Jurnal:</strong> <span id="previewNoBukti">Otomatis</span></div>
                    <div class="col-md-6"><strong>Tgl. Transaksi:</strong> <span id="previewTanggal"></span></div>
                </div>
                 <div class="row mb-2">
                    <div class="col-md-6"><strong>Lokasi:</strong> <span id="previewLokasi"></span></div>
                    <div class="col-md-6"><strong>Referensi:</strong> <span id="previewReferensi"></span></div>
                </div>
                 <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Catatan Header:</strong>
                        <p id="previewCatatanHeaderModal" class="mt-1" style="white-space: pre-wrap; min-height: 20px;"></p>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Perkiraan</th>
                                <th>Nama Perkiraan</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end">Kredit</th>
                                <th>Catatan Detail</th>
                            </tr>
                        </thead>
                        <tbody id="previewJurnalTableBody">
                            {{-- Akan diisi oleh JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold" id="previewTotalDebet">0.00</td>
                                <td class="text-end fw-bold" id="previewTotalKredit">0.00</td>
                                <td><span id="previewBalanceStatus" class="badge"></span></td>
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


<!-- Template Baris Detail Kas Keluar (untuk diclone) -->
<template id="detailKasKeluarRowTemplate">
    <tr>
        <td class="row-number text-center"></td>
        <td>
            <select class="form-select form-select-sm select-perkiraan-debet" name="details[][acc_kira_id]" required>
                <option value="" selected disabled>-- Pilih Kode --</option>
                @foreach ($perkiraan as $item) {{-- Variabel dari controller KK --}}
                    <option value="{{ $item->id }}" data-nama="{{ $item->cls_ina }}">{{ $item->cls_kiraid }} - {{ $item->cls_ina }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm nama-perkiraan-debet" readonly disabled></td>
        <td><input type="text" class="form-control form-control-sm text-end input-debet" name="details[][debet]" value="0.00" required></td> {{-- Diubah --}}
        <td><input type="text" class="form-control form-control-sm" name="details[][catatan_detail]"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm delete-row-debet"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable();

    const kasKeluarModal = new bootstrap.Modal(document.getElementById('kasKeluarModal'));
    const viewKasKeluarModal = new bootstrap.Modal(document.getElementById('viewKasKeluarModal'));
    const previewJurnalModal = new bootstrap.Modal(document.getElementById('previewJurnalModal'));

    const detailKasKeluarTableBody = $('#detailKasKeluarTableBody');
    const detailKasKeluarRowTemplate = document.getElementById('detailKasKeluarRowTemplate').content;

    // Variabel perkiraan dari controller KasKeluarController
        const perkiraan = @json($perkiraan->pluck('cls_ina', 'id'));

    function formatCurrency(number) {
        if (isNaN(number)) return "0,00";
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(number);
    }

    function parseCurrency(currencyString) {
        if (!currencyString) return 0;
        let numberString = String(currencyString).replace(/\./g, '').replace(',', '.');
        return parseFloat(numberString) || 0;
    }

    function updateRowNumbersKasKeluar() {
        detailKasKeluarTableBody.find('tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
            $(this).find('select, input').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    let newName = name.replace(/\[\d*\]/, `[${index}]`);
                    $(this).attr('name', newName);
                }
            });
        });
    }

    function calculateTotalDebet() { // Diubah
        let totalDebet = 0;
        detailKasKeluarTableBody.find('tr').each(function() {
            const debet = parseCurrency($(this).find('.input-debet').val()); // Diubah
            totalDebet += debet;
        });
        $('#totalDebet').text(formatCurrency(totalDebet)); // Diubah
        return totalDebet;
    }

    function addDetailRowKasKeluar(data = null) { // Diubah
        const newRow = $(detailKasKeluarRowTemplate.cloneNode(true));
        const debetInput = newRow.find('.input-debet'); // Diubah

        debetInput.inputmask({ // Diubah
            alias: 'numeric', groupSeparator: '.', radixPoint: ',', digits: 2, autoGroup: true,
            prefix: '', rightAlign: false, allowMinus: false, digitsOptional: false, placeholder: '0,00',
        });

        if (data) {
            newRow.find('.select-perkiraan-debet').val(data.acc_kira_id); // Diubah
            let namaPerkiraan = 'N/A';
            if (data.perkiraan) {
                namaPerkiraan = data.perkiraan.cls_ina;
            } else if (semuaPerkiraan[data.acc_kira_id]) { // Menggunakan semuaPerkiraan
                 namaPerkiraan = semuaPerkiraan[data.acc_kira_id].cls_ina;
            }
            newRow.find('.nama-perkiraan-debet').val(namaPerkiraan); // Diubah
            debetInput.val(parseFloat(data.debet) || 0); // Diubah
            newRow.find('[name$="[catatan_detail]"]').val(data.catatan || data.catatan_detail);
        } else {
             newRow.find('.nama-perkiraan-debet').val(''); // Diubah
        }

        detailKasKeluarTableBody.append(newRow);
        updateRowNumbersKasKeluar();
        calculateTotalDebet(); // Diubah
    }

    function resetKasKeluarModalForm() { // Diubah
        $('#kasKeluarForm')[0].reset(); // Diubah
        $('#formMethod').val('POST');
        $('#kasKeluarId').val(''); // Diubah
        $('#kasKeluarModalLabel').text('Tambah Kas Keluar Baru'); // Diubah
        detailKasKeluarTableBody.empty();
        $('#modal-alert').hide();
        $('#modal-error-list').empty();
        $('#no_bukti_display').val('Otomatis');
        $('#btnSimpanKasKeluar').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Kas Keluar'); // Diubah
        $('#rekening_asal_id').val('').trigger('change'); // Diubah
        calculateTotalDebet(); // Diubah
    }

    function displayModalErrors(errors) { /* Sama */ }
    function showMainAlert(type, message) {
        const alertDiv = $('#kaskeluar-alert'); // Diubah
        alertDiv.removeClass('alert-success alert-danger').addClass(`alert-${type}`).html(message).fadeIn();
        setTimeout(() => { alertDiv.fadeOut(); }, 5000);
    }

    $('#btnTambahKasKeluar').on('click', function() { // Diubah
        resetKasKeluarModalForm(); // Diubah
        addDetailRowKasKeluar(); // Diubah
        kasKeluarModal.show(); // Diubah
    });

    // Tombol Edit Kas Keluar
    $('.btn-edit').on('click', function() { // Diubah nama class jika perlu, tapi ini di scope Kas Keluar
        const id = $(this).data('id');
        resetKasKeluarModalForm(); // Diubah
        $('#kasKeluarModalLabel').text('Edit Kas Keluar'); // Diubah
        $('#formMethod').val('PUT');
        $('#kasKeluarId').val(id); // Diubah

        const url = `{{ url('akunting/kas-keluar') }}/${id}/edit`; // Diubah
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.get(url, function(response) {
            const header = response.jurnalHeader;
            const rekeningAsal = response.rekeningAsalDetail; // Diubah
            const debetDetails = response.debetDetails; // Diubah

            if (header) {
                $('#no_bukti_display').val(header.no_jurnal);
                $('#tanggal_buat').val(header.tanggal_buat.split('T')[0]);
                $('#lokasi_nama').val(header.lokasi_nama); // ID form input field
                $('#referensi').val(header.referensi);
                $('#catatan_header').val(header.catatan);
                if (rekeningAsal) { // Diubah
                    $('#rekening_asal_id').val(rekeningAsal.acc_kira_id); // Diubah
                }

                detailKasKeluarTableBody.empty();
                if (debetDetails && debetDetails.length > 0) { // Diubah
                    debetDetails.forEach(detail => addDetailRowKasKeluar(detail)); // Diubah
                } else {
                    addDetailRowKasKeluar(); // Diubah
                }
                calculateTotalDebet(); // Diubah
                kasKeluarModal.show(); // Diubah
            } else {
                showMainAlert('danger', 'Gagal memuat data kas keluar.'); // Diubah
            }
        }).fail(function() {
            showMainAlert('danger', 'Gagal memuat data kas keluar.'); // Diubah
        }).always(function() {
            $('.btn-edit[data-id="' + id + '"]').prop('disabled', false).html('<i class="fas fa-edit"></i>');
        });
    });


    // Tombol View Detail (Struktur modal view sama, jadi JSnya mirip)
    $('.btn-view').on('click', function() {
        const id = $(this).data('id');
        const url = `{{ url('akunting/kas-keluar') }}/${id}`; // Diubah
        const button = $(this);
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $('#viewKasKeluarModalLabel').text('Detail Kas Keluar'); // Diubah
        $('#viewNoBukti').text('-');
        $('#viewTanggalTransaksi').text('-');
        $('#viewLokasi').text('-');
        $('#viewReferensi').text('-');
        $('#viewCatatanHeader').text('-');
        $('#viewDetailTableBody').empty();
        $('#viewTotalDebet').text(formatCurrency(0));
        $('#viewTotalKredit').text(formatCurrency(0));
        $('#viewBalanceStatus').text('').removeClass('bg-success bg-danger');

        $.get(url, function(response) {
            const dataJurnal = response.jurnal;
            if (dataJurnal) {
                $('#viewKasKeluarModalLabel').text('Detail Kas Keluar: ' + (dataJurnal.no_jurnal || 'N/A')); // Diubah
                $('#viewNoBukti').text(dataJurnal.no_jurnal || '-');
                try {
                    $('#viewTanggalTransaksi').text(dataJurnal.tanggal_buat ? new Date(dataJurnal.tanggal_buat).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-');
                } catch(e) { $('#viewTanggalTransaksi').text(dataJurnal.tanggal_buat || '-');}
                 $('#viewLokasi').text(dataJurnal.lokasi_nama || '-');
                $('#viewReferensi').text(dataJurnal.referensi || '-');
                $('#viewCatatanHeader').text(dataJurnal.catatan || '-');

                const viewDetailTableBody = $('#viewDetailTableBody');
                viewDetailTableBody.empty();
                let totalDebetView = 0;
                let totalKreditView = 0;

                if (dataJurnal.details && dataJurnal.details.length > 0) {
                    dataJurnal.details.forEach((detail, index) => {
                        let kodePerkiraanText = 'N/A';
                        let namaPerkiraanText = 'N/A';

                        if (detail.perkiraan) {
                            kodePerkiraanText = detail.perkiraan.cls_kiraid || 'N/A';
                            namaPerkiraanText = detail.perkiraan.cls_ina || 'N/A';
                        } else if (semuaPerkiraan && semuaPerkiraan[detail.acc_kira_id]) { // Menggunakan semuaPerkiraan
                            kodePerkiraanText = semuaPerkiraan[detail.acc_kira_id].cls_kiraid || 'N/A';
                            namaPerkiraanText = semuaPerkiraan[detail.acc_kira_id].cls_ina || 'N/A';
                        }
                        const rowHtml = `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${kodePerkiraanText}</td>
                                <td>${namaPerkiraanText}</td>
                                <td class="text-end">${formatCurrency(detail.debet)}</td>
                                <td class="text-end">${formatCurrency(detail.kredit)}</td>
                                <td>${detail.catatan || ''}</td>
                            </tr>
                        `;
                        viewDetailTableBody.append(rowHtml);
                        totalDebetView += parseFloat(detail.debet || 0);
                        totalKreditView += parseFloat(detail.kredit || 0);
                    });
                } else {
                    viewDetailTableBody.append('<tr><td colspan="6" class="text-center">Tidak ada detail.</td></tr>');
                }
                $('#viewTotalDebet').text(formatCurrency(totalDebetView));
                $('#viewTotalKredit').text(formatCurrency(totalKreditView));
                const balanceStatusView = $('#viewBalanceStatus');
                if (Math.abs(totalDebetView - totalKreditView) < 0.001) {
                    balanceStatusView.text('Balance').removeClass('bg-danger').addClass('bg-success');
                } else {
                    balanceStatusView.text('Not Balance').removeClass('bg-success').addClass('bg-danger');
                }
                viewKasKeluarModal.show(); // Diubah
            } else {
                showMainAlert('danger', 'Gagal memuat data detail.');
            }
        }).fail(function() {
            showMainAlert('danger', 'Gagal memuat detail.');
        }).always(function() {
            button.prop('disabled', false).html('<i class="fas fa-eye"></i>');
        });
    });

    // Tombol Hapus
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const noBukti = $(this).data('no');
        const url = `{{ url('akunting/kas-keluar') }}/${id}`; // Diubah

        Swal.fire({
            title: 'Anda Yakin?', text: `Kas Keluar ${noBukti} akan dihapus!`, // Diubah
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url, type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire('Terhapus!', response.success, 'success');
                        location.reload();
                    },
                    error: function(jqXHR) {
                        Swal.fire('Gagal!', jqXHR.responseJSON.error || 'Gagal menghapus.', 'error');
                    }
                });
            }
        });
    });


    $('#addDetailRowKasKeluar').on('click', function() { addDetailRowKasKeluar(); }); // Diubah

    detailKasKeluarTableBody.on('click', '.delete-row-debet', function() { // Diubah
        if (detailKasKeluarTableBody.find('tr').length > 1) {
            $(this).closest('tr').remove();
            updateRowNumbersKasKeluar(); // Diubah
            calculateTotalDebet(); // Diubah
        } else {
            Swal.fire('Oops!', 'Minimal harus ada 1 baris pengeluaran.', 'warning'); // Diubah
        }
    });

    detailKasKeluarTableBody.on('change', '.select-perkiraan-debet', function() { // Diubah
        const selectedOption = $(this).find('option:selected');
        const namaPerkiraan = selectedOption.data('nama');
        $(this).closest('tr').find('.nama-perkiraan-debet').val(namaPerkiraan || ''); // Diubah
    });

    detailKasKeluarTableBody.on('input change', '.input-debet', function() { // Diubah
        calculateTotalDebet(); // Diubah
    });

    // Tombol Preview Jurnal
    $('#btnPreviewJurnal').on('click', function() {
        const previewBody = $('#previewJurnalTableBody');
        previewBody.empty();
        $('#previewTanggal').text($('#tanggal_buat').val() ? new Date($('#tanggal_buat').val()).toLocaleDateString('id-ID') : 'N/A');
        $('#previewLokasi').text($('#lokasi_nama').val() || '-');
        $('#previewReferensi').text($('#referensi').val() || '-');
        $('#previewCatatanHeaderModal').text($('#catatan_header').val() || '-');

        let totalDebetPreview = 0; // Diubah
        const rekeningAsalId = $('#rekening_asal_id').val(); // Diubah
        const rekeningAsalOption = $('#rekening_asal_id option:selected'); // Diubah
        const rekeningAsalNamaFull = rekeningAsalOption.text() || 'N/A';
        const rekeningAsalNama = rekeningAsalNamaFull.split(' - ')[1] || rekeningAsalNamaFull;
        const rekeningAsalKode = rekeningAsalNamaFull.split(' - ')[0] || 'N/A';

        // Baris Kredit (Rekening Asal) - akan ditambahkan setelah loop detail
        let rowKreditHtml = '';

        // Baris Debet (Dibayarkan Untuk)
        let hasDetails = false;
        detailKasKeluarTableBody.find('tr').each(function() {
            hasDetails = true;
            const row = $(this);
            const perkiraanId = row.find('.select-perkiraan-debet').val(); // Diubah
            const perkiraanOption = row.find('.select-perkiraan-debet option:selected'); // Diubah
            const namaPerkiraan = perkiraanOption.data('nama') || 'N/A';
            const kodePerkiraan = perkiraanOption.text().split(' - ')[0] || 'N/A';
            const debet = parseCurrency(row.find('.input-debet').val()); // Diubah
            const catatanDetail = row.find('[name$="[catatan_detail]"]').val();

            if (perkiraanId && debet > 0) { // Diubah
                 const rowHtml = `
                    <tr>
                        <td>${kodePerkiraan}</td>
                        <td>${namaPerkiraan}</td>
                        <td class="text-end">${formatCurrency(debet)}</td>
                        <td class="text-end">${formatCurrency(0)}</td>
                        <td>${catatanDetail || ''}</td>
                    </tr>
                `;
                previewBody.append(rowHtml);
                totalDebetPreview += debet; // Diubah
            }
        });

        if (!rekeningAsalId) {
             Swal.fire('Perhatian!', 'Silakan pilih Rekening Asal terlebih dahulu.', 'warning'); // Diubah
             return;
        }
        if (!hasDetails || totalDebetPreview <= 0) {
             Swal.fire('Perhatian!', 'Silakan isi detail pengeluaran dengan benar (minimal 1 baris dan total debet > 0).', 'warning'); // Diubah
             return;
        }

        // Tambahkan baris Kredit (Rekening Asal) di akhir
        rowKreditHtml = `
            <tr class="table-info"> {{-- Highlight baris kredit (asal) --}}
                <td>${rekeningAsalKode}</td>
                <td>${rekeningAsalNama.trim()}</td>
                <td class="text-end">${formatCurrency(0)}</td>
                <td class="text-end">${formatCurrency(totalDebetPreview)}</td>
                <td>Rekening Sumber</td>
            </tr>
        `;
        previewBody.append(rowKreditHtml); // Tambahkan baris kredit di akhir

        $('#previewTotalDebet').text(formatCurrency(totalDebetPreview)); // Diubah
        $('#previewTotalKredit').text(formatCurrency(totalDebetPreview)); // Diubah

        const balanceStatusPreview = $('#previewBalanceStatus');
        // Akan selalu balance by design
        if (Math.abs(totalDebetPreview - totalDebetPreview) < 0.001) {
            balanceStatusPreview.text('Balance').removeClass('bg-danger').addClass('bg-success');
        } else {
            balanceStatusPreview.text('Not Balance').removeClass('bg-success').addClass('bg-danger');
        }
        previewJurnalModal.show();
    });


    // Submit Form Kas Keluar
    $('#kasKeluarForm').on('submit', function(e) { // Diubah
        e.preventDefault();

        if (detailKasKeluarTableBody.find('tr').length < 1) {
            displayModalErrors({'details': ['Minimal harus ada 1 baris pengeluaran.']}); // Diubah
            return;
        }
        if (!$('#rekening_asal_id').val()) { // Diubah
             displayModalErrors({'rekening_asal_id': ['Rekening Asal harus dipilih.']}); // Diubah
            return;
        }
        const totalDebetVal = calculateTotalDebet(); // Diubah
        if (totalDebetVal <= 0) {
            displayModalErrors({'debet': ['Total pengeluaran (debet) harus lebih besar dari 0.']}); // Diubah
            return;
        }

        const formData = new FormData(this);
        const details = [];
         detailKasKeluarTableBody.find('tr').each(function() {
            const row = $(this);
            const debetInput = row.find('.input-debet'); // Diubah
            const debetValue = parseCurrency(debetInput.inputmask ? debetInput.inputmask('unmaskedvalue') : debetInput.val()); // Diubah

            if (row.find('.select-perkiraan-debet').val() && debetValue > 0) { // Diubah
                details.push({
                    acc_kira_id: row.find('.select-perkiraan-debet').val(), // Diubah
                    debet: debetValue, // Diubah
                    catatan_detail: row.find('[name$="[catatan_detail]"]').val()
                });
            }
        });

        formData.delete('details[][acc_kira_id]');
        formData.delete('details[][debet]'); // Diubah
        formData.delete('details[][catatan_detail]');

        details.forEach((detail, index) => {
            formData.append(`details[${index}][acc_kira_id]`, detail.acc_kira_id);
            formData.append(`details[${index}][debet]`, detail.debet); // Diubah
            formData.append(`details[${index}][catatan_detail]`, detail.catatan_detail);
        });

        const kasKeluarId = $('#kasKeluarId').val(); // Diubah
        const method = $('#formMethod').val();
        let url = "{{ route('kas-keluar.store') }}"; // Diubah, pastikan route name benar
        if (method === 'PUT' && kasKeluarId) {
            url = `{{ url('akunting/kas-keluar') }}/${kasKeluarId}`; // Diubah
        }

        const submitButton = $('#btnSimpanKasKeluar'); // Diubah
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
        $('#modal-alert').hide();

        $.ajax({
            url: url, type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
            success: function(response) {
                kasKeluarModal.hide(); // Diubah
                showMainAlert('success', response.success);
                location.reload();
            },
            error: function(jqXHR) { /* Sama */ },
            complete: function() {
                 submitButton.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Kas Keluar'); // Diubah
            }
        });
    });

});
</script>
@endpush
