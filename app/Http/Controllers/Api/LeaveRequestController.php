<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:Izin,Sakit,Cuti',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|image|mimes:jpg,jpeg,png|max:5048',
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $employee = Auth::user();
            $file = $request->file('attachment');
            
            // PERBAIKAN: Buat nama file baru sesuai format
            $newFileName = $employee->emp_NID . '_' . Carbon::now()->format('dmY') . '.' . $file->getClientOriginalExtension();
            
            // Simpan file dengan nama baru
            $filePath = basename($file->storeAs('public/leave_attachments', $newFileName));
        }

        LeaveRequest::create([
            'employee_id' => Auth::id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'reason' => $request->reason,
            'attachment_path' => $filePath,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Pengajuan izin berhasil dikirim dan sedang menunggu persetujuan.'], 201);
    }
}
