@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.');
@endphp

@section('main-content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-2 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
                <button class="btn btn-sm text-primary" type="button" data-toggle="collapse" data-target="#filterCollapse"
                    aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="filter_date_from" class="form-label">Dari Tanggal Retur</label>
                                <input type="date" class="form-control form-control-sm" id="filter_date_from"
                                    name="filter_date_from">
                            </div>
                            <div class="col-md-4">
                                <label for="filter_date_to" class="form-label">Sampai Tanggal Retur</label>
                                <input type="date" class="form-control form-control-sm" id="filter_date_to"
                                    name="filter_date_to">
                            </div>
                            <div class="col-md-4">
                                <label for="filter_sup_code" class="form-label">Kode Pelanggan</label>
                                <input type="text" class="form-control form-control-sm" id="filter_sup_code"
                                    name="filter_sup_code" placeholder="Masukkan kode pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" id="btn-apply-filter" class="btn btn-primary btn-sm mr-2">
                                    <i class="fas fa-filter"></i> Terapkan
                                </button>
                                <button type="button" id="btn-reset-filter" class="btn btn-secondary btn-sm mr-2">
                                    <i class="fas fa-times"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @can('tambah', $currentMenuSlug)
                    <a href="{{ route('retur.penjualan.create') }}" class="btn btn-primary btn-sm mr-2 mb-2 mb-md-0">
                        <i class="fas fa-plus"></i> Tambah Data
                    </a>
                @endcan
                <button id="btn-approve-all" class="btn btn-success btn-sm mr-2 mb-2 mb-md-0" disabled>
                    <i class="fas fa-check"></i> Setujui Semua
                </button>
                <button id="btn-print-all" class="btn btn-info btn-sm mb-2 mb-md-0">
                    <i class="fas fa-print"></i> Cetak Data
                </button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Retur Penjualan</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive pb-3">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width='5%'>Pelanggan</th>
                                <th width='7%'>NO</th>
                                <th width='13%'>Tgl Kembali</th>
                                <th width='10%'>Bruto</th>
                                <th width='10%'>Disc</th>
                                <th width='10%'>Pajak</th>
                                <th width='10%'>Netto</th>
                                <th width='5%'>Pengguna</th>
                                <th width='12%'>Tgl</th>
                                <th width='5%'>Disetujui</th>
                                <th width='13%'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- isi akan di-render via AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{!! route('retur.penjualan.data') !!}',
                    data: function(d) {
                        // Tambahkan parameter filter ke request
                        d.filter_date_from = $('#filter_date_from').val();
                        d.filter_date_to = $('#filter_date_to').val();
                        d.filter_sup_code = $('#filter_sup_code').val();
                    },
                    dataSrc: function(json) {
                        // Periksa apakah ada data dengan trx_posting = 'F'
                        const hasPendingApprovals = json.data.some(row => row.trx_posting === 'F');
                        // Enable/disable tombol setujui semua sesuai kebutuhan
                        $('#btn-approve-all').prop('disabled', !hasPendingApprovals);
                        return json.data;
                    }
                },
                columns: [{
                        data: 'Trx_SupCode',
                        name: 'Trx_SupCode'
                    },
                    {
                        data: 'trx_number',
                        name: 'trx_number'
                    },
                    {
                        data: 'Trx_Date',
                        name: 'Trx_Date'
                    },
                    {
                        data: 'Trx_GrossPrice',
                        name: 'Trx_GrossPrice'
                    },
                    {
                        data: 'Trx_TotDiscount',
                        name: 'Trx_TotDiscount'
                    },
                    {
                        data: 'Trx_Taxes',
                        name: 'Trx_Taxes'
                    },
                    {
                        data: 'Trx_NettPrice',
                        name: 'Trx_NettPrice'
                    },
                    {
                        data: 'Trx_UserID',
                        name: 'Trx_UserID'
                    },
                    {
                        data: 'Trx_LastUpdate',
                        name: 'Trx_LastUpdate'
                    },
                    {
                        data: 'trx_posting',
                        name: 'trx_posting',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            if (data === 'T') {
                                return '<span class="badge bg-success text-white">Sudah</span>';
                            }
                            return '<span class="badge bg-warning text-dark">Belum</span>';
                        }
                    },
                    {
                        data: 'Trx_Auto',
                        name: 'Trx_Auto',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            const editUrl = `/retur/penjualan/${data}/edit`;
                            const printUrl = `/retur/penjualan/${data}/print`;
                            const approveUrl = `/retur/penjualan/${data}/approve`;

                            // Disable tombol edit jika dokumen sudah disetujui (T)
                            const editBtnClass = row.trx_posting === 'T' ?
                                'btn-secondary disabled' : 'btn-warning';
                            const editBtnDisabled = row.trx_posting === 'T' ? 'disabled' : '';

                            // Disable approve button jika dokumen sudah disetujui (T)
                            const approveBtnClass = row.trx_posting === 'T' ?
                                'btn-secondary disabled' : 'btn-success';
                            const approveBtnDisabled = row.trx_posting === 'T' ? 'disabled' : '';

                            let buttons = '';

                            // Button Edit dengan permission check
                            @can('ubah', $currentMenuSlug)
                                buttons += `<a href="${editUrl}" class="btn btn-sm ${editBtnClass} mb-1 mr-1" title="Edit" ${editBtnDisabled}>
                                <i class="fas fa-edit"></i>
                            </a>`;
                            @endcan

                            // Button Approve
                            buttons += `<button data-id="${data}" class="btn btn-sm ${approveBtnClass} btn-approve mb-1 mr-1" title="Approve" ${approveBtnDisabled}>
                                <i class="fas fa-check"></i>
                            </button>`;

                            // Button Print
                            buttons += `<a href="${printUrl}" class="btn btn-sm btn-info mb-1 mr-1" title="Print" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>`;

                            return buttons;
                        }
                    },
                ],
                order: [
                    [1, 'desc']
                ],
            });

            // Inisialisasi Select2 untuk dropdown pelanggan
            $('#filter_sup_code').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih Pelanggan',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('retur.penjualan.customers') }}',
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

            // Event handler untuk update placeholder dengan selected value
            $('#filter_sup_code').on('select2:select', function(e) {
                var selectedData = e.params.data;
                // Update placeholder menjadi selected value
                $(this).data('select2').$container.find('.select2-selection__placeholder').text(selectedData
                    .text);
            });

            $('#filter_sup_code').on('select2:clear', function(e) {
                // Kembalikan ke placeholder asli saat di-clear
                $(this).data('select2').$container.find('.select2-selection__placeholder').text(
                    'Pilih Pelanggan');
            });

            $('#btn-apply-filter').on('click', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $('#btn-reset-filter').on('click', function(e) {
                e.preventDefault();
                $('#filterForm')[0].reset();
                $('#filter_sup_code').val(null).trigger('change');
                table.ajax.reload();
            });

            $('#filterForm input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btn-apply-filter').click();
                }
            });

            $('#btn-print-all').on('click', function(e) {
                e.preventDefault();
                var keyword = table.search();
                var url = '{{ route('retur.penjualan.printAll') }}';
                var params = [];

                // Tambahkan search keyword jika ada
                if (keyword) {
                    params.push('search=' + encodeURIComponent(keyword));
                }

                // Tambahkan parameter filter
                var filterDateFrom = $('#filter_date_from').val();
                var filterDateTo = $('#filter_date_to').val();
                var filterSupCode = $('#filter_sup_code').val();

                if (filterDateFrom) params.push('filter_date_from=' + encodeURIComponent(filterDateFrom));
                if (filterDateTo) params.push('filter_date_to=' + encodeURIComponent(filterDateTo));
                if (filterSupCode) params.push('filter_sup_code=' + encodeURIComponent(filterSupCode));

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                window.open(url, '_blank');
            });

            // Tangani klik button setujui semua
            $('#btn-approve-all').on('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah kamu yakin ingin menyetujui semua retur penjualan yang belum disetujui?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Setujui Semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('retur.penjualan.approveAll') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: `${response.count} retur penjualan berhasil disetujui.`,
                                        icon: 'success'
                                    });
                                    table.ajax.reload();
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: 'Terjadi kesalahan saat proses persetujuan.',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan saat menghubungi server.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });

            // Handle individu klik button setujui
            $(document).on('click', '.btn-approve', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah kamu yakin ingin menyetujui retur penjualan ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Setujui!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('retur/penjualan') }}/${id}/approve`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Retur penjualan berhasil disetujui.',
                                        icon: 'success'
                                    });
                                    table.ajax.reload();
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: response.message ||
                                            'Terjadi kesalahan saat proses persetujuan.',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
