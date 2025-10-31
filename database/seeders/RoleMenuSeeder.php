<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\keamanan\Role;     // Pastikan namespace ini benar
use App\Models\keamanan\Menu;     // Pastikan namespace ini benar
use App\Models\keamanan\RoleMenu; // Pastikan namespace ini benar

class RoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil role yang sudah ada
        $adminRole = Role::where('name', 'Admin')->first();

        // Ambil semua menu yang ada
        $allMenus = Menu::all();

        // --- Definisi Akses untuk Role Admin ---
        if ($adminRole) {
            foreach ($allMenus as $menu) {
                // Admin memiliki akses ke SEMUA menu secara default
                RoleMenu::firstOrCreate(
                    ['role_id' => $adminRole->id, 'menu_id' => $menu->id],
                    ['role_id' => $adminRole->id, 'menu_id' => $menu->id]
                );
            }
        }
    }
}