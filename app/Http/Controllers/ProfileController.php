<?php

namespace App\Http\Controllers;

// Import model Member Anda
use App\Models\keamanan\Member; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Untuk Rule::unique

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        // Mendapatkan instance user yang sedang login (sekarang adalah Member)
        $member = Auth::user(); 

        // Validasi input disesuaikan dengan kolom tabel m_members dan aturan Anda
        $request->validate([
            // Mem_UserName sebagai nama utama. Validasi unik terhadap tabel m_members.
            // Gunakan Mem_ID sebagai kolom untuk ignore di Rule::unique
            'Mem_UserName' => [
                'required', 
                'string', 
                'max:50', // Sesuaikan dengan panjang varchar di migrasi m_members
                Rule::unique('m_members', 'Mem_UserName')->ignore($member->Mem_ID, 'Mem_ID'), 
            ],
            // Hapus 'last_name' dan 'email' jika tidak ada di tabel m_members Anda
            // 'last_name' => 'nullable|string|max:255', 
            // 'email' => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,

            // Validasi password disesuaikan dengan kolom dan kebutuhan Anda
            'current_password' => 'nullable|required_with:new_password', // Current password opsional, tapi wajib jika new_password diisi
            'new_password' => 'nullable|min:4|max:12|required_with:current_password', // Min 4, Max 12
            // Aturan 'confirmed' secara otomatis akan mencari input 'new_password_confirmation'
            'new_password_confirmation' => 'nullable|min:4|max:12|required_with:new_password|same:new_password'
        ]);


        // Update kolom yang relevan di model Member
        // Ganti $user menjadi $member
        $member->Mem_UserName = $request->input('Mem_UserName');
        // Hapus baris ini jika tidak ada kolom last_name:
        // $member->last_name = $request->input('last_name');
        // Hapus baris ini jika tidak ada kolom email:
        // $member->email = $request->input('email'); 

        // Proses perubahan password
        if (!is_null($request->input('current_password'))) {
            // Memeriksa password saat ini yang di-hash
            if (Hash::check($request->input('current_password'), $member->mem_password)) {
                // Update password dengan password baru yang sudah di-hash
                $member->mem_password = Hash::make($request->input('new_password'));
            } else {
                // Jika password saat ini tidak cocok, kembalikan dengan error
                return redirect()->back()->withErrors(['current_password' => __('Password saat ini tidak cocok.')])->withInput();
            }
        }

        // Simpan perubahan pada model Member
        $member->save();

        // Redirect dengan pesan sukses
        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.'); 
    }
}