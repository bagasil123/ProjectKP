@extends('layouts.admin')
@section('main-content')

<div class="container-fluid">
    {{-- Page-level Title --}}
    <h1 class="h3 mb-4 text-gray-800">{{ ('Transfer Gudang') }}</h1>

    {{-- Session-based Alerts --}}
    @if (session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-left-danger" role="alert">
            <ul class="pl-4 my-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ====================================================== --}}
    {{-- KONDISI 1: MENAMPILKAN DAFTAR TRANSFER (LIST VIEW) --}}
    {{-- ====================================================== --}}
    @if(isset($transfers))
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Daftar Transfer') }}</h6>
            <a href="{{ route('transfergudang.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus fa-sm"></i> {{ __('Buat Transfer Baru') }}
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nomor Transaksi</th>
                            <th>Tanggal</th>
                            <th>Gudang Asal</th>
                            <th>Gudang Tujuan</th>
                            <th class="text-end">Bruto</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Pajak</th>
                            <th class="text-end">Netto</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $transfer)
                            <tr>
                                <td><strong>{{ $transfer->trx_number }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($transfer->Trx_Date)->isoFormat('DD MMMM YYYY') }}</td>
                                <td>{{ $transfer->Trx_WareCode ?? '-' }}</td>
                                <td>{{ $transfer->Trx_RcvNo ?? '-' }}</td>
                                <td class="text-end">{{ number_format($transfer->bruto_from_permintaan, 2) }}</td>
                                <td class="text-end">{{ number_format($transfer->diskon_from_permintaan, 2) }}</td>
                                <td class="text-end">{{ number_format($transfer->pajak_from_permintaan, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($transfer->netto_from_permintaan, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $transfer->trx_posting == 'F' ? 'warning' : 'success' }}">
                                        {{ $transfer->trx_posting == 'F' ? 'DRAFT' : 'POSTED' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($transfer->trx_posting == 'F')
                                        <a href="{{ route('transfergudang.edit', $transfer->Trx_Auto) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-danger delete-draft-btn" data-id="{{ $transfer->Trx_Auto }}" title="Hapus Draft"><i class="fas fa-trash"></i></button>
                                    @else
                                        <a href="{{ route('transfergudang.edit', $transfer->Trx_Auto) }}" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Belum ada data transfer gudang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
            <div class="d-flex justify-content-center">
                {{ $transfers->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ====================================================== --}}
    {{-- KONDISI 2: MENAMPILKAN FORM EDIT / SHOW (FORM VIEW) --}}
    {{-- ====================================================== --}}
    @elseif(isset($transfer))
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('transfergudang.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>
    </div>

    <!-- FORM HEADER UTAMA -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ $transfer->trx_posting == 'F' ? 'Form Edit Transfer' : 'Detail Transfer' }}</h6>
            <span class="badge badge-{{ $transfer->trx_posting == 'F' ? 'warning' : 'success' }}">{{ $transfer->trx_posting == 'F' ? 'DRAFT' : 'POSTED' }}</span>
        </div>
        <div class="card-body">
            <form id="headerForm">
                @csrf
                <input type="hidden" id="transferId" value="{{ $transfer->Trx_Auto }}">

                <div class="row">
                    <!-- Baris 1: No Transaksi dan Tanggal -->
                    <div class="col-md-6 mb-3"><label for="trx_number">No Transaksi</label><input type="text" id="trx_number" class="form-control" value="{{ $transfer->trx_number }}" readonly></div>
                    <div class="col-md-6 mb-3"><label for="Trx_Date">Tanggal Transaksi</label><input type="date" id="Trx_Date" class="form-control" name="Trx_Date" value="{{ $transfer->Trx_Date ? \Carbon\Carbon::parse($transfer->Trx_Date)->format('Y-m-d') : date('Y-m-d') }}" {{ $transfer->trx_posting == 'F' ? '' : 'readonly' }}></div>

                    <!-- BLOK OTOMATISASI (Hanya muncul saat status DRAFT) -->
                    @if($transfer->trx_posting === 'F')
                    <div class="form-group col-12">
                        <label for="permintaan_id" class="text-primary"><strong>Otomatisasi dari Permintaan Gudang</strong></label>
                        <div class="input-group">
                            <select id="permintaan_id" class="form-control">
                                <option value="">-- Pilih Nomor Permintaan untuk Mengisi Otomatis --</option>
                                @foreach($permintaanGudang as $pg)
                                    <option value="{{ $pg->Pur_Auto }}">{{ $pg->pur_ordernumber }} ({{ $pg->Pur_Date->format('d M Y') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-12">
                        {{-- TOMBOL INI SEKARANG AKAN MUNCUL --}}
                        <button type="button" id="btnSyncDetails" class="btn btn-info btn-block" disabled>
                            <i class="fas fa-sync-alt"></i> Gunakan Data & Simpan Detail
                        </button>
                    </div>
                    @endif


                    <!-- Baris 2: Gudang Asal dan Tujuan -->
                    <div class="col-md-6 mb-3"><label for="Trx_WareCode">Gudang Asal</label><select name="Trx_WareCode" id="Trx_WareCode" class="form-control" {{ $transfer->trx_posting == 'F' ? '' : 'disabled' }}><option value="">-- Pilih Gudang --</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->WARE_Name }}" @if($transfer->Trx_WareCode == $warehouse->WARE_Name) selected @endif>{{ $warehouse->WARE_Name }}</option>@endforeach</select></div>
                    <div class="col-md-6 mb-3"><label for="Trx_RcvNo">Gudang Tujuan</label><select name="Trx_RcvNo" id="Trx_RcvNo" class="form-control" {{ $transfer->trx_posting == 'F' ? '' : 'disabled' }}><option value="">-- Pilih Gudang --</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->WARE_Name }}" @if($transfer->Trx_RcvNo == $warehouse->WARE_Name) selected @endif>{{ $warehouse->WARE_Name }}</option>@endforeach</select></div>

                    <!-- Baris 3: Catatan -->
                    <div class="col-12 mb-2"><label for="Trx_Note">Catatan</label><textarea id="Trx_Note" class="form-control" name="Trx_Note" rows="2" {{ $transfer->trx_posting == 'F' ? '' : 'readonly' }}>{{ $transfer->Trx_Note }}</textarea></div>
                </div>
            </form>
        </div>
    </div>

    <!-- TOMBOL AKSI (Hanya muncul saat status DRAFT) -->
    @if($transfer->trx_posting === 'F')
    <div class="mb-3 d-flex">
        <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#detailModal"><i class="fas fa-plus"></i> Tambah Barang Manual</button>
        <button id="btnSubmitTransfer" class="btn btn-success mr-2"><i class="fas fa-save"></i> Simpan & Posting</button>
        <button id="btnCancelDraft" class="btn btn-danger"><i class="fas fa-times"></i> Batalkan & Hapus Draft</button>
    </div>
    @endif

    <!-- TABEL DETAIL BARANG -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Detail Barang</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="detailTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Pajak</th>
                            <th class="text-end">Subtotal</th>
                            @if($transfer->trx_posting === 'F')
                            <th class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfer->details as $detail)
                        <tr>
                            <td>{{ $detail->Trx_ProdCode }}</td>
                            <td>{{ $detail->trx_prodname ?? 'N/A' }}</td>
                            <td>{{ $detail->trx_uom ?? 'N/A' }}</td>
                            <td class="text-end">{{ $detail->Trx_QtyTrx }}</td>
                            <td class="text-end">{{ number_format($detail->trx_cogs ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($detail->trx_discount ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($detail->trx_taxes ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($detail->trx_nettprice ?? 0, 2) }}</td>
                            @if($transfer->trx_posting === 'F')
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger delete-detail-btn" data-id="{{ $detail->id }}" title="Hapus Item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $transfer->trx_posting === 'F' ? '9' : '8' }}" class="text-center">Belum ada barang yang ditambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL UNTUK TAMBAH BARANG MANUAL -->
    @if($transfer->trx_posting === 'F')
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content"><form id="detailForm">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Barang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modal_Trx_ProdCode">Kode Produk
                                    <span class="text-danger"></span>
                                </label>
                                <input type="text" id="modal_Trx_ProdCode" name="Trx_ProdCode" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modal_trx_prodname">Nama Produk</label>
                                <input type="text" id="modal_trx_prodname" name="trx_prodname" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <hr>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modal_trx_uom" class="form-label">Satuan</label>
                                <!-- Satuan tidak perlu 'detail-calc' karena tidak mengubah angka -->
                                <select name="trx_uom" id="modal_trx_uom" class="form-control" required>
                                    <option value="" disabled selected>-- Pilih Satuan --</option>
                                    <option value="PCS">PCS</option>
                                    <option value="BOX">BOX</option>
                                    <option value="KG">KG</option>
                                </select>
                                </div>
                            <div class="col-md-6 mb-3">
                                <label for="modal_Trx_QtyTrx" class="form-label">Qty</label>
                                <!-- PASTIKAN ADA kelas 'detail-calc' -->
                                <input type="number" id="modal_Trx_QtyTrx" name="Trx_QtyTrx" class="form-control detail-calc" step="1" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modal_trx_cogs" class="form-label">Harga</label>
                                <!-- PASTIKAN ADA kelas 'detail-calc' -->
                                <input type="number" id="modal_trx_cogs" name="trx_cogs" class="form-control detail-calc" step="0.01" min="0" required>
                            </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modal_trx_discount">Diskon</label>
                                    <!-- PASTIKAN ADA kelas 'detail-calc' -->
                                    <input type="number" id="modal_trx_discount" name="trx_discount" class="form-control detail-calc" step="0.01" min="0" value="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modal_trx_taxes">Pajak</label>
                                    <!-- PASTIKAN ADA kelas 'detail-calc' -->
                                    <input type="number" id="modal_trx_taxes" name="trx_taxes" class="form-control detail-calc" step="0.01" min="0" value="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modal_trx_nettprice">Subtotal (Netto)</label>
                                    <!-- Ini TIDAK perlu 'detail-calc' karena hanya menampilkan hasil -->
                                    <input type="number" id="modal_trx_nettprice" name="trx_nettprice" class="form-control" readonly style="background-color: #e9ecef;">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" id="btnSaveDetail" class="btn btn-primary">Simpan Barang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif

</div>
@endsection


@push('scripts')
{{-- SweetAlert2 --}}
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
    // =======================================================================
    // SCRIPT UNTUK HALAMAN DAFTAR UTAMA (INDEX)
    // =======================================================================
    // Event listener dipasang pada elemen statis terdekat (tabel)
    $('#dataTable').on('click', '.delete-draft-btn', function() {
        const transferId = $(this).data('id');
        const baseUrl = '{{ url("/mutasigudang/transfergudang") }}';

        Swal.fire({
            title: 'Hapus Draft Ini?',
            text: "Aksi ini akan menghapus draft transfer secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${transferId}`,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire('Berhasil Dihapus!', response.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    });

    // =======================================================================
    // SCRIPT KHUSUS UNTUK HALAMAN FORM (EDIT / SHOW)
    // =======================================================================
    @if(isset($transfer))

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    const transferId = $('#transferId').val();
    const isDraft = "{{ $transfer->trx_posting }}" === 'F';
    const baseUrl = '{{ url("/mutasigudang/transfergudang") }}';

    // --- KALKULASI REAL-TIME UNTUK MODAL TAMBAH BARANG MANUAL ---
    function calculateManualNetPrice() {
    // Ambil nilai dari setiap input menggunakan ID yang sudah distandarkan
    const qty = parseFloat($('#modal_Trx_QtyTrx').val()) || 0;
    const cogs = parseFloat($('#modal_trx_cogs').val()) || 0;
    const discount = parseFloat($('#modal_trx_discount').val()) || 0;
    const taxes = parseFloat($('#modal_trx_taxes').val()) || 0;

    // Hitung subtotal dengan rumus yang benar
    const netPrice = (qty * cogs) - discount + taxes;

    // Set nilai dari input subtotal
    $('#modal_trx_nettprice').val(netPrice.toFixed(2));
    }

    // Pasang event listener ke SEMUA input dengan kelas '.detail-calc'
    // yang berada di dalam modal. Ini memastikan setiap perubahan akan memicu kalkulasi ulang.
    $('#detailModal').on('keyup change', '.detail-calc', calculateManualNetPrice);

    // Saat modal ditutup, reset form dan kalkulasi
    $('#detailModal').on('hidden.bs.modal', function () {
        $('#detailForm')[0].reset();
        $('#modal_trx_nettprice').val(''); // Kosongkan subtotal
    });


    // --- FITUR OTOMATISASI DARI PERMINTAAN GUDANG ---
    $('#permintaan_id').on('change', function() {
        const permintaanId = $(this).val();
        const syncButton = $('#btnSyncDetails');

        if (!permintaanId) {
            syncButton.prop('disabled', true);
            return;
        }
        syncButton.prop('disabled', false).html('<i class="fas fa-spinner fa-spin"></i> Memuat...');

        $.ajax({
            url: `${baseUrl}/fetch-details/${permintaanId}`,
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    const data = response.data;

                    $('#Trx_WareCode').val(data.pur_warehouse);
                    $('#Trx_RcvNo').val(data.pur_destination);

                    const tableBody = $('#detailTable tbody');
                    tableBody.empty();

                    if (data.details.length > 0) {
                        data.details.forEach(function(item) {
                            const row = `<tr>
                                <td>${item.Pur_ProdCode}</td>
                                <td>${item.pur_prodname}</td>
                                <td>${item.Pur_UOM}</td>
                                <td class="text-end">${item.Pur_Qty}</td>
                                <td class="text-end">${Number(item.Pur_GrossPrice).toLocaleString('id-ID')}</td>
                                <td class="text-end">${Number(item.Pur_Discount).toLocaleString('id-ID')}</td>
                                <td class="text-end">${Number(item.Pur_Taxes).toLocaleString('id-ID')}</td>
                                <td class="text-end fw-bold">${Number(item.Pur_NettPrice).toLocaleString('id-ID')}</td>
                                <td class="text-center"><span class="badge badge-info">PREVIEW</span></td>
                            </tr>`;
                            tableBody.append(row);
                        });
                        syncButton.html('<i class="fas fa-sync-alt"></i> Gunakan Data & Simpan Detail');
                    } else {
                        tableBody.html('<tr><td colspan="9" class="text-center">Permintaan ini tidak memiliki detail barang.</td></tr>');
                        syncButton.prop('disabled', true).html('<i class="fas fa-sync-alt"></i> Gunakan Data & Simpan Detail');
                    }
                } else {
                    Swal.fire('Error!', response.message, 'error');
                    syncButton.prop('disabled', true).html('<i class="fas fa-sync-alt"></i> Gunakan Data & Simpan Detail');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
                syncButton.prop('disabled', true).html('<i class="fas fa-sync-alt"></i> Gunakan Data & Simpan Detail');
            }
        });
    });

    $('#btnSyncDetails').on('click', function() {
        const permintaanId = $('#permintaan_id').val();
        if(!permintaanId) return;

        Swal.fire({
            title: 'Gunakan Data Ini?',
            text: "Detail yang ada saat ini akan diganti. Gudang Asal & Tujuan juga akan disesuaikan. Lanjutkan?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${transferId}/sync-details`,
                    type: 'POST',
                    data: { permintaan_id: permintaanId },
                    success: function(response) {
                        Swal.fire('Sukses!', response.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                         Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal menyimpan data.', 'error');
                    }
                });
            }
        });
    });

    // --- FUNGSI-FUNGSI UMUM ---
    if(isDraft) {
        $('#headerForm').on('change', 'input[type=date], textarea, select', function() {
            $.ajax({
                url: `${baseUrl}/${transferId}`,
                type: 'POST',
                data: $('#headerForm').serialize() + '&_method=PUT',
                success: function(response) {
                    if(response.success) { console.log(response.message); }
                },
                error: function(xhr) { console.error(xhr.responseJSON?.message); }
            });
        });
    }

    $('#btnSaveDetail').on('click', function () {
        const detailData = $('#detailForm').serializeArray();
        detailData.push({name: 'Trx_Auto', value: transferId});
        $.ajax({
            url: '{{ route("transfergudang.storeDetail") }}',
            method: 'POST', data: $.param(detailData),
            success: function (response) { Swal.fire("Sukses!", response.message, "success").then(() => location.reload()); },
            error: function (xhr) {
                let errorHtml = '<ul>';
                if (xhr.status === 422) { $.each(xhr.responseJSON.errors, function (key, value) { errorHtml += '<li>' + value[0] + '</li>'; });
                } else { errorHtml += '<li>' + (xhr.responseJSON?.message || "Terjadi kesalahan.") + '</li>'; }
                errorHtml += '</ul>';
                Swal.fire("Error!", errorHtml, "error");
            }
        });
    });

    $('#detailTable').on('click', '.delete-detail-btn', function() {
    const detailId = $(this).data('id');

    Swal.fire({
        title: 'Hapus Barang Ini?',
        text: "Data tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                // PERBAIKI BARIS INI: Tambahkan {transferId} di tengah URL
                url: `${baseUrl}/${transferId}/details/${detailId}`,

                type: 'POST',
                data: {
                    _method: 'DELETE',
                    // Anda tidak perlu mengirim transfer_id di data lagi karena sudah ada di URL
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Muat ulang halaman untuk melihat perubahan
                    Swal.fire('Berhasil!', 'Item berhasil dihapus.', 'success').then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        }
    });
});

    $('#btnSubmitTransfer').click(function() {
        const hasItems = $('#detailTable tbody tr').not(':has(td[colspan])').length > 0;
        if (!hasItems) {
            Swal.fire('Peringatan!', 'Tambahkan minimal satu barang sebelum posting.', 'warning');
            return;
        }
        Swal.fire({
            title: 'Simpan & Posting Transfer?', text: "Data tidak bisa diubah lagi.", icon: 'question',
            showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Ya, Posting!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${transferId}/submit`,
                    type: 'POST', data: { _method: 'PUT' },
                    success: function(response) { Swal.fire('Berhasil!', response.message, 'success').then(() => window.location.href = '{{ route("transfergudang.index") }}' ); },
                    error: function(xhr) { Swal.fire('Gagal!', xhr.responseJSON?.message, 'error');}
                });
            }
        });
    });

    $('#btnCancelDraft').click(function() {
        Swal.fire({
            title: 'Batalkan & Hapus Draft Ini?', text: "Seluruh data akan dihapus permanen.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus Draft!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${transferId}`,
                    type: 'POST', data: { _method: 'DELETE' },
                    success: function(response) { Swal.fire('Dihapus!', response.message, 'success').then(() => window.location.href = '{{ route("transfergudang.index") }}' ); },
                    error: function(xhr) { Swal.fire('Error!', xhr.responseJSON?.message, 'error');}
                });
            }
        });
    });

    @endif
});
</script>
@endpush
