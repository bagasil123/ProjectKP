@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
@endphp

@section('main-content')
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Form Retur Pembelian</h6>
            </div>
            <div class="card-body">
                <form id="headerForm">@csrf
                    <input type="hidden" id="draftId" value="{{ $header->Trx_Auto }}">
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">No. Retur</label>
                        <div class="col-sm-4">
                            <input type="text" id="trxNumber" name="trx_number" class="form-control form-control-sm"
                                value="{{ $header->trx_number }}" readonly>
                        </div>
                        <label class="col-sm-2 col-form-label">Tanggal Retur</label>
                        <div class="col-sm-4">
                            <input type="date" name="Trx_Date" id="Trx_Date" class="form-control form-control-sm"
                                value="{{ old('Trx_Date', $header->Trx_Date?->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Supplier</label>
                        <div class="col-sm-4">
                            <select name="Trx_SupCode" id="Trx_SupCode" class="form-control" required>
                                @if ($header->Trx_SupCode)
                                    <option value="{{ $header->Trx_SupCode }}" selected>
                                        {{ $header->Trx_SupCode }}
                                        @if ($header->supplier)
                                            - {{ $header->supplier->nama_supplier }}
                                        @endif
                                    </option>
                                @endif
                            </select>
                        </div>
                        <label class="col-sm-2 col-form-label">Gudang</label>
                        <div class="col-sm-4">
                            <select name="Trx_WareCode" id="Trx_WareCode" class="form-control" required>
                                @if ($header->Trx_WareCode)
                                    <option value="{{ $header->Trx_WareCode }}" selected>
                                        {{ $header->Trx_WareCode }}
                                        @if ($header->warehouse)
                                            - {{ $header->warehouse->WARE_Name }}
                                        @endif
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">Catatan</label>
                        <div class="col-sm-10">
                            <textarea name="Trx_Note" id="Trx_Note" class="form-control form-control-sm" rows="2">{{ old('Trx_Note', $header->Trx_Note) }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-3 d-flex">
            @can('tambah', $currentMenuSlug)
                <button type="button" id="addCodeButton" class="btn btn-primary mr-2" data-bs-toggle="modal"
                    data-bs-target="#dtlModal">
                    <i class="fas fa-plus"></i>
                </button>
            @endcan
            <button id="btnPublish" class="btn btn-info mr-2">
                <i class="fas fa-floppy-disk"></i>
            </button>
            <button id="btnCancelDraft" class="btn btn-danger mr-2">
                <i class="fas fa-times"></i>
            </button>
            <a href="{{ route('retur.pembelian.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive pb-3">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width='10%'>Kode</th>
                                <th width='10%'>Nama Produk</th>
                                <th width='5%'>Qty</th>
                                <th width='5%'>Satuan</th>
                                <th width='10%'>Harga Beli</th>
                                <th width='10%'>Disc (%)</th>
                                <th width='10%'>Pajak (%)</th>
                                <th width='10%'>Nominal</th>
                                <th width='20%'>Catatan</th>
                                <th width='10%'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- isi akan di-render via AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="dtlModal" tabindex="-1">
            <div class="modal-dialog">
                <form id="dtlForm" onsubmit="return false;">@csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Kode</label>
                                <div class="col-sm-10">
                                    <input name="Trx_ProdCode" id="Trx_ProdCode" class="form-control" required>
                                    <small class="text-muted">Ketik kode produk untuk mendapatkan informasi produk</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Nama Produk</label>
                                <div class="col-sm-10">
                                    <input name="trx_prodname" id="trx_prodname" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Satuan</label>
                                <div class="col-sm-4">
                                    <select name="trx_uom" id="trx_uom" class="form-control" required>
                                        <option value="">Pilih Satuan</option>
                                        {{-- isi akan di-render via AJAX --}}
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Harga Beli</label>
                                <div class="col-sm-4">
                                    <input type="number" min="1000" step="1000" name="Trx_GrossPrice"
                                        id="Trx_GrossPrice" class="form-control calc-trigger" readonly required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Pajak (%)</label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" step="0.1" name="Trx_Taxes" id="Trx_Taxes"
                                        class="form-control calc-trigger" required>
                                </div>
                                <label class="col-sm-2 col-form-label">Potongan (%)</label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" step="0.1" name="Trx_Discount"
                                        id="Trx_Discount" class="form-control calc-trigger" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Qty</label>
                                <div class="col-sm-4">
                                    <input type="number" min="1" name="Trx_QtyTrx" id="Trx_QtyTrx"
                                        class="form-control calc-trigger" required>
                                </div>
                                <div class="col-sm-6">
                                    <span class="form-text text-muted">Stok tersedia: <span id="stock_info"
                                            class="text-danger fw-bold">-</span></span>
                                    <input type="hidden" id="Trx_NettPrice" name="Trx_NettPrice" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Catatan</label>
                                <div class="col-sm-10">
                                    <textarea name="Trx_NoteDetail" id="Trx_NoteDetail" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="dtlSave" type="button" class="btn btn-primary">
                                <i class="fas fa-check"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function() {
            // Ambil headerId dari backend (diberikan oleh controller)
            const headerId = {{ $header->Trx_Auto }};
            let editMode = false,
                currentDetailId = null;

            // Hitung otomatis harga bersih di modal
            $('.calc-trigger').on('input', calculateNettPrice);

            function calculateNettPrice() {
                const qty = parseFloat($('#Trx_QtyTrx').val()) || 0;
                const grossPrice = parseFloat($('#Trx_GrossPrice').val()) || 0;
                const discountPercent = parseFloat($('#Trx_Discount').val()) || 0;
                const taxesPercent = parseFloat($('#Trx_Taxes').val()) || 0;

                // Hitung subtotal (harga * qty)
                const subtotal = qty * grossPrice;

                // Terapkan persentase diskon dan pajak
                const discountAmount = subtotal * (discountPercent / 100);
                const afterDiscount = subtotal - discountAmount;
                const taxAmount = afterDiscount * (taxesPercent / 100);

                // Hitung harga akhir
                const total = afterDiscount + taxAmount;
                $('#Trx_NettPrice').val(total.toFixed(2));
            }

            // Inisialisasi dropdown satuan saat modal dibuka (hanya jika belum ada data)
            $('#dtlModal').on('shown.bs.modal', function() {
                // Hanya load jika dropdown masih kosong dan bukan mode edit
                if ($('#trx_uom option').length <= 1 && !editMode) {
                    loadUomOptions();
                }
            });

            // Reset editMode saat modal ditutup
            $('#dtlModal').on('hidden.bs.modal', function() {
                editMode = false;
            });

            // Load UOM options untuk dropdown
            function loadUomOptions(selectedValue = null, callback = null) {
                $.ajax({
                    url: '{{ route('retur.pembelian.uom-options') }}',
                    method: 'GET',
                    data: {
                        selected: selectedValue
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#trx_uom').empty().append('<option value="">Pilih Satuan</option>');
                            response.data.forEach(function(uom) {
                                // Gunakan UOM_Auto sebagai value dan UOM_Code sebagai display
                                const isSelected = selectedValue && selectedValue == uom.UOM_Auto ? 'selected' : '';
                                $('#trx_uom').append('<option value="' + uom.UOM_Auto + '" ' + isSelected + '>' +
                                    uom.UOM_Code + '</option>');
                            });
                            
                            // Jika ada selectedValue, set sebagai nilai terpilih
                            if (selectedValue) {
                                $('#trx_uom').val(selectedValue);
                                // Trigger change event untuk memastikan nilai ter-set
                                $('#trx_uom').trigger('change');
                            }
                            
                            // Callback jika ada
                            if (callback && typeof callback === 'function') {
                                callback();
                            }
                        }
                    },
                    error: function() {
                        console.error('Gagal memuat data satuan');
                    }
                });
            }

            // Helper function untuk mencari UOM_Auto berdasarkan UOM_Code
            function findUomAutoByCode(uomCode, callback) {
                $.ajax({
                    url: '{{ route('retur.pembelian.uom-options') }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const uom = response.data.find(item => item.UOM_Code === uomCode);
                            callback(uom ? uom.UOM_Auto : null);
                        } else {
                            callback(null);
                        }
                    },
                    error: function() {
                        callback(null);
                    }
                });
            }

            // Autocomplete produk
            $('#Trx_ProdCode').on('blur', function() {
                const kodeInput = $(this);
                const kode = kodeInput.val().trim();

                if (!kode) {
                    clearProductFields();
                    return;
                }

                // Tampilkan loading
                $('#trx_prodname').val('Loading...');
                $('#stock_info').text('Loading...');

                $.ajax({
                    url: '{{ route('retur.pembelian.product-data') }}',
                    method: 'GET',
                    data: {
                        kode_produk: kode
                    },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            $('#trx_prodname').val(data.nama_produk);
                            $('#Trx_GrossPrice').val(data.harga_beli);
                            $('#stock_info').text(data.qty + ' ' + data.uom_code);

                            // Set default UOM jika ada
                            if (data.uom_code) {
                                // Cari UOM_Auto berdasarkan UOM_Code dari data produk
                                findUomAutoByCode(data.uom_code, function(uomAuto) {
                                    if (uomAuto) {
                                        // Load UOM options dengan UOM_Auto sebagai nilai default
                                        loadUomOptions(uomAuto, function() {
                                            // Focus ke qty input setelah UOM ter-load
                                            $('#Trx_QtyTrx').focus();
                                        });
                                    } else {
                                        // Jika tidak ditemukan, load dropdown kosong
                                        loadUomOptions(null, function() {
                                            $('#Trx_QtyTrx').focus();
                                        });
                                    }
                                });
                            } else {
                                // Focus ke qty input jika tidak ada UOM default
                                $('#Trx_QtyTrx').focus();
                            }
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            Swal.fire('Produk Tidak Ditemukan', 'Kode produk "' + kode +
                                '" tidak ditemukan dalam database.', 'warning');
                        } else {
                            Swal.fire('Error', 'Terjadi kesalahan saat mengambil data produk.',
                                'error');
                        }
                        clearProductFields();
                    }
                });
            });

            function clearProductFields() {
                $('#trx_prodname').val('');
                $('#Trx_GrossPrice').val('');
                $('#stock_info').text('-');
                $('#trx_uom').val('');
            }

            // Inisialisasi Select2 untuk dropdown supplier
            $('#Trx_SupCode').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih Supplier',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('retur.pembelian.suppliers') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 10) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });

            // Inisialisasi Select2 untuk dropdown warehouse
            $('#Trx_WareCode').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih Gudang',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('retur.pembelian.warehouses') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 10) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });

            // Pembaruan Header yang Dibatasi - untuk menghindari panggilan AJAX yang terlalu sering
            let headerUpdateTimer;
            $('#headerForm').on('change', 'input, textarea, select', function() {
                clearTimeout(headerUpdateTimer);
                headerUpdateTimer = setTimeout(updateHeader, 500);
            });

            function updateHeader() {
                if (!validateHeaderForm()) {
                    return;
                }

                const data = $('#headerForm').serialize();
                $.ajax({
                    url: `/retur/pembelian/${headerId}`,
                    type: 'PUT',
                    data: data,
                    success: function(response) {},
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal memperbarui header: ' + xhr.responseText, 'error');
                    }
                });
            }

            // Validasi formulir header
            function validateHeaderForm() {
                let isValid = true;
                $('#headerForm [required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                return isValid;
            }

            // Inisialisasi DataTable DETAIL
            const detailTable = $('#dataTable').DataTable({
                ajax: {
                    url: `/retur/pembelian/${headerId}/details`,
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'Trx_ProdCode'
                    },
                    {
                        data: 'trx_prodname'
                    },
                    {
                        data: 'Trx_QtyTrx',
                        render: function(data) {
                            return parseFloat(data).toFixed(2).replace('.', ',');
                        }
                    },
                    {
                        data: 'uom',
                        render: function(data, type, row) {
                            return data && data.UOM_Code ? data.UOM_Code : (row.trx_uom || '-');
                        }
                    },
                    {
                        data: 'Trx_GrossPrice',
                        render: function(data) {
                            return parseFloat(data).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'Trx_Discount',
                        render: function(data) {
                            return parseFloat(data).toFixed(2).replace('.', ',');
                        }
                    },
                    {
                        data: 'Trx_Taxes',
                        render: function(data) {
                            return parseFloat(data).toFixed(2).replace('.', ',');
                        }
                    },
                    {
                        data: 'Trx_NettPrice',
                        render: function(data) {
                            return parseFloat(data).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'Trx_Note'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            let buttons = '';

                            @can('ubah', $currentMenuSlug)
                                buttons += `<button class="btn btn-sm btn-warning edit-btn mb-1 mr-1" data-id="${row.Trx_Auto || row.trx_number_dtl}">
                                <i class="fas fa-edit"></i>
                            </button>`;
                            @endcan

                            @can('hapus', $currentMenuSlug)
                                buttons += `<button class="btn btn-sm btn-danger delete-btn mb-1 mr-1" data-id="${row.Trx_Auto || row.trx_number_dtl}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                            @endcan

                            return buttons;
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                drawCallback: function() {
                    // Mengaktifkan/menonaktifkan tombol berdasarkan apakah ada data
                    const hasRecords = this.api().data().length > 0;
                    $('#btnPublish').prop('disabled', !hasRecords);
                }
            });

            // Event handler untuk clear selection
            $('#Trx_SupCode').on('select2:clear', function(e) {
                $(this).val('').trigger('change');
            });

            $('#Trx_WareCode').on('select2:clear', function(e) {
                $(this).val('').trigger('change');
            });

            // Tambah DETAIL (buka modal kosong)
            $('#addCodeButton').click(function() {
                // Periksa apakah header valid terlebih dahulu
                if (!validateHeaderForm()) {
                    Swal.fire('Perhatian', 'Harap lengkapi data header terlebih dahulu', 'warning');
                    return;
                }

                // Perbarui header terlebih dahulu untuk memastikan disimpan
                updateHeader();

                // Reset form and buka modal
                editMode = false;
                currentDetailId = null;
                $('#dtlForm')[0].reset();

                // Atur nilai default
                $('#Trx_QtyTrx').val(1);
                $('#Trx_GrossPrice').val(0);
                $('#Trx_Discount').val(0);
                $('#Trx_Taxes').val(0);
                $('#Trx_NettPrice').val(0);

                // Load UOM options untuk form baru dan buka modal setelah selesai
                loadUomOptions(null, function() {
                    $('#dtlModal').modal('show');
                });
            });

            // Simpan atau Update DETAIL
            $('#dtlSave').click(async function() {
                // Validasi form detail
                if (!validateDetailForm()) {
                    return;
                }

                calculateNettPrice(); // Hitung sekali lagi untuk memastikan harga bersih sudah benar

                const payload = $('#dtlForm').serialize();

                try {
                    if (editMode) {
                        // UPDATE
                        await $.ajax({
                            url: `/retur/pembelian/${headerId}/details/${currentDetailId}`,
                            type: 'PUT',
                            data: payload
                        });
                    } else {
                        // CREATE
                        await $.post(
                            `/retur/pembelian/${headerId}/details`,
                            payload
                        );
                    }
                } catch (e) {
                    return Swal.fire('Error', 'Gagal simpan detail: ' + (e.responseText ||
                        'Unknown error'), 'error');
                }

                $('#dtlModal').modal('hide');
                detailTable.ajax.reload(null, false);
                editMode = false;
                currentDetailId = null;

                // Pesan sukses dengan penutupan otomatis
                Swal.fire({
                    title: 'Berhasil',
                    text: 'Item berhasil disimpan',
                    icon: 'success',
                    timer: 5000,
                    showConfirmButton: false
                })
            });

            // Validasi formulir detail
            function validateDetailForm() {
                let isValid = true;
                $('#dtlForm [required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Validasi tambahan untuk kolom numerik
                if (parseFloat($('#Trx_QtyTrx').val()) <= 0) {
                    $('#Trx_QtyTrx').addClass('is-invalid');
                    isValid = false;
                }

                if (parseFloat($('#Trx_GrossPrice').val()) <= 0) {
                    $('#Trx_GrossPrice').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    Swal.fire('Perhatian', 'Harap lengkapi semua data yang diperlukan', 'warning');
                }

                return isValid;
            }

            // Prefill EDIT DETAIL
            $('#dataTable').on('click', '.edit-btn', function() {
                editMode = true;
                currentDetailId = $(this).data('id');

                // Ambil data row
                const rowIdx = detailTable.row($(this).closest('tr')).index();
                const row = detailTable.row(rowIdx).data();

                // Atur ulang formulir terlebih dahulu
                $('#dtlForm')[0].reset();

                // Isi kolom formulir
                $('#Trx_ProdCode').val(row.Trx_ProdCode);
                $('#trx_prodname').val(row.trx_prodname);
                $('#Trx_QtyTrx').val(row.Trx_QtyTrx);
                $('#Trx_GrossPrice').val(row.Trx_GrossPrice);
                $('#Trx_Discount').val(row.Trx_Discount);
                $('#Trx_Taxes').val(row.Trx_Taxes);
                $('#Trx_NettPrice').val(row.Trx_NettPrice);
                $('#Trx_NoteDetail').val(row.Trx_Note);
                
                // Load UOM options dengan nilai yang sudah ada dan buka modal setelah selesai
                loadUomOptions(row.trx_uom, function() {
                    $('#dtlModal').modal('show');
                });
            });

            // HAPUS DETAIL
            $('#dataTable').on('click', '.delete-btn', function() {
                const detailId = $(this).data('id');
                Swal.fire({
                    title: 'Yakin hapus item?',
                    text: "Item yang dihapus tidak dapat dikembalikan",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/retur/pembelian/${headerId}/details/${detailId}`,
                        type: 'DELETE'
                    }).done(() => {
                        detailTable.ajax.reload(null, false);

                        Swal.fire({
                            title: 'Berhasil',
                            text: 'Item berhasil dihapus',
                            icon: 'success',
                            timer: 5000,
                            showConfirmButton: false
                        });
                    }).fail((xhr) => {
                        Swal.fire('Error', 'Gagal menghapus item: ' + xhr.responseText,
                            'error');
                    });
                });
            });

            // BATALKAN DRAFT (hapus header & cascade detail)
            $('#btnCancelDraft').click(function() {
                Swal.fire({
                    title: 'Batalkan seluruh draft?',
                    text: "Semua data yang sudah diinput akan dihapus",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, batalkan!',
                    cancelButtonText: 'Tidak'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/retur/pembelian/${headerId}`,
                        type: 'DELETE'
                    }).done(() => {
                        Swal.fire({
                            title: 'Berhasil',
                            text: 'Draft telah dibatalkan',
                            icon: 'success',
                            timer: 5000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href =
                                '{{ route('retur.pembelian.index') }}';
                        });
                    }).fail((xhr) => {
                        Swal.fire('Error', 'Gagal membatalkan draft: ' + xhr.responseText,
                            'error');
                    });
                });
            });

            // PUBLISH DRAFT (ubah posting ke F)
            $('#btnPublish').click(function() {
                // Periksa apakah ada setidaknya satu item
                if (detailTable.data().count() === 0) {
                    Swal.fire('Perhatian',
                        'Tidak dapat menyimpan draft kosong. Tambahkan minimal satu item.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Simpan Retur Pembelian?',
                    text: "Pastikan data sudah benar",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, simpan!',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/retur/pembelian/${headerId}/publish`,
                        type: 'PUT'
                    }).done(() => {
                        Swal.fire({
                            title: 'Berhasil',
                            text: 'Retur Pembelian telah disimpan',
                            icon: 'success',
                            timer: 5000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href =
                                '{{ route('retur.pembelian.index') }}';
                        });
                    }).fail((xhr) => {
                        Swal.fire('Error', xhr.responseJSON?.message ||
                            'Gagal menyimpan draft', 'error');
                    });
                });
            });

            // Periksa apakah memiliki rincian saat halaman dimuat untuk mengaktifkan/mematikan tombol
            $.get(`/retur/pembelian/${headerId}/details`, function(response) {
                const hasDetails = response.data && response.data.length > 0;
                $('#btnPublish').prop('disabled', !hasDetails);
            });
        });
    </script>
@endpush
