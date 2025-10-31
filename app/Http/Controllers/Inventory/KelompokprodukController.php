<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\KelompokProduk;
use Illuminate\Support\Facades\Validator;

class KelompokProdukController extends Controller
{
    public function index()
    {
        $kelompokProduks = KelompokProduk::latest()->get();

        return view('inventory.kelompokproduk.index', compact('kelompokProduks'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelompok' => 'required|unique:kelompokproduk_tabel,nama_kelompok',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        KelompokProduk::create([
            'nama_kelompok' => $request->nama_kelompok,
        ]);

        return response()->json(['message' => 'Kelompok Produk berhasil ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        $kelompok = KelompokProduk::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_kelompok' => 'required|unique:kelompokproduk_tabel,nama_kelompok,' . $kelompok->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kelompok->update([
            'nama_kelompok' => $request->nama_kelompok,
        ]);

        return response()->json(['message' => 'Kelompok Produk berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $kelompok = KelompokProduk::find($id);

        if (!$kelompok) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $kelompok->delete();

        return response()->json(['message' => 'Kelompok Produk berhasil dihapus.']);
    }
}
