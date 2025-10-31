<?php

namespace App\Models\keamanan; // Pastikan namespace ini sesuai dengan lokasi file Anda

// Import kelas-kelas yang diperlukan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // Penting: ini yang membuat model bisa diotentikasi
use Illuminate\Notifications\Notifiable; // Opsional: jika Anda menggunakan fitur notifikasi Laravel
use Spatie\Permission\Traits\HasRoles;

class Member extends Authenticatable // Model ini HARUS meng-extend Authenticatable
{
    use Notifiable,HasRoles; // Gunakan trait Notifiable jika diperlukan

    // Definisikan nama tabel yang digunakan oleh model ini
    protected $table = 'm_members';

    // Definisikan primary key dari tabel
    protected $primaryKey = 'Mem_Auto';

    // Nonaktifkan timestamps otomatis (created_at dan updated_at)
    // karena tabel 'm_members' Anda menggunakan Mem_LastUpdate
    public $timestamps = false;

    // Daftar kolom yang bisa diisi secara massal (mass assignable)
    protected $fillable = [
        'Mem_ID', 'Mem_UserName', 'mem_password', 'Mem_ActiveYN', 'role_id', // Tambahkan role_id jika ada
        'mem_veriterima', 'mem_veriretur', 'Mem_UpdateID', 'Mem_LastUpdate',
        'Mem_StartPeriod', 'Mem_EndPeriod', 'Mem_Menu', 'Mem_Note', 'mem_count', 'Mem_rECORD'
    ];

    // Kolom-kolom yang harus disembunyikan saat model diserialisasi (misalnya ke JSON)
    protected $hidden = [
        'mem_password', // Sembunyikan password demi keamanan
    ];

    // --- Implementasi Metode dari Authenticatable Contract ---

    /**
     * Dapatkan nama kolom yang digunakan sebagai 'username' untuk otentikasi.
     * Secara default Laravel mencari 'email' atau 'username'. Kita akan pakai 'Mem_UserName'.
     * Ini akan menjadi input yang diharapkan Laravel saat login.
     */
    public function getAuthIdentifierName()
    {
        return 'Mem_Auto'; // Menggunakan kolom 'Mem_UserName' sebagai identifier
    }

    /**
     * Dapatkan nilai identifikasi otentikasi untuk pengguna saat ini.
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Dapatkan password dari pengguna untuk tujuan otentikasi.
     */
    public function getAuthPassword()
    {
        return $this->mem_password; // Menggunakan kolom 'mem_password' sebagai password
    }

    /**
     * Dapatkan "remember me" token untuk pengguna.
     * Jika Anda ingin menggunakan fitur "Ingat Saya", Anda HARUS menambahkan kolom 'remember_token'
     * (string, nullable, 100 karakter) ke tabel 'm_members' melalui migrasi baru.
     * Jika tidak ada kolom ini, kembalikan null atau string kosong.
     */
    public function getRememberToken()
    {
        // Contoh jika Anda menambahkan kolom remember_token:
        // return $this->remember_token;
        return null; // Mengembalikan null jika kolom 'remember_token' tidak ada
    }

    /**
     * Set "remember me" token untuk pengguna.
     */
    public function setRememberToken($value)
    {
        // Contoh jika Anda menambahkan kolom remember_token:
        // $this->remember_token = $value;
    }

    /**
     * Dapatkan nama kolom "remember me" token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token'; // Nama standar kolom remember token Laravel
    }

    // --- Relasi Model (Opsional, tapi penting untuk fungsionalitas lain) ---
    // Pastikan Anda telah mengimpor model Role, RightAccess, RoleMenu di bagian atas file
    // dan namespace-nya sudah benar sesuai lokasi file Anda.

    /**
     * Dapatkan role utama member ini.
     */
    public function role()
    {
        return $this->belongsTo(\App\Models\keamanan\Role::class, 'role_id', 'id');
    }

    /**
     * Dapatkan hak akses granular (RightAccess) untuk member ini.
     */
    public function rightAccesses()
    {
        return $this->hasMany(\App\Models\keamanan\RightAccess::class, 'AC_USER', 'Mem_ID');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Presensi\Employee::class, 'emp_Code', 'Mem_ID');
    }
}
