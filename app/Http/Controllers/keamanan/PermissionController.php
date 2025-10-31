<?php

namespace App\Http\Controllers\keamanan;

use App\Http\Controllers\Controller;
use App\Models\keamanan\Role;
use App\Models\keamanan\Menu;
use App\Models\keamanan\RoleMenu; // Penting: Ini model tabel pivot role_menu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    // ... (metode index() atau lainnya untuk menampilkan halaman permission) ...

    public function index()
    {
        $roles = Role::all();
        $menus = Menu::orderBy('order')->get(); // Ambil semua menu

        $selectedRole = request()->query('role_id') ? Role::find(request()->query('role_id')) : null;
        $currentPermissions = collect();
        if ($selectedRole) {
            // Ini akan mengambil ID menu dari role_menu yang sudah ada untuk role ini
            $currentPermissions = $selectedRole->menus->pluck('id'); 
        }

        return view('keamanan.permission.index', compact('roles', 'menus', 'selectedRole', 'currentPermissions'));
    }

    /**
     * Memperbarui akses menu untuk role tertentu.
     * Ini adalah metode yang perlu dimodifikasi.
     */
    public function updateMenuAccess(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'selected_menus' => 'nullable|array', // ID menu yang dicentang di form
            'selected_menus.*' => 'exists:menus,id', // Validasi setiap ID menu
        ]);

        $role = Role::findOrFail($request->role_id);
        $selectedMenuIds = $request->input('selected_menus', []); // ID menu yang dicentang

        DB::beginTransaction();
        try {
            // Tambahkan parent_id jika ada
            $allMenuIds = $selectedMenuIds;

            foreach ($selectedMenuIds as $menuId) {
                $menu = Menu::find($menuId);
                if ($menu && $menu->parent_id) {
                    $allMenuIds[] = $menu->parent_id; // Tambahkan parent menu
                }
            }

            // Hilangkan duplikat ID
            $allMenuIds = array_unique($allMenuIds);

            // Sync semua menu dan parent-nya
            $role->menus()->sync($allMenuIds);

            DB::commit(); // Commit transaksi
            return redirect()->back()->with('success', 'Akses menu berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback(); // Rollback jika ada kesalahan
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}