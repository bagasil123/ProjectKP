<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\keamanan\Member;    // Import model Member
use App\Models\keamanan\RightAccess; // Import model RightAccess
use App\Models\keamanan\RoleMenu;          // Import model RoleMenu (untuk kombinasi menu)
use Illuminate\Support\Facades\DB; // Untuk transaksi database

class RightAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua member yang sudah ada (dari MemberSeeder)
        $members = Member::all();

        // Loop setiap member
        foreach ($members as $member) {
            // Pastikan member memiliki role utama
            if ($member->role) {
                // Ambil semua kombinasi role-menu (id dari tabel role_menu)
                // yang terkait dengan role utama member ini
                $roleMenusForMemberRole = RoleMenu::where('role_id', $member->role->id)->get();

                foreach ($roleMenusForMemberRole as $rm) {
                    // Buat entri RightAccess untuk setiap kombinasi role-menu ini.
                    // Ini akan memberikan hak akses default (misalnya 'T' untuk semua)
                    // jika role member tersebut memiliki akses ke menu tersebut.
                    RightAccess::firstOrCreate(
                        ['AC_USER' => $member->Mem_ID, 'AC_MAINMENU' => $rm->id], // Kondisi pencarian
                        [
                            'AC_AD' => 'T', // Beri hak 'Tambah' secara default
                            'AC_ED' => 'T', // Beri hak 'Ubah' secara default
                            'AC_DE' => 'T', // Beri hak 'Hapus' secara default
                            // PERBAIKAN DI SINI: Persingkat string menjadi 10 karakter atau kurang
                            'AC_USERID' => 'SYS_SEEDER', // Ganti 'SYSTEM_SEED' menjadi 'SYS_SEEDER' (10 karakter)
                            // Atau 'SYS_SEED' (8 karakter)
                            'AC_LASTUPDATE' => now(),
                        ]
                    );
                }
            } else {
                $this->command->info("Member ID: {$member->Mem_ID} (Username: {$member->Mem_UserName}) does not have a role. Skipping RightAccess creation.");
            }
        }

        $this->command->info('RightAccessSeeder completed. Default permissions created for members.');
    }
}