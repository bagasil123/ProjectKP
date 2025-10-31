<?php

namespace App\Http\Controllers\ControllerSP;

use App\Http\Controllers\Controller;
use App\Models\SPModels\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar pelanggan.
     */
    public function index()
    {
        $pelanggans = Pelanggan::orderBy('anggota')->get();
        return view('SistemPenjualan.Pelanggan', compact('pelanggans'));
    }

    /**
     * Menyimpan pelanggan baru ke database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'anggota' => 'required|string|max:255|unique:daftarpelanggan,anggota',
            'alamat' => 'required|string',
            'telp' => 'required|string|max:20|unique:daftarpelanggan,telp',
            'email' => 'nullable|email|max:100',
            'cara_bayar' => 'nullable|string|in:TUNAI,KREDIT,KONSINYASI',
            'lama_bayar' => 'nullable|integer|min:0',
            'potongan' => 'nullable|numeric|min:0|max:100',
            'nominal_plafon' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:Aktif,Tidak Aktif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Buat kode dan tambahkan tanggal
        $last = Pelanggan::orderBy('id', 'desc')->first();
        $validated['kode'] = 'PEL-' . str_pad(($last->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        $validated['tanggal'] = now();
        // Baris yang menyebabkan error SQL telah dihapus dari sini.

        $pelanggan = Pelanggan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan baru berhasil ditambahkan.',
            'data' => $pelanggan
        ]);
    }

    /**
     * Memperbarui data pelanggan.
     */
    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validator = Validator::make($request->all(), [
            'anggota' => 'required|string|max:255|unique:daftarpelanggan,anggota,' . $pelanggan->id,
            'alamat' => 'required|string',
            'telp' => 'required|string|max:20|unique:daftarpelanggan,telp,' . $pelanggan->id,
            'email' => 'nullable|email|max:100',
            'cara_bayar' => 'nullable|string|in:TUNAI,KREDIT,KONSINYASI',
            'lama_bayar' => 'nullable|integer|min:0',
            'potongan' => 'nullable|numeric|min:0|max:100',
            'nominal_plafon' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:Aktif,Tidak Aktif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pelanggan->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui.',
            'data' => $pelanggan
        ]);
    }

    /**
     * Menghapus data pelanggan.
     */
    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil dihapus.'
        ]);
    }
}
