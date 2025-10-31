@extends('layouts.admin')

@section('main-content')
@php
    $currentRouteName = Route::currentRouteName();
    $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
@endphp
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Persetujuan Izin Karyawan</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengajuan Izin Tertunda</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="approvalTable">
                    <thead>
                        <tr>
                            <th>Nama Karyawan</th>
                            <th>Jenis Izin</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allRequests as $request)
                        <tr>
                            <td>{{ $request->employee->emp_Name }}</td>
                            <td>{{ $request->type }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('d M Y') }}</td>
                            <td>
                                @if($request->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($request->status == 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($request->status == 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                {{-- Tombol Detail/Approval --}}
                                <button class="btn btn-info btn-sm btn-detail"
                                        data-toggle="modal"
                                        data-target="#approvalModal"
                                        data-id="{{ $request->id }}"
                                        data-status="{{ $request->status }}"
                                        data-name="{{ $request->employee->emp_Name }}"
                                        data-type="{{ $request->type }}"
                                        data-start_date="{{ \Carbon\Carbon::parse($request->start_date)->format('d M Y') }}"
                                        data-end_date="{{ \Carbon\Carbon::parse($request->end_date)->format('d M Y') }}"
                                        data-reason="{{ $request->reason }}"
                                        data-attachment="{{ $request->attachment_path ? asset('storage/leave_attachments/' . $request->attachment_path) : '' }}">
                                    <i class="fas fa-eye"></i> Detail
                                </button>

                                @can('hapus', $currentMenuSlug)
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-request" 
                                            data-id="{{ $request->id }}" 
                                            data-name="Pengajuan dari {{ $request->employee->emp_Name }}" 
                                            title="Hapus Permanen">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data pengajuan izin.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- PERBAIKAN: Modal Baru untuk Validasi Approval -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Detail Pengajuan Izin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th width="35%">Nama Karyawan</th>
                            <td id="modal_name"></td>
                        </tr>
                        <tr>
                            <th>Jenis Izin</th>
                            <td id="modal_type"></td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td id="modal_dates"></td>
                        </tr>
                        <tr>
                            <th>Alasan</th>
                            <td id="modal_reason" style="white-space: pre-wrap;"></td>
                        </tr>
                        <tr>
                            <th>Lampiran</th>
                            <td id="modal_attachment"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer justify-content-between">
                {{-- Bagian untuk status PENDING --}}
                <div id="footer-pending" style="display: none;">
                    <p class="mb-0 text-muted">Setujui izin ini?</p>
                    <div>
                        <form id="rejectForm" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak</button>
                        </form>
                        <form id="approveForm" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Ya, Setuju</button>
                        </form>
                    </div>
                </div>
                {{-- Bagian untuk status APPROVED --}}
                <div id="footer-approved" class="text-success" style="display: none;">
                    <i class="fas fa-check-circle"></i> <strong><span class="status-type"></span> telah disetujui.</strong>
                </div>
                {{-- Bagian untuk status REJECTED --}}
                <div id="footer-rejected" class="text-danger" style="display: none;">
                    <i class="fas fa-times-circle"></i> <strong><span class="status-type"></span> ditolak.</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#approvalTable').DataTable();

        $('body').on('click', '.btn-detail', function() {
            var request = $(this).data();
            $('#modal_name').text(request.name);
            $('#modal_type').text(request.type);
            $('#modal_dates').text(request.start_date + ' - ' + request.end_date);
            $('#modal_reason').text(request.reason);

            if (request.attachment) {
                $('#modal_attachment').html(`<a href="${request.attachment}" target="_blank">Lihat Dokumen</a>`);
            } else {
                $('#modal_attachment').text('-');
            }
            
            $('#footer-pending, #footer-approved, #footer-rejected').hide();

            if (request.status === 'pending') {
                // Atur action URL untuk form
                var approveUrl = `{{ url('presensi/leave-approvals') }}/${request.id}/approve`;
                var rejectUrl = `{{ url('presensi/leave-approvals') }}/${request.id}/reject`;
                $('#approveForm').attr('action', approveUrl);
                $('#rejectForm').attr('action', rejectUrl);
                // Tampilkan footer untuk aksi pending
                $('#footer-pending').show();
            } else if (request.status === 'approved') {
                $('#footer-approved .status-type').text(request.type);
                $('#footer-approved').show();
            } else if (request.status === 'rejected') {
                $('#footer-rejected .status-type').text(request.type);
                $('#footer-rejected').show();
            }

            var approveUrl = `{{ url('presensi/leave-approvals') }}/${request.id}/approve`;
            var rejectUrl = `{{ url('presensi/leave-approvals') }}/${request.id}/reject`;

            $('#approveForm').attr('action', approveUrl);
            $('#rejectForm').attr('action', rejectUrl);
        });

        // --- PERBAIKAN: Tambahkan event listener untuk tombol hapus ---
        $('body').on('click', '.btn-delete-request', function() {
            const id = $(this).data('id');
            const itemName = $(this).data('name');
            const deleteUrl = `{{ url('presensi/leave-approvals') }}/${id}`;
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

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
                                response.success || 'Data berhasil dihapus.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function(jqXHR) {
                            let errorMsg = 'Gagal menghapus data.';
                            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                errorMsg = jqXHR.responseJSON.message;
                            }
                            Swal.fire('Gagal!', errorMsg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
