<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Presensi\RealAbsensi;
use App\Models\Presensi\Jadwal;
use Illuminate\Support\Carbon;

class AutoClockOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-clock-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clocks out employees who missed their clock-out window.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai proses clock out otomatis...');

        $now = Carbon::now();
        
        // 1. Cari absensi yang sudah clock in hari ini tapi belum clock out
        $attendancesToProcess = RealAbsensi::whereDate('TS_TANGGAL', $now->toDateString())
                                           ->whereNotNull('TS_JAMIN')
                                           ->whereNull('TS_JAMOUT')
                                           ->get();

        $processedCount = 0;

        foreach ($attendancesToProcess as $attendance) {
            // 2. Dapatkan jadwal karyawan untuk hari itu
            $schedule = Jadwal::where('TMP_emp', $attendance->TS_EMP)
                              ->where('tmp_periode', $now->format('Y-m'))
                              ->where('emp_tgl', $now->format('d'))
                              ->first();

            if ($schedule) {
                $jamOut = Carbon::parse($now->toDateString() . ' ' . $schedule->jam_out);
                $deadline = $jamOut->copy()->addHour(); // Batas akhir adalah 1 jam setelah jam pulang

                // 3. Jika waktu saat ini sudah melewati batas akhir, proses clock out
                if ($now->isAfter($deadline)) {
                    $attendance->update([
                        'TS_JAMOUT' => $schedule->jam_out, // Clock out di jam pulang sesuai jadwal
                        'TS_NOTE' => ($attendance->TS_NOTE ? $attendance->TS_NOTE . ' | ' : '') . 'Auto Clock Out by System',
                        'TS_UPDATEDATE' => $now,
                        'TS_UPDATEUSER' => 'SYSTEM',
                    ]);
                    $processedCount++;
                    $this->info("Clock out otomatis untuk: {$attendance->TS_NAME}");
                }
            }
        }

        $this->info("Proses selesai. Total {$processedCount} karyawan diproses.");
        return 0;
    }
}
