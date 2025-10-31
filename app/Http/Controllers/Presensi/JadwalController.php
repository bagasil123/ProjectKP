<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use App\Models\Presensi\Jadwal;
use App\Models\Presensi\Employee;
use App\Models\Presensi\Divisi;
use App\Models\Presensi\Shift;
use App\Models\Presensi\LiburNasional;
use App\Models\Presensi\OfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function index()
    {
        $divisi = Divisi::orderBy('Div_Name')->get();
        $shifts = Shift::orderBy('shift_code')->get();
        $officeLocations = OfficeLocation::all();
        $holidays = LiburNasional::orderBy('tanggal', 'desc')->get(); // Diurutkan agar lebih rapi
        return view('presensi.jadwal.index', compact('divisi', 'shifts', 'holidays', 'officeLocations'));
    }

    public function fetchJadwal(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
            'divisi_id' => 'required|integer|exists:ts_div,div_auto',
        ]);

        $employees = Employee::where('emp_DivCode', $request->divisi_id)->orderBy('emp_Name')->get();
        $employeeIds = $employees->pluck('emp_Auto');

        $schedules = Jadwal::where('tmp_periode', $request->periode)
            ->whereIn('TMP_emp', $employeeIds)
            ->get()
            ->groupBy('TMP_emp');

        return response()->json([
            'schedules' => $schedules,
            'employees' => $employees,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:m_employee,emp_Auto',
            'default_work_shift' => 'required|string|exists:m_shift,shift_code', // Validasi shift kerja
            'default_holiday_shift' => 'required|string|exists:m_shift,shift_code', // Validasi shift libur
            'location_id' => 'required|integer|exists:m_officeloc,id', 

        ]);

        $periode = Carbon::createFromFormat('Y-m', $request->periode);
        $daysInMonth = $periode->daysInMonth;
        $employees = Employee::whereIn('emp_Auto', $request->employee_ids)->with(['divisi', 'posisi'])->get();
        $masterShifts = Shift::all()->keyBy('shift_code');
        
        $officeLocation = OfficeLocation::find($request->location_id);
        if (!$officeLocation) {
            return response()->json(['message' => 'Lokasi kantor tidak valid.'], 422);
        }

        $nationalHolidays = LiburNasional::whereMonth('tanggal', $periode->month)
                            ->whereYear('tanggal', $periode->year)
                            ->pluck('tanggal')
                            ->map(fn($date) => Carbon::parse($date)->toDateString())
                            ->toArray();

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = $periode->copy()->setDay($day);
                    
                    // Tentukan kode shift berdasarkan pilihan user
                    $shiftCode = ($date->isWeekend() || in_array($date->toDateString(), $nationalHolidays)) 
                                 ? $request->default_holiday_shift 
                                 : $request->default_work_shift;
                    
                    $shiftData = $masterShifts->get($shiftCode);
                    $jamIn = $shiftData->jam_in ?? '00:00:00';
                    $jamOut = $shiftData->jam_out ?? '00:00:00';

                    Jadwal::updateOrCreate(
                        ['tmp_periode' => $request->periode, 'TMP_emp' => $employee->emp_Auto, 'emp_tgl' => str_pad($day, 2, '0', STR_PAD_LEFT)],
                        [
                            'TMP_CODE' => $employee->emp_Code, 'TMP_NAME' => $employee->emp_Name,
                            'DEPARTEMEN' => optional($employee->divisi)->Div_Name ?? '',
                            'posisi' => optional($employee->posisi)->Pos_Name ?? '',
                            'shift_code' => $shiftCode, 
                            'jam_in' => $jamIn, 
                            'jam_out' => $jamOut,
                            'latitude' => $officeLocation->latitude,
                            'longitude' => $officeLocation->longitude,
                            'tmp_user' => auth()->id(), 
                            'tmp_date' => now(),
                        ]
                    );
                }
            }
            DB::commit();
            return response()->json(['message' => 'Jadwal untuk ' . count($employees) . ' karyawan berhasil digenerate.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat jadwal: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'shift_code' => 'required|string|max:10',
            'jam_in' => 'required|date_format:H:i',
            'jam_out' => 'required|date_format:H:i',
            'location_id' => 'required|integer|exists:m_officeloc,id', // Validasi input lokasi
        ]);

        $jadwal = Jadwal::findOrFail($id);
        $location = OfficeLocation::find($request->location_id);

        if (!$location) {
            return response()->json(['message' => 'Lokasi tidak ditemukan.'], 404);
        }

        // Update data jadwal dengan data baru
        $jadwal->update([
            'shift_code' => $request->shift_code,
            'jam_in' => $request->jam_in,
            'jam_out' => $request->jam_out,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ]);

        return response()->json([
            'message' => 'Jadwal berhasil diperbarui.',
            'updated_jadwal' => $jadwal,
        ]);
    }

    public function checkJadwal(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'tanggal' => 'required|date',
        ]);

        $date = Carbon::parse($request->tanggal);
        $schedule = Jadwal::where('TMP_emp', $request->employee_id)
                        ->where('tmp_periode', $date->format('Y-m'))
                        ->where('emp_tgl', $date->format('d'))
                        ->first();

        if (!$schedule) {
            return response()->json(['message' => 'Tidak ada jadwal kerja pada tanggal ini.'], 404);
        }

        if (in_array($schedule->shift_code, ['L', 'OFF'])) {
            return response()->json(['message' => 'Jadwal pada tanggal ini adalah hari libur.'], 404);
        }
        
        return response()->json([
            'shift_code' => $schedule->shift_code,
            'jam_in' => Carbon::parse($schedule->jam_in)->format('H:i'),
            'jam_out' => Carbon::parse($schedule->jam_out)->format('H:i'),
        ]);
    }
    
    /**
     * FUNGSI BARU: Menghapus seluruh jadwal untuk karyawan terpilih pada periode tertentu.
     */
    public function destroyJadwal(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
            'employee_id' => 'required|integer|exists:m_employee,emp_Auto',
        ]);

        Jadwal::where('tmp_periode', $request->periode)
              ->where('TMP_emp', $request->employee_id)
              ->delete();
              
        return response()->json(['success' => 'Seluruh jadwal karyawan pada periode ini telah dihapus.']);
    }
}
