<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Presensi\Employee; // Import model Employee
use App\Models\Presensi\LeaveRequest; // Import model LeaveRequest
use Illuminate\Support\Carbon;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus data lama untuk menghindari duplikasi saat seeding ulang
        LeaveRequest::truncate();

        // Ambil 3 karyawan pertama sebagai contoh
        $employees = Employee::take(3)->get();

        if ($employees->count() < 3) {
            $this->command->info('Tidak cukup data karyawan untuk membuat seeder izin.');
            return;
        }

        $requests = [
            // Pengajuan yang masih PENDING
            [
                'employee_id' => $employees[0]->emp_Auto,
                'start_date' => Carbon::today()->addDays(2),
                'end_date' => Carbon::today()->addDays(2),
                'type' => 'Izin',
                'reason' => 'Ada keperluan keluarga mendadak.',
                'attachment_path' => null, // PERBAIKAN: Tambahkan key ini dengan nilai null
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Pengajuan yang sudah APPROVED
            [
                'employee_id' => $employees[1]->emp_Auto,
                'start_date' => Carbon::today()->subDays(5),
                'end_date' => Carbon::today()->subDays(4),
                'type' => 'Sakit',
                'reason' => 'Sakit demam, surat dokter terlampir.',
                'attachment_path' => 'contoh_surat_dokter.pdf', // Nama file contoh
                'status' => 'approved',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
            // Pengajuan yang di-REJECTED
            [
                'employee_id' => $employees[2]->emp_Auto,
                'start_date' => Carbon::today()->addWeek(),
                'end_date' => Carbon::today()->addWeek()->addDay(),
                'type' => 'Cuti',
                'reason' => 'Ingin liburan.',
                'attachment_path' => null, // PERBAIKAN: Tambahkan key ini dengan nilai null
                'status' => 'rejected',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ];

        DB::table('leave_requests')->insert($requests);
    }
}
