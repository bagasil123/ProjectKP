<?php

namespace App\Http\Controllers\keamanan;

use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        // Mengambil semua role, dan menghitung jumlah member yang terkait dengan setiap role
        // 'members' di sini merujuk pada nama metode relasi di model Role.php
        $roles = \App\Models\keamanan\Role::withCount('members')->get();

        return view('keamanan.role.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
        ], [
            'name.unique' => 'Nama role sudah digunakan.',
            'name.required' => 'Nama role wajib diisi.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $role = Role::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil ditambahkan.',
            'data' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Normalisasi string untuk perbandingan
        $inputName = strtolower(trim(preg_replace('/\s+/', ' ', $request->name)));
        $currentName = strtolower(trim(preg_replace('/\s+/', ' ', $role->name)));

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('roles', 'name')->ignore($role->id),
                function ($attribute, $value, $fail) use ($currentName, $inputName) {
                    if ($inputName === $currentName) {
                        $fail('Nama role sama seperti sebelumnya. Harap masukkan nama yang berbeda.');
                    }
                },
            ],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique'   => 'Nama role sudah digunakan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $role->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil diperbarui.',
            'data' => $role
        ]);
    }


    public function destroy(Role $role)
    {
        // Validasi 1: Cek jika role adalah default/protected
        $protectedRoles = ['admin','owner']; // Sesuaikan dengan kebutuhan
        if (in_array(strtolower($role->name), $protectedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Role sistem default tidak dapat dihapus.'
            ], 403);
        }

        try {
            $role->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus.',
                'data' => [
                    'deleted_role' => $role->name,
                    'deleted_at' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus role: ' . $e->getMessage()
            ], 500);
        }
    }

}
