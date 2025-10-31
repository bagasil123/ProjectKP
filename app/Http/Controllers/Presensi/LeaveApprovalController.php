<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use App\Models\Presensi\LeaveRequest;
use App\Models\Presensi\RealAbsensi;
use App\Models\Presensi\LiburNasional;
use App\Models\Presensi\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        $allRequests = LeaveRequest::with('employee')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('presensi.approval.index', compact('allRequests'));
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        DB::beginTransaction();
        try {
            $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
            $employee = $leaveRequest->employee;

            $nationalHolidays = LiburNasional::whereMonth('tanggal', Carbon::parse($leaveRequest->start_date)->month)
                                             ->whereYear('tanggal', Carbon::parse($leaveRequest->start_date)->year)
                                             ->pluck('tanggal')
                                             ->map(fn($date) => Carbon::parse($date)->toDateString())
                                             ->toArray();

            foreach ($period as $date) {
                if ($date->isWeekend() || in_array($date->toDateString(), $nationalHolidays)) {
                    continue;
                }

                RealAbsensi::updateOrCreate(
                    ['TS_EMP' => $employee->emp_Auto, 'TS_TANGGAL' => $date->toDateString()],
                    [
                        'TS_NAME' => $employee->emp_Name,
                        'TS_CODE' => $employee->emp_Code,
                        'TS_NIK' => $employee->emp_NID,
                        'TS_STATUS' => strtoupper($leaveRequest->type),
                        'TS_NOTE' => $leaveRequest->reason,
                        'TS_FILE_PENDUKUNG' => $leaveRequest->attachment_path,
                        'TS_ENTRYUSER' => auth()->id(),
                        'TS_ENTRYDATE' => now(),
                    ]
                );
            }

            Notification::create([
                'employee_id' => $leaveRequest->employee_id,
                'title' => 'Pengajuan Izin Disetujui',
                'message' => "Pengajuan {$leaveRequest->type} Anda untuk tanggal " . 
                             Carbon::parse($leaveRequest->start_date)->format('d M Y') . " telah disetujui.",
            ]);

            // PERBAIKAN: Ubah status menjadi 'approved'
            $leaveRequest->update(['status' => 'approved']);
            
            DB::commit();
            return back()->with('success', 'Pengajuan izin telah disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyetujui izin: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyetujui izin. Silakan cek log untuk detail.');
        }
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        try {
            Notification::create([
                'employee_id' => $leaveRequest->employee_id,
                'title' => 'Pengajuan Izin Ditolak',
                'message' => "Mohon maaf, pengajuan {$leaveRequest->type} Anda untuk tanggal " . 
                             Carbon::parse($leaveRequest->start_date)->format('d M Y') . " ditolak.",
            ]);
            
            // PERBAIKAN: Ubah status menjadi 'rejected'
            $leaveRequest->update(['status' => 'rejected']);

            return back()->with('success', 'Pengajuan izin telah ditolak.');
        } catch (\Exception $e) {
            Log::error('Gagal menolak izin: ' . $e->getMessage());
            return back()->with('error', 'Gagal menolak izin. Silakan cek log untuk detail.');
        }
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        try {
            if ($leaveRequest->attachment_path) {
                \Storage::disk('public')->delete('leave_attachments/' . $leaveRequest->attachment_path);
            }
            $leaveRequest->delete();
            return back()->with('success', 'Data pengajuan izin berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
