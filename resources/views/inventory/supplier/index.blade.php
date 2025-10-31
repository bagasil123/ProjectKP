@extends('layouts.admin')

@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
@endphp

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Data Supplier</h1>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <!-- Tombol Tambah dengan Permission Check -->
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
        @endphp
        
        @can('tambah', $currentMenuSlug)
            <button type="button" class="btn btn-primary" id="btnAddSupplier">
                <i class="fas fa-plus"></i> Tambah Supplier
            </button>
        @endcan
    </div>
    
    <!-- Tabel data -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="2%" class="text-center align-middle">No</th>
                            <th width="10%" class="text-center align-middle">Kode</th>
                            <th width="20%" class="text-center align-middle">Nama Supplier</th>
                            <th width="20%" class="text-center align-middle">Alamat</th>
                            <th width="10%" class="text-center align-middle">Contact</th>
                            <th width="10%" class="text-center align-middle">Telp</th>
                            <th width="15%" class="text-center align-middle">Email</th>
                            <th width="9%" class="text-center align-middle">Tanggal</th>
                            <th width="4%" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $index => $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $supplier->kode_supplier }}</td>
                                <td>{{ $supplier->nama_supplier }}</td>
                                <td>{{ $supplier->alamat }}</td>
                                <td>{{ $supplier->contact_person }}</td>
                                <td>{{ $supplier->telp }}</td>
                                <td>{{ $supplier->email }}</td>
                                <td>{{ $supplier->tanggal->format('d M Y') }}</td>
                                <td class="text-center">
                                    @php
                                        $currentRouteName = Route::currentRouteName();
                                        $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
                                    @endphp
                                    
                                    @can('ubah', $currentMenuSlug)
                                    <button class="btn btn-sm btn-warning edit-btn"
                                        data-id="{{ $supplier->id }}"
                                        data-kode="{{ $supplier->kode_supplier }}"
                                        data-nama="{{ $supplier->nama_supplier }}"
                                        data-alamat="{{ $supplier->alamat }}"
                                        data-contact="{{ $supplier->contact_person }}"
                                        data-telp="{{ $supplier->telp }}"
                                        data-email="{{ $supplier->email }}"
                                        data-tanggal="{{ $supplier->tanggal->format('Y-m-d') }}"
                                        data-cara_bayar_id="{{ $supplier->cara_bayar_id }}"
                                        data-lama_bayar="{{ $supplier->lama_bayar }}"
                                        data-potongan="{{ $supplier->potongan }}"
                                        title="Edit Supplier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    
                                    @can('hapus', $currentMenuSlug)
                                    <button class="btn btn-sm btn-danger delete-btn"
                                        data-id="{{ $supplier->id }}"
                                        data-nama="{{ $supplier->nama_supplier }}"
                                        title="Hapus Supplier">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data supplier</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Universal Modal for Add/Edit -->
    <div class="modal fade" id="universalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="mainForm" method="POST" class="modal-content"> 
                @csrf
                <input type="hidden" id="id" name="id">
                <input type="hidden" name="_method" value="POST">
                
                <div class="modal-header py-2">
                    <h5 class="modal-title h6" id="modalTitle">Tambah Supplier</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body p-2">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-6">
                            <div class="form-group mb-2">
                                <label class="small mb-0">Kode Supplier <span class="text-danger">*</span></label>
                                <input type="text" id="kode_supplier" name="kode_supplier" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label class="small mb-0">Telp <span class="text-danger">*</span></label>
                                <input type="text" id="telp" name="telp" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label class="small mb-0">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" id="tanggal" name="tanggal" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="col-6">
                            <div class="form-group mb-2">
                                <label class="small mb-0">Nama Supplier <span class="text-danger">*</span></label>
                                <input type="text" id="nama_supplier" name="nama_supplier" class="form-control form-control-sm" required>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label class="small mb-0">Email</label>
                                <input type="email" id="email" name="email" class="form-control form-control-sm">
                            </div>
                            
                            <div class="form-group mb-2">
                                <label class="small mb-0">Contact Person <span class="text-danger">*</span></label>
                                <input type="text" id="contact_person" name="contact_person" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        
                        <!-- Full Width -->
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="small mb-0">Alamat <span class="text-danger">*</span></label>
                                <textarea id="alamat" name="alamat" class="form-control form-control-sm" rows="2" required></textarea>
                            </div>
                        </div>
                        
                        <!-- Payment Section -->
                        <div class="col-4 mt-3">
                            <div class="form-group mb-2">
                                <label class="small mb-0">Cara Bayar <span class="text-danger">*</span></label>
                                <select id="cara_bayar_id" name="cara_bayar_id" class="form-control form-control-sm" required>
                                    <option value="">Pilih Cara Bayar</option>
                                    @foreach($caraBayarOptions as $cara)
                                        <option value="{{ $cara->id }}">{{ $cara->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-4 mt-3">
                            <div class="form-group mb-2">
                                <label class="small mb-0">Lama Bayar (hari)</label>
                                <input type="number" id="lama_bayar" name="lama_bayar" class="form-control form-control-sm" min="0" placeholder="0">
                            </div>
                        </div>
                        
                        <div class="col-4 mt-3">
                            <div class="form-group mb-2">
                                <label class="small mb-0">Potongan (%)</label>
                                <input type="number" id="potongan" name="potongan" class="form-control form-control-sm" min="0" max="100" step="0.01" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" id="modalSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    #dataTable th {
        text-align: center;
    }
    
    #dataTable td:nth-child(1),
    #dataTable td:nth-child(2),
    #dataTable td:nth-child(5),
    #dataTable td:nth-child(6),
    #dataTable td:nth-child(8),
    #dataTable td:nth-child(9) {
        text-align: center;
    }
    
    #dataTable td:nth-child(4) {
        white-space: normal !important;
        word-wrap: break-word;
    }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    const modalEl = document.getElementById('universalModal');
    const modalInstance = new bootstrap.Modal(modalEl);
    const form = $('#mainForm');
    const baseInventoryUrl = "{{ url('inventory') }}";

    // Initialize DataTable
    $('#dataTable').DataTable();
    
    // Reset & Open Add Modal
    $('#btnAddSupplier').click(function() {
        form.trigger('reset');
        $('#modalTitle').text('Tambah Supplier Baru');
        $('#modalSubmit').text('Simpan');
        form.attr('action', `${baseInventoryUrl}/supplier`);
        form.find('input[name="_method"]').val('POST');
        $('#id').val('');
        modalInstance.show();
    });

    // Open Edit Modal
    $('#dataTable').on('click', '.edit-btn', function() {
        const btn = $(this);
        const id = btn.data('id');

        $('#modalTitle').text('Edit Supplier');
        $('#modalSubmit').text('Simpan Perubahan');
        form.attr('action', `${baseInventoryUrl}/supplier/${id}`);
        form.find('input[name="_method"]').val('PUT');

        // Populate form fields
        $('#id').val(id);
        $('#kode_supplier').val(btn.data('kode'));
        $('#nama_supplier').val(btn.data('nama'));
        $('#alamat').val(btn.data('alamat'));
        $('#contact_person').val(btn.data('contact'));
        $('#telp').val(btn.data('telp'));
        $('#email').val(btn.data('email'));
        $('#tanggal').val(btn.data('tanggal'));
        $('#cara_bayar_id').val(btn.data('cara_bayar_id'));
        $('#lama_bayar').val(btn.data('lama_bayar'));
        $('#potongan').val(btn.data('potongan'));

        modalInstance.show();
    });

    // Submit Form with AJAX
    form.on('submit', function(e) {
        e.preventDefault();
        const token = $('meta[name="csrf-token"]').attr('content');
        const formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                modalInstance.hide();
                Swal.fire('Berhasil', response.message || 'Supplier berhasil disimpan', 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let messages = '';
                    Object.values(errors).forEach(arr => arr.forEach(msg => messages += msg + '<br>'));
                    Swal.fire('Error', messages, 'error');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    Swal.fire('Error', xhr.responseJSON.message, 'error');
                } else {
                    Swal.fire('Error', 'Terjadi kesalahan pada server. Status: ' + xhr.status, 'error');
                }
            }
        });
    });

    // Delete Confirmation
    $('#dataTable').on('click', '.delete-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        const nama = btn.data('nama');
        const row = btn.closest('tr');
        const token = $('meta[name="csrf-token"]').attr('content');

        Swal.fire({
            title: 'Hapus Supplier?',
            html: `Yakin ingin menghapus supplier <strong>${nama}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseInventoryUrl}/supplier/${id}`,
                    method: 'POST',
                    data: {
                        _token: token,
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        if(response.success) {
                            row.fadeOut(400, function() {
                                row.remove();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    html: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan pada server';
                        
                        if (xhr.status === 404) {
                            message = 'Data supplier tidak ditemukan';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: message,
                            timer: 3000,
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush