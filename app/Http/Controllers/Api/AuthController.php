<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Presensi\Employee; // Gunakan model Employee Anda
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Sesuaikan validasi dengan file Dart
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan NIK (emp_NID)
        if (!$user = Employee::where('emp_NID', $request->nik)->first()) {
            return response()->json(['message' => 'NIK atau yang Anda masukkan salah.'], 401);
        }

        // Jika user tidak ditemukan atau password salah
        if (!Hash::check($request->password, $user->emp_password)) {
            return response()->json(['message' => 'Password yang Anda masukkan salah.'], 401);
        }

        // Hapus token lama, buat token baru menggunakan Sanctum
        $user->tokens()->delete();
        $token = $user->createToken('mobile-token-' . $user->emp_NID)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
