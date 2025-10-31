<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi\Employee;
use App\Models\Presensi\RealAbsensi;
use App\Models\Presensi\Jadwal;
use App\Models\Presensi\Divisi;
use App\Models\Presensi\LiburNasional;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB; // Import DB Facade

class RekapController extends Controller
{
    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'divisi_id' => 'nullable|integer|exists:ts_div,div_auto',
            'gender' => 'nullable|string|in:M,F',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $period = CarbonPeriod::create($startDate, $endDate);
        $divisiName = 'Semua Divisi';

        // 1. Filter Karyawan
        $employeesQuery = Employee::with(['divisi', 'posisi']);
        if ($request->filled('divisi_id')) {
            $employeesQuery->where('emp_DivCode', $request->divisi_id);
            $divisi = Divisi::find($request->divisi_id);
            if ($divisi) {
                $divisiName = $divisi->Div_Name;
            }
        }
        if ($request->filled('gender')) {
            $employeesQuery->where('emp_Sex', $request->gender);
        }
        $employees = $employeesQuery->orderBy('emp_Name')->get();
        $employeeIds = $employees->pluck('emp_Auto');

        // 2. Ambil semua data relevan
        $absences = RealAbsensi::whereIn('TS_EMP', $employeeIds)
            ->whereBetween('TS_TANGGAL', [$startDate, $endDate])
            ->get()->groupBy('TS_EMP');

        // PERBAIKAN: Query jadwal yang lebih aman dan efisien
        $schedules = Jadwal::whereIn('TMP_emp', $employeeIds)
            ->where(function ($query) use ($startDate, $endDate) {
                $startPeriod = $startDate->format('Y-m');
                $endPeriod = $endDate->format('Y-m');
                $query->whereBetween('tmp_periode', [$startPeriod, $endPeriod]);
            })
            ->get()
            ->groupBy('TMP_emp');

        $nationalHolidays = LiburNasional::whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('tanggal')->map(fn($date) => Carbon::parse($date)->toDateString())->toArray();

        // 3. Bangun data rekap
        $rekapData = [];
        foreach ($employees as $employee) {
            $summary = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Cuti' => 0, 'Alpa' => 0];
            $totalWorkDays = 0;

            foreach ($period as $date) {
                $isHoliday = $date->isWeekend() || in_array($date->toDateString(), $nationalHolidays);
                
                if (!$isHoliday) {
                    $totalWorkDays++;
                }

                $employeeAbsence = $absences->get($employee->emp_Auto, collect())->firstWhere('TS_TANGGAL', $date->toDateString());
                
                if ($employeeAbsence) {
                    $status = strtoupper($employeeAbsence->TS_STATUS ?: 'HADIR');
                    if (array_key_exists(ucfirst(strtolower($status)), $summary)) {
                        $summary[ucfirst(strtolower($status))]++;
                    }
                } elseif (!$isHoliday) {
                    $summary['Alpa']++;
                }
            }
            
            $attendancePercentage = ($totalWorkDays > 0) 
                ? round(($summary['Hadir'] / $totalWorkDays) * 100) 
                : 0;

            $rekapData[] = [
                'employee' => $employee,
                'summary' => $summary,
                'attendance_percentage' => $attendancePercentage,
            ];
        }

        // 4. Tentukan output
        if ($request->has('print')) {
            $pdf = Pdf::loadView('presensi.absensi.rekap_pdf', [
                'rekapData' => $rekapData,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'divisiName' => $divisiName,
            ])->setPaper('a4', 'landscape');
            return $pdf->stream('laporan-rekapitulasi-absensi.pdf');
        }

        return response()->json($rekapData);
    }
}
