<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\keamanan\Member; // Import model Member Anda
use App\Models\keamanan\Role; // Import model Role Anda
use App\Models\keamanan\Menu; // Import model Menu Anda
use App\Models\keamanan\RoleMenu; // Import model RoleMenu Anda (tabel pivot)

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // --- GATES UNTUK HAK AKSES GRANULAR (Tambah, Ubah, Hapus) per Menu ---
        // Ini adalah inti dari implementasi hak akses dinamis Anda.
        // Helper function untuk mendapatkan record RightAccess yang spesifik
        $getSpecificRightAccess = function (Member $member, string $menuSlug) {
            // Pastikan member memiliki role
            if (!$member->role) {
                return null;
            }

            // Dapatkan objek Menu berdasarkan slug
            $menu = Menu::where('slug', $menuSlug)->first();
            if (!$menu) {
                return null; // Menu tidak ditemukan
            }

            // Dapatkan ID kombinasi Role-Menu (dari tabel role_menu)
            // untuk role utama user dan menu yang dituju
            $roleMenu = RoleMenu::where('role_id', $member->role->id)
                                ->where('menu_id', $menu->id)
                                ->first();
            if (!$roleMenu) {
                return null; // Kombinasi role-menu tidak ditemukan di role_menu
            }

            // Cari record hak akses spesifik dari user di tabel mn_rightacces
            // AC_MAINMENU di RightAccess merujuk ke id dari RoleMenu
            return $member->rightAccesses->where('AC_MAINMENU', $roleMenu->id)->first();
        };

        // Gate untuk Hak Tambah (AC_AD)
        // Penggunaan: @can('tambah', 'keamanan.roles')
        Gate::define('tambah', function (Member $member, string $menuSlug) use ($getSpecificRightAccess) {
            $rightAccess = $getSpecificRightAccess($member, $menuSlug);
            return $rightAccess && $rightAccess->AC_AD === 'T';
        });

        // Gate untuk Hak Ubah (AC_ED)
        // Penggunaan: @can('ubah', 'keamanan.roles')
        Gate::define('ubah', function (Member $member, string $menuSlug) use ($getSpecificRightAccess) {
            $rightAccess = $getSpecificRightAccess($member, $menuSlug);
            return $rightAccess && $rightAccess->AC_ED === 'T';
        });

        // Gate untuk Hak Hapus (AC_DE)
        // Penggunaan: @can('hapus', 'keamanan.roles')
        Gate::define('hapus', function (Member $member, string $menuSlug) use ($getSpecificRightAccess) {
            $rightAccess = $getSpecificRightAccess($member, $menuSlug);
            return $rightAccess && $rightAccess->AC_DE === 'T';
        });

        Gate::define('access_menu', function (Member $member, string $menuSlug) {
            // Jika user tidak punya role atau role tidak punya menu
            if (!$member->role || $member->role->menus->isEmpty()) {
                return false;
            }

            // Cari apakah slug menu ini ada di daftar menu yang diizinkan untuk role ini
            // Auth::user()->role->menus sudah di-eager load di model Member->role()
            return $member->role->menus->contains('slug', $menuSlug);
        });

    }
}

