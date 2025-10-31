@extends('layouts.admin')
@section('main-content')

<div class="container-fluid">
    {{-- TAMPILAN DAFTAR PERMINTAAN (INDEX) --}}
    @if(isset($orders))
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan Gudang</h6>
            <a href="{{ route('gudangorder.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Buat Permintaan Baru
            </a>
        </div>
        <div class="card-body">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No. Permintaan</th>
                            <th>Tanggal</th>
                            <th>Lokasi Asal</th>
                            <th>Lokasi Tujuan</th>
                            <th>Bruto</th>
                            <th>Diskon</th>
                            <th>Pajak</th>
                            <th>Netto</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->pur_ordernumber }}</td>
                            <td>{{ $order->Pur_Date ? $order->Pur_Date->format('d M Y') : '-' }}</td>
                            <td>{{ $order->pur_warehouse }}</td>
                            <td>{{ $order->pur_destination ?? '-' }}</td>
                            <td class="text-end">{{ number_format($order->total_bruto, 2) }}</td>
                            <td class="text-end">{{ number_format($order->total_discount, 2) }}</td>
                            <td class="text-end">{{ number_format($order->total_taxes, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($order->grand_total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->pur_status === 'draft' ? 'warning' : 'success' }} text-white">
                                    {{ $order->pur_status === 'draft' ? 'DRAFT' : 'SUBMITTED' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('gudangorder.edit', $order->Pur_Auto) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('gudangorder.show', $order->Pur_Auto) }}" class="btn btn-sm btn-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if ($order->pur_status === 'draft')
                                <button class="btn btn-sm btn-danger delete-order-btn"
                                    data-id="{{ $order->Pur_Auto }}"
                                    data-name="{{ $order->pur_ordernumber }}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Belum ada data permintaan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                 {!! $orders->links() !!}
            </div>
        </div>
    </div>

    {{-- TAMPILAN FORM (CREATE / EDIT / SHOW) --}}
    @elseif(isset($order))
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('gudangorder.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Form Permintaan Gudang</h6>
            <span class="badge bg-{{ $order->pur_status === 'draft' ? 'warning' : 'success' }} text-white">
                {{ $order->pur_status === 'draft' ? 'DRAFT' : 'SUBMITTED' }}
            </span>
        </div>
        <div class="card-body">
            <form id="headerForm">
                @csrf
                <input type="hidden" id="orderId" value="{{ $order->Pur_Auto }}">
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="pur_ordernumber" class="form-label">Nomor Permintaan</label>
                        <input type="text" name="pur_ordernumber" class="form-control"  value="{{ $order->pur_ordernumber }}" readonly >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="Pur_Date" class="form-label">Tanggal</label>
                        <input type="date" name="Pur_Date" class="form-control" value="{{ old('Pur_Date', $order->Pur_Date ? $order->Pur_Date->format('Y-m-d') : date('Y-m-d')) }}" required {{ $order->pur_status === 'draft' ? '' : 'readonly' }}>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pur_warehouse" class="form-label">Lokasi Asal</label>
                        <select name="pur_warehouse" class="form-control" required {{ $order->pur_status === 'draft' ? '' : 'disabled' }}>
                            <option value="">-- Pilih Lokasi Asal --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->WARE_Name }}" @if($order->pur_warehouse == $warehouse->WARE_Name) selected @endif>
                                    {{ $warehouse->WARE_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pur_destination">Lokasi Tujuan</label>
                        <select name="pur_destination" class="form-control" required {{ $order->pur_status === 'draft' ? '' : 'disabled' }}>
                            <option value="">-- Pilih Gudang Tujuan --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->WARE_Name }}" @if($order->pur_destination == $warehouse->WARE_Name) selected @endif>
                                    {{ $warehouse->WARE_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label for="Pur_Note" class="form-label">Catatan</label>
                        <textarea class="form-control" name="Pur_Note" {{ $order->pur_status === 'draft' ? '' : 'readonly' }}>{{ $order->Pur_Note }}</textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TOMBOL AKSI (Hanya muncul jika status DRAFT) --}}
    @if($order->pur_status === 'draft')
    <div class="mb-3 d-flex">
        <button type="button" class="btn btn-primary mr-2" data-bs-toggle="modal" data-bs-target="#detailModal">
            <i class="fas fa-plus"></i> Tambah Barang
        </button>
        <button id="btnSubmitOrder" class="btn btn-success mr-2">
            <i class="fas fa-save"></i> Simpan & Ajukan
        </button>
        <button id="btnCancelDraft" class="btn btn-danger">
            <i class="fas fa-times"></i> Batalkan Draft
        </button>
    </div>
    @endif

    {{-- TABEL DETAIL BARANG --}}
    <div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Barang</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="detailTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th>Pajak</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->details as $detail)
                    <tr>
                        <td>{{ $detail->Pur_ProdCode }}</td>
                        <td>{{ $detail->pur_prodname }}</td>
                        <td>{{ $detail->Pur_UOM }}</td>
                        <td>{{ $detail->Pur_Qty }}</td>
                        <td>{{ number_format($detail->Pur_GrossPrice, 2) }}</td>
                        <td>{{ number_format($detail->Pur_Discount, 2) }}</td>
                        <td>{{ number_format($detail->Pur_Taxes, 2) }}</td>
                        <td>{{ number_format($detail->Pur_NettPrice, 2) }}</td>
                        <td>
                            @if($order->pur_status === 'draft')
                            <button class="btn btn-sm btn-danger delete-detail-btn" data-id="{{ $detail->Pur_Det_Auto }}" title="Hapus Barang">
                                <i class="fas fa-trash"></i>
                            </button>
                            @else
                            <span class="text-muted">Locked</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        {{-- Ubah colspan menjadi 9 --}}
                        <td colspan="9" class="text-center font-italic">Belum ada barang yang ditambahkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

    {{-- MODAL UNTUK TAMBAH BARANG (Hanya bisa jika status DRAFT) --}}
    @if($order->pur_status === 'draft')
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="detailForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Pur_Auto" value="{{ $order->Pur_Auto }}">
                    <div class="row">
                        {{-- Kode Produk & Nama Produk --}}
                        <div class="col-md-6 mb-3">
                            <label for="Pur_ProdCode" class="form-label">Kode Produk</label>
                            <input type="text" name="Pur_ProdCode" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pur_prodname" class="form-label">Nama Produk</label>
                            <input type="text" name="pur_prodname" class="form-control" required>
                        </div>

                        {{-- Satuan & Qty --}}
                        <div class="col-md-6 mb-3">
                            <label for="Pur_UOM" class="form-label">Satuan</label>
                            <select name="Pur_UOM" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Satuan --</option>
                                <option value="PCS">PCS</option>
                                <option value="BOX">BOX</option>
                                <option value="KG">KG</option>
                                {{-- Tambahkan UOM lain jika perlu --}}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Pur_Qty" class="form-label">Qty</label>
                            {{-- Tambahkan class 'detail-calc' untuk memicu kalkulasi --}}
                            <input type="number" id="Pur_Qty" name="Pur_Qty" class="form-control detail-calc" step="1" min="1" required>
                        </div>

                        {{-- Harga, Diskon, Pajak (BARU) --}}
                        <div class="col-md-4 mb-3">
                            <label for="Pur_GrossPrice" class="form-label">Harga</label>
                            {{-- Tambahkan class 'detail-calc' --}}
                            <input type="number" id="Pur_GrossPrice" name="Pur_GrossPrice" class="form-control detail-calc" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="Pur_Discount" class="form-label">Diskon</label>
                            {{-- Tambahkan class 'detail-calc' --}}
                            <input type="number" id="Pur_Discount" name="Pur_Discount" class="form-control detail-calc" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="Pur_Taxes" class="form-label">Pajak</label>
                            {{-- Tambahkan class 'detail-calc' --}}
                            <input type="number" id="Pur_Taxes" name="Pur_Taxes" class="form-control detail-calc" step="0.01" min="0" value="0">
                        </div>

                        {{-- Nominal / Harga Bersih (BARU) --}}
                        <div class="col-12 mb-3">
                            <label for="pur_NetPrice" class="form-label">Nominal (Harga Bersih)</label>
                            <input type="number" id="pur_NetPrice" name="pur_NetPrice" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnSaveDetail" class="btn btn-primary">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

    @endif

    @endif {{-- Menutup blok @if/@elseif utama --}}

</div>
@endsection


@push('scripts')
{{-- SweetAlert2 --}}
<script>
$('#dataTable').DataTable();
$(document).ready(function() {
    // Setup CSRF Token untuk semua request AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    // Fungsi helper untuk menampilkan notifikasi toast
    function showToast(type, message) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({
            icon: type,
            title: message
        });
    }

    // Blok kode ini hanya berjalan jika kita berada di halaman form (edit/create)
    @if(isset($order))
    const orderId = $('#orderId').val();
    const baseUrl = `{{ url('/mutasigudang/gudangorder') }}`;

    function calculateNetPrice() {
        const qty = parseFloat($('#Pur_Qty').val()) || 0;
        const price = parseFloat($('#Pur_GrossPrice').val()) || 0;
        const discount = parseFloat($('#Pur_Discount').val()) || 0;
        const taxes = parseFloat($('#Pur_Taxes').val()) || 0;

        if (qty > 0 && price > 0) {
            const subtotal = qty * price;
            const netPrice = (subtotal - discount) + taxes;
            $('#pur_NetPrice').val(netPrice.toFixed(2));
        } else {
            $('#pur_NetPrice').val(''); // Kosongkan jika qty atau harga tidak valid
        }
    }

    // Panggil fungsi kalkulasi setiap kali input yang relevan diubah
    // Kita menggunakan class '.detail-calc' yang sudah ditambahkan di HTML
    $('#detailModal').on('keyup change', '.detail-calc', function() {
        calculateNetPrice();
    });

    // Reset form dan kalkulasi saat modal ditutup
    $('#detailModal').on('hidden.bs.modal', function () {
        $('#detailForm')[0].reset();
        $('#pur_NetPrice').val('');
    });

    // 1. Simpan perubahan header secara otomatis saat input diubah
    $('#headerForm').on('change', 'input, textarea, select', function() {
        const data = $('#headerForm').serialize();
        $.ajax({
            url: `${baseUrl}/${orderId}/update-header`,
            type: 'POST',
            data: data + '&_method=PUT',
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message || 'Perubahan berhasil disimpan.');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal memperbarui data.';
                showToast('error', errorMsg);
                console.log(xhr.responseText);
            }
        });
    });

    // 2. Simpan detail barang baru saat tombol di modal diklik
    $('#btnSaveDetail').on('click', function() {
        const data = $('#detailForm').serialize();
        $.ajax({
            url: '{{ route("gudangorder.storeDetail") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#detailModal').modal('hide');
                    $('#detailForm')[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Barang berhasil ditambahkan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Muat ulang halaman untuk menampilkan data baru
                    });
                } else {
                    Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan pada server.';
                if (xhr.status === 422) { // Error validasi
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).map(e => `<li>${e[0]}</li>`).join('');
                    Swal.fire('Error Validasi!', `<ul>${errorMsg}</ul>`, 'error');
                } else {
                    Swal.fire('Error!', xhr.responseJSON.message || errorMsg, 'error');
                }
                console.error(xhr.responseText);
            }
        });
    });

    // 3. Hapus header barang
    $('#dataTable').on('click', '.delete-order-btn', function() {
        const orderId = $(this).data('id');
        const orderName = $(this).data('name');
        const baseUrl = `{{ url('/mutasigudang/gudangorder') }}`;

        Swal.fire({
            title: 'Hapus Permintaan Ini?',
            text: `Anda akan menghapus draft ${orderName}. Aksi ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${orderId}`,
                    type: 'POST', // Gunakan POST dengan method override
                    data: {
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Dihapus!',
                            text: response.message,
                        }).then(() => {
                            location.reload(); // Muat ulang halaman untuk melihat perubahan
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Gagal menghapus draft.';
                        showToast('error', errorMsg);
                    }
                });
            }
        });
    });

    // 3. Hapus detail barang
    $('#detailTable').on('click', '.delete-detail-btn', function() {
        const detailId = $(this).data('id');
        Swal.fire({
            title: 'Hapus Barang Ini?',
            text: "Anda tidak akan bisa mengembalikannya!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${orderId}/details/${detailId}`,
                    type: 'POST', // Method override
                    data: { _method: 'DELETE' },
                    success: function() {
                        location.reload();
                    },
                    error: function(xhr) {
                        showToast('error', 'Gagal menghapus barang.');
                    }
                });
            }
        });
    });

    // 4. Submit seluruh permintaan (mengubah status jadi 'submitted')
    $('#btnSubmitOrder').click(function() {
        const hasItems = $('#detailTable tbody tr').length > 0 && !$('#detailTable tbody td[colspan]').length;
        if (!hasItems) {
            Swal.fire('Peringatan!', 'Anda harus menambahkan setidaknya satu barang sebelum menyimpan.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Simpan & Ajukan Permintaan?',
            text: "Setelah diajukan, permintaan tidak bisa diubah lagi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Ajukan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${orderId}/submit`,
                    type: 'POST',
                    data: { '_method': 'PUT' },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Permintaan berhasil diajukan.',
                        }).then(() => {
                            window.location.href = '{{ route("gudangorder.index") }}';
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Gagal mengajukan permintaan.';
                        showToast('error', errorMsg);
                    }
                });
            }
        });
    });

    // 5. Batalkan dan hapus seluruh draft
    $('#btnCancelDraft').click(function() {
        Swal.fire({
            title: 'Batalkan & Hapus Draft Ini?',
            text: "Seluruh data pada form ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Tidak, Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/${orderId}`,
                    type: 'POST',
                    data: { '_method': 'DELETE' },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus!',
                            text: 'Draft permintaan telah berhasil dihapus.',
                        }).then(() => {
                           window.location.href = '{{ route("gudangorder.index") }}';
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Gagal membatalkan draft.';
                        showToast('error', errorMsg);
                    }
                });
            }
        });
    });
    @endif

});
</script>
@endpush
