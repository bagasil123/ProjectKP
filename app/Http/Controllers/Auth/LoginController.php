<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // Penting: Pastikan ini di-import

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Dapatkan nama bidang login yang digunakan oleh aplikasi.
     * Kita menggunakan 'Mem_UserName' sebagai username.
     */
    public function username()
    {
        return 'Mem_UserName';
    }

    /**
     * Dapatkan array kredensial yang digunakan untuk percobaan otentikasi.
     * Ini digunakan oleh trait AuthenticatesUsers.
     */
    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->{$this->username()},
            'password' => $request->mem_password, // ← gunakan key 'password'
        ];
    }

    /**
     * Validasi permintaan login.
     * Ini meng-override metode default dari trait AuthenticatesUsers.
     * Sekarang memvalidasi 'mem_password' alih-alih 'password'.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string', // Validasi username (Mem_UserName)
            'mem_password' => 'required|string',   // Validasi password (mem_password)
        ]);
    }

    /**
     * Method ini dipanggil setelah pengguna berhasil login.
     */
    protected function authenticated(Request $request, $user)
    {
        // $user di sini adalah instance dari model App\Models\keamanan\Member yang baru saja login
        
        // Periksa status Mem_ActiveYN
        if ($user->Mem_ActiveYN === 'N') {
            // Jika status tidak aktif, logout user tersebut
            Auth::guard('web')->logout(); // Pastikan logout dari guard 'web' yang Anda gunakan

            $request->session()->invalidate(); // Batalkan sesi
            $request->session()->regenerateToken(); // Regenerasi token CSRF

            // Arahkan kembali ke halaman login dengan pesan kesalahan
            return redirect('/login')->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Jika status aktif, biarkan proses login standar Laravel berlanjut
        return redirect()->intended($this->redirectPath())->with('success', 'Anda berhasil login!');
    }
}