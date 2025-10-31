<?php

namespace Database\Seeders; // Namespace untuk seeder

use Illuminate\Database\Seeder;
use App\Models\keamanan\Member;   // Import model Member
use App\Models\keamanan\Karyawan; // Import model Karyawan (PENTING: sumber data)
use App\Models\keamanan\Role;             // Import model Role (untuk mengaitkan role)
use App\Models\Presensi\Employee;
use Illuminate\Support\Facades\Hash; // Import Hash facade (PENTING: untuk meng-hash password)

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Metode ini akan dieksekusi saat seeder dijalankan.
     */
    public function run(): void
    {
        // 1. Mengambil Role yang Sudah Ada
        // Role-role ini harus sudah dibuat oleh RoleSeeder sebelumnya di DatabaseSeeder.
        $adminRole = Role::where('name', 'Admin')->first();

        // 2. Mengambil Semua Data Karyawan
        // Karyawan-karyawan ini harus sudah dibuat oleh KaryawanSeeder sebelumnya.
        $employees = Employee::all();

        // 3. Menentukan Daftar Role yang Tersedia untuk Alokasi Otomatis
        // Array ini berisi objek-objek role yang akan diberikan secara bergiliran kepada karyawan.
        // Jika ada lebih banyak karyawan daripada role di sini, role akan diulang.
        $rolesAvailable = [
            $adminRole,
        ];
        $roleIndex = 0; // Index untuk melacak role mana yang akan diberikan selanjutnya

        // 4. Loop Melalui Setiap Karyawan untuk Membuat Akun Member
        foreach ($employees as $employee) {
            // Menentukan role untuk karyawan saat ini berdasarkan urutan `$rolesAvailable`
            // `$roleIndex % count($rolesAvailable)` akan memastikan role berulang jika jumlah karyawan > jumlah role tersedia.
            $assignedRole = $rolesAvailable[$roleIndex % count($rolesAvailable)];

            // Memastikan role yang akan diberikan tidak null (yaitu, role tersebut benar-benar ditemukan di DB)
            if (!$assignedRole) {
                // Memberikan informasi di konsol jika role tidak ditemukan, lalu lewati karyawan ini.
                $this->command->info("Role not found for Karyawan ID: {$employee->Kar_ID}. Skipping.");
                continue;
            }

            // Membuat atau Menemukan (firstOrCreate) Entri Member
            // Ini adalah metode Eloquent yang akan mencari record berdasarkan kondisi pertama (Mem_ID)
            // Jika ditemukan, tidak akan membuat baru. Jika tidak, akan membuat record baru dengan data kedua.
            Member::firstOrCreate(
                ['Mem_ID' => $employee->emp_Code],
                [
                    'Mem_UserName' => $employee->emp_Name,
                    'mem_password' => Hash::make('password'),
                    'Mem_ActiveYN' => 'Y',
                    'role_id' => $assignedRole->id,
                    'mem_veriterima' => 'F',
                    'mem_veriretur' => 'F',
                    // PERBAIKAN DI SINI: Persingkat string menjadi 10 karakter atau kurang
                    'Mem_UpdateID' => 'SYS_SEED', // Ganti 'SYSTEM_SEED' menjadi 'SYS_SEED' (8 karakter)
                    // Atau 'SEED_SYSTEM' (11 karakter, masih terlalu panjang)
                    // Atau 'SYS_ADMIN' (9 karakter)
                    'Mem_LastUpdate' => now(),
                    'Mem_StartPeriod' => null,
                    'Mem_EndPeriod' => null,
                    'Mem_Menu' => null,
                    'Mem_Note' => "Akun untuk karyawan: {$employee->emp_Name}",
                    'mem_count' => 0,
                    'Mem_rECORD' => null,
                ]
            );

            $roleIndex++; // Pindah ke role berikutnya untuk karyawan selanjutnya
        }

        // Memberikan pesan informasi di konsol setelah seeder selesai
        $this->command->info('MemberSeeder completed. Created members from Employee data.');
    }
}
