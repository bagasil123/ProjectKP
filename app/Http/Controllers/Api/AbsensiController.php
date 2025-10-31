<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi\RealAbsensi;
use App\Models\Presensi\Jadwal;
use App\Models\Presensi\Employee; // Gunakan model Employee Anda
use App\Models\Presensi\OfficeLocation;
use App\Models\Presensi\Shift;
use App\Models\Presensi\WorkdayMarker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk logging
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Exception; // Tambahkan ini untuk menangkap semua jenis error

class AbsensiController extends Controller
{
    /**
     * PENYESUAIAN: Endpoint untuk karyawan melakukan clock in.
     * Menggunakan Auth::user() untuk keamanan dan konsistensi.
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:5048',
        ]);

        try {
            $employee = Auth::user();
            $today = Carbon::today();
            $now = Carbon::now();

            // 1. Dapatkan jadwal untuk hari ini
            $schedule = Jadwal::where('TMP_emp', $employee->emp_Auto)
                              ->where('tmp_periode', $today->format('Y-m'))
                              ->where('emp_tgl', $today->format('d'))
                              ->first();

            if (!$schedule) {
                return response()->json(['message' => 'Anda tidak memiliki jadwal kerja untuk hari ini.'], 400);
            }

            // 2. Validasi Waktu Clock In
            $jamIn = Carbon::parse($today->toDateString() . ' ' . $schedule->jam_in);
            $windowStart = $jamIn->copy()->subHour(); // 1 jam sebelum jam masuk
            $windowEnd = $jamIn; // Tepat pada jam masuk

            if ($now->isBefore($windowStart)) {
                return response()->json(['message' => 'Clock in terlalu awal. Anda bisa clock in mulai jam ' . $windowStart->format('H:i')], 400);
            }
            if ($now->isAfter($windowEnd)) {
                return response()->json(['message' => 'Anda terlambat. Batas waktu clock in adalah jam ' . $windowEnd->format('H:i')], 400);
            }

            // 3. Validasi Lokasi Clock In
            if (is_null($schedule->latitude) || is_null($schedule->longitude)) {
                return response()->json(['message' => 'Lokasi shift untuk jadwal ini belum diatur oleh admin.'], 400);
            }

            $distance = $this->calculateDistance(
                $schedule->latitude,
                $schedule->longitude,
                $request->latitude,
                $request->longitude
            );

            if ($distance > 100) { // Jarak maksimal 100 meter
                return response()->json(['message' => 'Anda berada di luar jangkauan lokasi yang diizinkan. Jarak Anda: ' . round($distance) . ' meter dari lokasi.'], 400);
            }

            // 4. Cek apakah sudah pernah clock in
            if (RealAbsensi::where('TS_EMP', $employee->emp_Auto)->whereDate('TS_TANGGAL', $today)->exists()) {
                return response()->json(['message' => 'Anda sudah melakukan clock in hari ini.'], 409);
            }

            if (RealAbsensi::where('TS_EMP', $employee->emp_Auto)->whereDate('TS_TANGGAL', $today)->exists()) {
                return response()->json(['message' => 'Anda sudah melakukan clock in hari ini.'], 409);
            }

            // Cek apakah sudah ada penanda untuk hari ini
            $markerExists = WorkdayMarker::where('date', $today->toDateString())->exists();

            // Jika belum ada, buat penanda baru menggunakan data pengguna pertama ini
            if (!$markerExists) {
                WorkdayMarker::create([
                    'employee_id' => $employee->emp_Auto,
                    'date' => $today->toDateString(),
                    'clock_in_time' => $now->toTimeString(),
                    'is_workday_flag' => true,
                ]);
            }
            
            // 5. Proses Clock In jika semua validasi berhasil
            $fotoPath = basename($request->file('selfie')->store('public/absensi_fotos'));

            RealAbsensi::create([
                'TS_EMP'        => $employee->emp_Auto,
                'TS_CODE'       => $employee->emp_Code,
                'TS_NIK'        => $employee->emp_NID,
                'TS_NAME'       => $employee->emp_Name,
                'TS_TANGGAL'    => $today->toDateString(),
                'TS_JAMIN'      => $now->toTimeString(),
                'TS_FOTO'       => $fotoPath,
                'TS_LATITUDE'   => $request->latitude,
                'TS_LONGITUDE'  => $request->longitude,
                'TS_STATUS'     => 'HADIR',
                'TS_NOTE'       => $request->notes,
                'TS_ENTRYDATE'  => $now,
                'TS_ENTRYUSER'  => $employee->emp_Code,
                'TS_ACTIVE'     => 'Y',
            ]);

            return response()->json(['message' => 'Clock in berhasil dicatat.'], 200);

        } catch (Exception $e) {
            Log::error('Clock In Gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server saat clock in.'], 500);
        }
    }

    /**
     * Fungsi helper untuk menghitung jarak antara dua titik GPS (Haversine formula).
     * @return float Jarak dalam meter.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Endpoint untuk karyawan melakukan clock out.
     * Route ini harus diproteksi dengan auth:sanctum.
     */
    public function clockOut(Request $request)
    {
        try {
            $employee = Auth::user();
            $today = Carbon::today();
            $now = Carbon::now();

            $schedule = Jadwal::where('TMP_emp', $employee->emp_Auto)
                              ->where('tmp_periode', $today->format('Y-m'))
                              ->where('emp_tgl', $today->format('d'))
                              ->first();

            if (!$schedule) {
                return response()->json(['message' => 'Jadwal kerja hari ini tidak ditemukan.'], 404);
            }

            $attendance = RealAbsensi::where('TS_EMP', $employee->emp_Auto)
                                      ->where('TS_TANGGAL', $today->toDateString())
                                      ->first();

            if (!$attendance) {
                return response()->json(['message' => 'Anda belum melakukan clock in hari ini.'], 404);
            }
            if ($attendance->TS_JAMOUT) {
                return response()->json(['message' => 'Anda sudah melakukan clock out hari ini.'], 409);
            }
            
            // Validasi Waktu Clock Out
            $jamOut = Carbon::parse($today->toDateString() . ' ' . $schedule->jam_out);
            $windowEnd = $jamOut->copy()->addHour(); // Batas akhir: 1 jam setelah jam pulang

            if ($now->isBefore($jamOut)) {
                return response()->json(['message' => 'Belum waktunya clock out. Anda bisa clock out mulai jam ' . $jamOut->format('H:i')], 400);
            }
            if ($now->isAfter($windowEnd)) {
                return response()->json(['message' => 'Waktu clock out Anda sudah terlewat dan akan diproses otomatis oleh sistem.'], 400);
            }

            // Update jam pulang
            $attendance->update([
                'TS_JAMOUT'     => $now->toTimeString(),
                'TS_UPDATEDATE' => $now,
                'TS_UPDATEUSER' => $employee->emp_Code,
            ]);

            return response()->json(['message' => 'Clock out berhasil dicatat.'], 200);

        } catch (Exception $e) {
            Log::error('Clock Out Gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server saat clock out.'], 500);
        }
    }

    /**
     * Mendapatkan status absensi karyawan untuk hari ini.
     * Berguna agar mobile app tahu tombol apa yang harus ditampilkan.
     * Route ini harus diproteksi dengan auth:sanctum.
     */
    public function getTodayStatus(Request $request)
    {
        try {
            $employee = Auth::user();
            $today = Carbon::today();
            
            $schedule = Jadwal::where('TMP_emp', $employee->emp_Auto)
                              ->where('tmp_periode', $today->format('Y-m'))
                              ->where('emp_tgl', $today->format('d'))
                              ->first();

            $attendance = RealAbsensi::where('TS_EMP', $employee->emp_Auto)
                                      ->whereDate('TS_TANGGAL', $today)
                                      ->first();

            // Inisialisasi variabel
            $shiftInfo = 'Jadwal tidak ditemukan';
            $locationInfo = 'Lokasi tidak diatur';
            $shiftLocationInfo = 'Lokasi shift tidak diatur';
            $status = 'belum_absen';
            $clockInTime = '';
            $clockOutTime = '';
            $canClockOut = false;
            $jamOutTime = '';

            if ($attendance && in_array(strtoupper($attendance->TS_STATUS), ['IZIN', 'SAKIT', 'CUTI', 'DISPENSASI'])) {
                // Jika hari ini statusnya adalah izin, langsung kirim respons khusus
                return response()->json([
                    'status' => 'on_leave',
                    'leave_info' => [
                        'type' => $attendance->TS_STATUS,
                        'reason' => $attendance->TS_NOTE,
                        // Untuk menampilkan rentang tanggal, diperlukan query tambahan yang kompleks.
                        // Untuk saat ini, kita kirim status hari ini saja.
                    ],
                    'shift_info' => 'Anda sedang tidak masuk kerja',
                    'location_info' => 'Anda sedang tidak masuk kerja',
                    'shift_location_info' => '',
                    'clock_in_time' => '',
                    'clock_out_time' => '',
                    'can_clock_out' => false,
                    'jam_out_time' => '',
                ]);
            }

            if ($schedule && in_array(strtoupper($schedule->shift_code), ['L', 'OFF'])) {
                return response()->json([
                    'status' => 'holiday', // Status baru untuk hari libur
                    'shift_info' => 'Hari Libur',
                    'location_info' => '-',
                    'shift_location_info' => '-',
                    'clock_in_time' => '',
                    'clock_out_time' => '',
                    'can_clock_out' => false,
                    'jam_out_time' => '',
                ]);
            }

            // Proses informasi Jadwal Shift
            if ($schedule) {
                $shiftData = Shift::where('shift_code', $schedule->shift_code)->first();
                $shiftName = $shiftData->shift_name ?? $schedule->shift_code;
                $jamIn = Carbon::parse($schedule->jam_in)->format('H:i');
                $jamOut = Carbon::parse($schedule->jam_out)->format('H:i');
                $shiftInfo = "$shiftName ($jamIn - $jamOut)";

                $scheduledJamOut = Carbon::parse($today->toDateString() . ' ' . $schedule->jam_out);
                $jamOutTime = $scheduledJamOut->format('H:i');
                if (Carbon::now()->isAfter($scheduledJamOut)) {
                    $canClockOut = true;
                }

                if ($schedule->latitude && $schedule->longitude) {
                    $lat = number_format((float)$schedule->latitude, 5);
                    $lng = number_format((float)$schedule->longitude, 5);
                    $shiftLocationInfo = "Lat: $lat, Lng: $lng";
                }
            }

            // Proses informasi Absensi
            if ($attendance) {
                // Jika data absensi ADA, tentukan status dan jam
                if (!$attendance->TS_JAMOUT) {
                    $status = 'sudah_masuk';
                    $clockInTime = Carbon::parse($attendance->TS_JAMIN)->format('H:i');
                } else {
                    $status = 'selesai';
                    $clockInTime = Carbon::parse($attendance->TS_JAMIN)->format('H:i');
                    $clockOutTime = Carbon::parse($attendance->TS_JAMOUT)->format('H:i');
                }

                // PERBAIKAN UTAMA: Gunakan nama kolom yang benar (TS_LATITUDE & TS_LONGITUDE)
                if ($attendance->TS_LATITUDE && $attendance->TS_LONGITUDE) {
                    $tolerance = 0.0001;
                    $officeLocation = OfficeLocation::whereBetween('latitude', [$attendance->TS_LATITUDE - $tolerance, $attendance->TS_LATITUDE + $tolerance])
                                                    ->whereBetween('longitude', [$attendance->TS_LONGITUDE - $tolerance, $attendance->TS_LONGITUDE + $tolerance])
                                                    ->first();
                    $locationInfo = $officeLocation 
                        ? $officeLocation->name 
                        : "Lat: " . number_format($attendance->TS_LATITUDE, 5) . ", Lng: " . number_format($attendance->TS_LONGITUDE, 5);
                } else {
                    // Jika data absensi ada tapi tidak ada koordinat
                    $locationInfo = 'Lokasi clock-in tidak tercatat';
                }
            }

            // Gabungkan semua informasi ke dalam satu respons
            $responseData = [
                'status' => $status,
                'shift_info' => $shiftInfo,
                'location_info' => $locationInfo,
                'shift_location_info' => $shiftLocationInfo,
                'clock_in_time' => $clockInTime,
                'clock_out_time' => $clockOutTime,
                'can_clock_out' => $canClockOut, 
                'jam_out_time' => $jamOutTime,
            ];

            return response()->json($responseData);

        } catch (Exception $e) {
            Log::error('Get Status Gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil status absensi.'], 500);
        }
    }


    public function getHistory(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        try {
            $employee = Auth::user();
            
            $query = RealAbsensi::where('TS_EMP', $employee->emp_Auto)
                                ->orderBy('TS_TANGGAL', 'desc');

            // Terapkan filter rentang tanggal jika ada
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('TS_TANGGAL', [$request->start_date, $request->end_date]);
            }

            $history = $query->get();

            return response()->json($history, 200);

        } catch (Exception $e) {
            Log::error('Get History Gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data histori.'], 500);
        }
    }
    
    public function getAttendanceOverview(Request $request)
    {
        try {
            $user = Auth::user();
            $startOfMonth = Carbon::now()->startOfMonth();
            $today = Carbon::today();

            // 1. Hitung total hari kerja yang sudah berlalu di bulan ini
            $totalWorkdays = WorkdayMarker::whereBetween('date', [$startOfMonth, $today])->count();

            // 2. Hitung total absensi per status untuk pengguna ini di bulan ini
            $attendanceSummary = RealAbsensi::where('TS_EMP', $user->emp_Auto)
                ->whereBetween('TS_TANGGAL', [$startOfMonth, $today])
                ->select('TS_STATUS', DB::raw('count(*) as total'))
                ->groupBy('TS_STATUS')
                ->pluck('total', 'TS_STATUS');

            $hadirCount = $attendanceSummary->get('HADIR', 0);
            $izinCount = $attendanceSummary->get('IZIN', 0);
            $sakitCount = $attendanceSummary->get('SAKIT', 0);
            $cutiCount = $attendanceSummary->get('CUTI', 0);
            
            // Total hari di mana ada catatan (selain alpa)
            $totalRecordedDays = $hadirCount + $izinCount + $sakitCount + $cutiCount;
            
            // Alpa adalah selisih antara total hari kerja dan total hari yang tercatat
            $alpaCount = max(0, $totalWorkdays - $totalRecordedDays);

            // 3. Hitung persentase
            $hadirPercent = ($totalWorkdays > 0) ? round(($hadirCount / $totalWorkdays) * 100) : 0;
            // Izin, Sakit, dan Cuti digabung menjadi satu kategori "Izin"
            $izinTotalCount = $izinCount + $sakitCount + $cutiCount;
            $izinPercent = ($totalWorkdays > 0) ? round(($izinTotalCount / $totalWorkdays) * 100) : 0;
            $alpaPercent = ($totalWorkdays > 0) ? round(($alpaCount / $totalWorkdays) * 100) : 0;

            return response()->json([
                'hadir_percent' => $hadirPercent,
                'izin_percent' => $izinPercent,
                'alpa_percent' => $alpaPercent,
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil overview absensi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data overview.'], 500);
        }
    }
    
    // ... method lainnya ...
}
