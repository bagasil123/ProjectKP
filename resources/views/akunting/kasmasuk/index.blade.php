@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Kas Masuk</h1>
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
        @endphp
        @can('tambah', $currentMenuSlug)
        <button type="button" class="btn btn-primary" id="btnTambahKasMasuk">
            <i class="fas fa-plus"></i> Tambah Kas Masuk
        </button>
        @endcan
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kas Masuk</h6>
        </div>
        <div class="card-body">
             <div id="kasmasuk-alert" class="alert" style="display: none;"></div>

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="10%">Tanggal</th>
                            <th>No. Jurnal</th>
                            <th width="5%">Referensi</th>
                            <th width="15%">Rekening Tujuan (Debet)</th>
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
                        {{-- Anda mungkin perlu memfilter $jurnals di controller atau di sini jika 'tipe_jurnal' ada --}}
                        {{-- Atau, jika ini adalah daftar umum, pastikan 'show' menampilkan data dengan benar --}}
                        @php
                            $rekeningTujuanText = '-';
                            if ($jurnal->details->isNotEmpty()) {
                                $debitDetail = $jurnal->details->firstWhere('debet', '>', 0);
                                if ($debitDetail && $debitDetail->perkiraan) {
                                    $rekeningTujuanText = $debitDetail->perkiraan->cls_kiraid . ' - ' . $debitDetail->perkiraan->cls_ina;
                                } elseif ($debitDetail) {
                                    // Fallback jika relasi perkiraan tidak ada tapi acc_kira_id ada
                                    $pk = \App\Models\Akuntansi\AccKira::find($debitDetail->acc_kira_id);
                                    if ($pk) $rekeningTujuanText = $pk->cls_kiraid . ' - ' . $pk->cls_ina;
                                }
                            }
                        @endphp
                        <tr id="row-kasmasuk-{{ $jurnal->id }}">
                            <td>{{ $jurnal->tanggal_buat->format('d-m-Y') }}</td>
                            <td>{{ $jurnal->no_jurnal }}</td>
                            <td>{{ $jurnal->referensi }}</td>
                            <td>{{ $rekeningTujuanText }}</td>
                            <td>{{ Str::limit($jurnal->catatan, 50) }}</td>
                            <td class="text-end">{{ number_format($jurnal->nominal, 2, ',', '.') }}</td>
                            <td>{{ $jurnal->user->name ?? 'N/A' }}</td>
                            <td>{{ $jurnal->tanggal_edit->format('d-m-Y H:i') }}</td>
                            <td>
                                <button class="btn btn-info btn-sm btn-view" data-id="{{ $jurnal->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @can('ubah', $currentMenuSlug)
                                <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $jurnal->id }}" title="Edit Kas Masuk">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('hapus', $currentMenuSlug)
                                <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $jurnal->id }}" data-no="{{ $jurnal->no_jurnal }}" title="Hapus Kas Masuk">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data kas masuk.</td>
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

<!-- Modal View Kas Masuk Detail -->
<div class="modal fade" id="viewKasMasukModal" tabindex="-1" aria-labelledby="viewKasMasukModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewKasMasukModalLabel">Detail Kas Masuk</h5>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md-3"><strong>No. Jurnal:</strong> <span id="viewNoBukti"></span></div>
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

<!-- Modal Tambah/Edit Kas Masuk -->
<div class="modal fade" id="kasMasukModal" tabindex="-1" aria-labelledby="kasMasukModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="kasMasukForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="kasmasuk_id" id="kasMasukId" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="kasMasukModalLabel">Tambah Kas Masuk Baru</h5>
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
                            <label for="rekening_tujuan_id" class="form-label">Rekening Tujuan (Simpan Ke) <span class="text-danger">*</span></label>
                            <div class="col-md-12">
                            <select class="form-control" id="rekening_tujuan_id" name="rekening_tujuan_id" required>
                                <option value="" selected disabled>-- Pilih Rekening Tujuan --</option>
                                @foreach ($perkiraan as $item)
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
                    <h5 class="mb-3">Detail Penerimaan (Diterima Dari)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="detailKasMasukTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Kode Perkiraan <span class="text-danger">*</span></th>
                                    <th style="width: 30%;">Nama Perkiraan</th>
                                    <th style="width: 20%;">Kredit <span class="text-danger">*</span></th>
                                    <th style="width: 15%;">Catatan</th>
                                    <th style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detailKasMasukTableBody"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total Kredit</td>
                                    <td class="text-end fw-bold" id="totalKredit">0.00</td>
                                    <td colspan="2">
                                        {{-- Rekening Tujuan (Debet) akan = Total Kredit --}}
                                    </td>
                                </tr>
                                <tr>
                                     <td colspan="6">
                                         <button type="button" class="btn btn-success btn-sm" id="addDetailRowKasMasuk">
                                             <i class="fas fa-plus"></i> Tambah Baris Penerimaan
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
                    <button type="submit" class="btn btn-primary" id="btnSimpanKasMasuk">
                        <i class="fas fa-save"></i> Simpan Kas Masuk
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
                <h5 class="modal-title" id="previewJurnalModalLabel">Preview Jurnal Kas Masuk</h5>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>No. Jurnal:</strong> <span id="previewNoBukti">Otomatis</span></div>
                    <div class="col-md-6"><strong>Tgl. Transaksi:</strong> <span id="previewTanggal"></span></div>
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
                {{-- Tombol Simpan dari sini bisa dihilangkan jika alur utamanya via modal utama --}}
            </div>
        </div>
    </div>
</div>


<!-- Template Baris Detail Kas Masuk (untuk diclone) -->
<template id="detailKasMasukRowTemplate">
    <tr>
        <td class="row-number text-center"></td>
        <td>
            <select class="form-select form-select-sm select-perkiraan-kredit" name="details[][acc_kira_id]" required>
                <option value="" selected disabled>-- Pilih Kode --</option>
                @foreach ($perkiraan as $item) {{-- Ganti dengan perkiraan yang sesuai untuk kredit --}}
                    <option value="{{ $item->id }}" data-nama="{{ $item->cls_ina }}">{{ $item->cls_kiraid }} - {{ $item->cls_ina }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm nama-perkiraan-kredit" readonly disabled></td>
        <td><input type="text" class="form-control form-control-sm text-end input-kredit" name="details[][kredit]" value="0.00" required></td>
        <td><input type="text" class="form-control form-control-sm" name="details[][catatan_detail]"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm delete-row-kredit"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable(); // Inisialisasi DataTable jika belum

    const kasMasukModal = new bootstrap.Modal(document.getElementById('kasMasukModal'));
    const viewKasMasukModal = new bootstrap.Modal(document.getElementById('viewKasMasukModal'));
    const previewJurnalModal = new bootstrap.Modal(document.getElementById('previewJurnalModal'));

    const detailKasMasukTableBody = $('#detailKasMasukTableBody');
    const detailKasMasukRowTemplate = document.getElementById('detailKasMasukRowTemplate').content;
    // Untuk get nama perkiraan dari dropdown
    const perkiraan = @json($perkiraan->pluck('cls_ina', 'id'));


    function formatCurrency(number) {
        if (isNaN(number)) return "0,00"; // Ganti titik jadi koma untuk format ID
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number);
    }

    function parseCurrency(currencyString) {
        if (!currencyString) return 0;
        let numberString = String(currencyString).replace(/\./g, '').replace(',', '.'); // Hapus titik ribuan, ganti koma desimal jadi titik
        return parseFloat(numberString) || 0;
    }

    function updateRowNumbersKasMasuk() {
        detailKasMasukTableBody.find('tr').each(function(index) {
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

    function calculateTotalKredit() {
        let totalKredit = 0;
        detailKasMasukTableBody.find('tr').each(function() {
            const kredit = parseCurrency($(this).find('.input-kredit').val());
            totalKredit += kredit;
        });
        $('#totalKredit').text(formatCurrency(totalKredit));
        return totalKredit; // Kembalikan nilai untuk digunakan di preview
    }

    function addDetailRowKasMasuk(data = null) {
        const newRow = $(detailKasMasukRowTemplate.cloneNode(true));
        const kreditInput = newRow.find('.input-kredit');

        kreditInput.inputmask({
            alias: 'numeric', groupSeparator: '.', radixPoint: ',', digits: 2, autoGroup: true,
            prefix: '', rightAlign: false, allowMinus: false, digitsOptional: false, placeholder: '0,00',
        });

        if (data) {
            newRow.find('.select-perkiraan-kredit').val(data.acc_kira_id);
            if (data.perkiraan) {
                newRow.find('.nama-perkiraan-kredit').val(data.perkiraan.cls_ina);
            } else if (perkiraanFull[data.acc_kira_id]) {
                 newRow.find('.nama-perkiraan-kredit').val(perkiraanFull[data.acc_kira_id].cls_ina);
            } else {
                newRow.find('.nama-perkiraan-kredit').val('N/A');
            }
            kreditInput.val(parseFloat(data.kredit) || 0); // Beri angka mentah
            newRow.find('[name$="[catatan_detail]"]').val(data.catatan || data.catatan_detail);
        } else {
             newRow.find('.nama-perkiraan-kredit').val('');
        }

        detailKasMasukTableBody.append(newRow);
        updateRowNumbersKasMasuk();
        calculateTotalKredit();
    }

    function resetKasMasukModalForm() {
        $('#kasMasukForm')[0].reset();
        $('#formMethod').val('POST');
        $('#kasMasukId').val('');
        $('#kasMasukModalLabel').text('Tambah Kas Masuk Baru');
        detailKasMasukTableBody.empty();
        $('#modal-alert').hide();
        $('#modal-error-list').empty();
        $('#no_bukti_display').val('Otomatis');
        $('#btnSimpanKasMasuk').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Kas Masuk');
        $('#rekening_tujuan_id').val('').trigger('change'); // Reset select2 jika pakai
        calculateTotalKredit();
    }

    function displayModalErrors(errors) {
        const errorList = $('#modal-error-list');
        errorList.empty();
        $.each(errors, function(key, value) {
            if ($.isArray(value)) {
                $.each(value, function(index, message) { errorList.append('<li>' + message + '</li>'); });
            } else {
                errorList.append('<li>' + value + '</li>');
            }
        });
        $('#modal-alert').fadeIn();
    }

    function showMainAlert(type, message) {
        const alertDiv = $('#kasmasuk-alert');
        alertDiv.removeClass('alert-success alert-danger').addClass(`alert-${type}`).html(message).fadeIn();
        setTimeout(() => { alertDiv.fadeOut(); }, 5000);
    }

    $('#btnTambahKasMasuk').on('click', function() {
        resetKasMasukModalForm();
        addDetailRowKasMasuk(); // Tambah satu baris default untuk penerimaan
        kasMasukModal.show();
    });

    // Tombol Edit Kas Masuk
    $('.btn-edit').on('click', function() {
        const id = $(this).data('id');
        resetKasMasukModalForm();
        $('#kasMasukModalLabel').text('Edit Kas Masuk');
        $('#formMethod').val('PUT');
        $('#kasMasukId').val(id);

        const url = `{{ url('akunting/kas-masuk') }}/${id}/edit`;
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.get(url, function(response) {
            const header = response.jurnalHeader;
            const rekeningTujuan = response.rekeningTujuanDetail;
            const kreditDetails = response.kreditDetails;

            if (header) {
                $('#no_bukti_display').val(header.no_jurnal);
                $('#tanggal_buat').val(header.tanggal_buat.split('T')[0]);
                $('#lokasi').val(header.lokasi_nama);
                $('#referensi').val(header.referensi);
                $('#catatan_header').val(header.catatan);
                if (rekeningTujuan) {
                    $('#rekening_tujuan_id').val(rekeningTujuan.acc_kira_id);
                }

                detailKasMasukTableBody.empty();
                if (kreditDetails && kreditDetails.length > 0) {
                    kreditDetails.forEach(detail => addDetailRowKasMasuk(detail));
                } else {
                    addDetailRowKasMasuk(); // Jika tidak ada detail kredit, tambahkan satu baris kosong
                }
                calculateTotalKredit();
                kasMasukModal.show();
            } else {
                showMainAlert('danger', 'Gagal memuat data kas masuk.');
            }
        }).fail(function() {
            showMainAlert('danger', 'Gagal memuat data kas masuk.');
        }).always(function() {
            $('.btn-edit[data-id="' + id + '"]').prop('disabled', false).html('<i class="fas fa-edit"></i>');
        });
    });


    // Tombol View Detail
    $('.btn-view').on('click', function() {
        const id = $(this).data('id');
        const url = `{{ url('akunting/kas-masuk') }}/${id}`;
        const button = $(this);
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $('#viewKasMasukModalLabel').text('Detail Kas Masuk');
        // Reset field di modal view
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
            const dataJurnal = response.jurnal; // Sesuai struktur respons controller show()
            if (dataJurnal) {
                $('#viewKasMasukModalLabel').text('Detail Kas Masuk: ' + (dataJurnal.no_jurnal || 'N/A'));
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
                        } else if (perkiraanFull && perkiraanFull[detail.acc_kira_id]) {
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
                viewKasMasukModal.show();
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
        const url = `{{ url('akunting/kas-masuk') }}/${id}`;

        Swal.fire({
            title: 'Anda Yakin?', text: `Kas Masuk ${noBukti} akan dihapus!`,
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


    $('#addDetailRowKasMasuk').on('click', function() { addDetailRowKasMasuk(); });

    detailKasMasukTableBody.on('click', '.delete-row-kredit', function() {
        if (detailKasMasukTableBody.find('tr').length > 1) { // Minimal 1 baris penerimaan
            $(this).closest('tr').remove();
            updateRowNumbersKasMasuk();
            calculateTotalKredit();
        } else {
            Swal.fire('Oops!', 'Minimal harus ada 1 baris penerimaan.', 'warning');
        }
    });

    detailKasMasukTableBody.on('change', '.select-perkiraan-kredit', function() {
        const selectedOption = $(this).find('option:selected');
        const namaPerkiraan = selectedOption.data('nama');
        $(this).closest('tr').find('.nama-perkiraan-kredit').val(namaPerkiraan || '');
    });

    detailKasMasukTableBody.on('input change', '.input-kredit', function() {
        calculateTotalKredit();
    });

    // Tombol Preview Jurnal
    $('#btnPreviewJurnal').on('click', function() {
        const previewBody = $('#previewJurnalTableBody');
        previewBody.empty();
        $('#previewTanggal').text($('#tanggal_buat').val() ? new Date($('#tanggal_buat').val()).toLocaleDateString('id-ID') : 'N/A');
        $('#previewCatatanHeaderModal').text($('#catatan_header').val() || '-');
        $('#previewLokasi').text($('#lokasi_nama').val() || '-');
        $('#previewReferensi').text($('#referensi').val() || '-');


        let totalKreditPreview = 0;
        const rekeningTujuanId = $('#rekening_tujuan_id').val();
        const rekeningTujuanOption = $('#rekening_tujuan_id option:selected');
        const rekeningTujuanNama = rekeningTujuanOption.text() || 'N/A'; // Ambil teks dari option
        const rekeningTujuanNamaOnly = rekeningTujuanNama.split(' - ')[1] || rekeningTujuanNama;
        const rekeningTujuanKode = rekeningTujuanOption.text().split(' - ')[0] || 'N/A'; // Ambil kode


        // Baris Debet (Rekening Tujuan)
        // Dihitung setelah total kredit diketahui
        let rowDebetHtml = ''; // Akan diisi nanti

        // Baris Kredit (Diterima Dari)
        let hasDetails = false;
        detailKasMasukTableBody.find('tr').each(function() {
            hasDetails = true;
            const row = $(this);
            const perkiraanId = row.find('.select-perkiraan-kredit').val();
            const perkiraanOption = row.find('.select-perkiraan-kredit option:selected');
            const namaPerkiraan = perkiraanOption.data('nama') || 'N/A';
            const kodePerkiraan = perkiraanOption.text().split(' - ')[0] || 'N/A';
            const kredit = parseCurrency(row.find('.input-kredit').val());
            const catatanDetail = row.find('[name$="[catatan_detail]"]').val();

            if (perkiraanId && kredit > 0) {
                 const rowHtml = `
                    <tr>
                        <td>${kodePerkiraan}</td>
                        <td>${namaPerkiraan}</td>
                        <td class="text-end">${formatCurrency(0)}</td>
                        <td class="text-end">${formatCurrency(kredit)}</td>
                        <td>${catatanDetail || ''}</td>
                    </tr>
                `;
                previewBody.append(rowHtml);
                totalKreditPreview += kredit;
            }
        });

        if (!rekeningTujuanId) {
             Swal.fire('Perhatian!', 'Silakan pilih Rekening Tujuan terlebih dahulu.', 'warning');
             return;
        }
        if (!hasDetails || totalKreditPreview <= 0) {
             Swal.fire('Perhatian!', 'Silakan isi detail penerimaan dengan benar (minimal 1 baris dan total kredit > 0).', 'warning');
             return;
        }


        rowDebetHtml = `
            <tr class="table-info"> {{-- Highlight baris debet --}}
                <td>${rekeningTujuanKode}</td>
                <td>${rekeningTujuanNamaOnly.trim()}</td>
                <td class="text-end">${formatCurrency(totalKreditPreview)}</td>
                <td class="text-end">${formatCurrency(0)}</td>
                <td>Rekening Tujuan</td>
            </tr>
        `;
        previewBody.prepend(rowDebetHtml); // Tambahkan baris debet di awal

        $('#previewTotalDebet').text(formatCurrency(totalKreditPreview));
        $('#previewTotalKredit').text(formatCurrency(totalKreditPreview));

        const balanceStatusPreview = $('#previewBalanceStatus');
        if (Math.abs(totalKreditPreview - totalKreditPreview) < 0.001) { // Akan selalu balance by design
            balanceStatusPreview.text('Balance').removeClass('bg-danger').addClass('bg-success');
        } else {
            balanceStatusPreview.text('Not Balance').removeClass('bg-success').addClass('bg-danger');
        }
        previewJurnalModal.show();
    });


    // Submit Form Kas Masuk
    $('#kasMasukForm').on('submit', function(e) {
        e.preventDefault();

        if (detailKasMasukTableBody.find('tr').length < 1) {
            displayModalErrors({'details': ['Minimal harus ada 1 baris penerimaan.']});
            return;
        }
        if (!$('#rekening_tujuan_id').val()) {
             displayModalErrors({'rekening_tujuan_id': ['Rekening Tujuan harus dipilih.']});
            return;
        }
        const totalKreditVal = calculateTotalKredit();
        if (totalKreditVal <= 0) {
            displayModalErrors({'kredit': ['Total kredit harus lebih besar dari 0.']});
            return;
        }

        const formData = new FormData(this); // Lebih mudah untuk file, tapi oke juga untuk data biasa
        const details = [];
         detailKasMasukTableBody.find('tr').each(function() {
            const row = $(this);
            const kreditInput = row.find('.input-kredit');
            // Ambil nilai unmasked jika inputmask digunakan, atau nilai biasa
            const kreditValue = parseCurrency(kreditInput.inputmask ? kreditInput.inputmask('unmaskedvalue') : kreditInput.val());

            if (row.find('.select-perkiraan-kredit').val() && kreditValue > 0) {
                details.push({
                    acc_kira_id: row.find('.select-perkiraan-kredit').val(),
                    kredit: kreditValue,
                    catatan_detail: row.find('[name$="[catatan_detail]"]').val()
                });
            }
        });

        // Hapus 'details' bawaan dari FormData karena kita akan kirim yang sudah diproses
        formData.delete('details[][acc_kira_id]');
        formData.delete('details[][kredit]');
        formData.delete('details[][catatan_detail]');

        // Tambahkan 'details' yang sudah diproses
        details.forEach((detail, index) => {
            formData.append(`details[${index}][acc_kira_id]`, detail.acc_kira_id);
            formData.append(`details[${index}][kredit]`, detail.kredit);
            formData.append(`details[${index}][catatan_detail]`, detail.catatan_detail);
        });


        const kasMasukId = $('#kasMasukId').val();
        const method = $('#formMethod').val(); // POST or PUT
        let url = "{{ route('kas-masuk.store') }}";
        if (method === 'PUT' && kasMasukId) {
            url = `{{ url('akunting/kas-masuk') }}/${kasMasukId}`;
            // FormData sudah otomatis menangani _method jika input hidden ada
        }

        // DEBUG: Log data yang akan dikirim
        // for (var pair of formData.entries()) {
        //     console.log(pair[0]+ ', ' + pair[1]);
        // }


        const submitButton = $('#btnSimpanKasMasuk');
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
        $('#modal-alert').hide();

        $.ajax({
            url: url,
            type: 'POST', // Method spoofing (PUT) ditangani oleh _method di FormData
            data: formData,
            processData: false, // Penting untuk FormData
            contentType: false, // Penting untuk FormData
            dataType: 'json',
            success: function(response) {
                kasMasukModal.hide();
                showMainAlert('success', response.success);
                location.reload();
            },
            error: function(jqXHR) {
                if (jqXHR.status === 422) {
                    displayModalErrors(jqXHR.responseJSON.errors);
                } else {
                    let errorMsg = 'Terjadi kesalahan server.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.server) {
                         errorMsg = jqXHR.responseJSON.errors.server[0];
                    } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMsg = jqXHR.responseJSON.message;
                    }
                    displayModalErrors({ 'server': [errorMsg] });
                     console.error("Server/AJAX Error:", jqXHR.responseText);
                }
            },
            complete: function() {
                 submitButton.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Kas Masuk');
            }
        });
    });

    // Inisialisasi select2 jika Anda menggunakannya
    // $('#rekening_tujuan_id, .select-perkiraan-kredit').select2({
    //     dropdownParent: $('#kasMasukModal') // Penting jika select2 di dalam modal bootstrap
    // });

}); // End document ready
</script>
@endpush
