<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi\Employee; // Pastikan namespace model Employee Anda benar
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Memvalidasi data pengguna sebelum mengizinkan reset password.
     */
    public function validateStep(Request $request)
    {
        $request->validate([
            // Sesuaikan dengan nama kolom di tabel m_employee
            'nik' => 'required|string|exists:m_employee,emp_NID',
            'email' => 'required|email',
            'join_date' => 'required|date_format:Y-m-d', // Asumsi mobile mengirim format YYYY-MM-DD
        ]);

        $employee = Employee::where('emp_NID', $request->nik)->first();

        // PERBAIKAN: Validasi email, bisa menggunakan emp_Email atau emp_Email2
        $emailIsValid = (
            strtolower($employee->emp_Email) === strtolower($request->email) ||
            strtolower($employee->emp_Email2) === strtolower($request->email)
        );

        // PERBAIKAN: Validasi tanggal bergabung menggunakan emp_startcontract
        $joinDateIsValid = Carbon::parse($employee->emp_startcontract)->format('Y-m-d') === $request->join_date;

        // Jika salah satu validasi gagal
        if (!$emailIsValid || !$joinDateIsValid) {
            return response()->json(['message' => 'Data yang Anda masukkan tidak sesuai.'], 422);
        }

        return response()->json(['message' => 'Validasi berhasil. Anda dapat melanjutkan ke tahap reset password.']);
    }

    /**
     * Mereset password pengguna.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|exists:m_employee,emp_NID',
            'new_password' => [
                'required',
                'string',
                Password::min(8) // Minimal 8 karakter
                    ->mixedCase()   // Wajib ada huruf besar dan kecil
                    ->numbers()     // Wajib ada angka
            ],
        ]);

        $employee = Employee::where('emp_NID', $request->nik)->first();
        
        // Update password pengguna menggunakan kolom 'emp_password'
        $employee->emp_password = Hash::make($request->new_password);
        $employee->save();

        return response()->json(['message' => 'Password Anda telah berhasil direset. Silakan login kembali.']);
    }
}
