<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Satuanproduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SatuanProdukController extends Controller
{
    /**
     * Menampilkan daftar satuan produk.
     */
    public function index()
    {
        $satuanProduks = SatuanProduk::orderBy('UOM_Code', 'asc')->get();
        return view('inventory.satuanproduk.index', compact('satuanProduks'));
    }

    /**
     * Menyimpan satuan produk baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'UOM_Code' => 'required|unique:m_uom,UOM_Code|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        SatuanProduk::create([
            'UOM_Code' => $request->input('UOM_Code'),
            'UOM_Entrydate' => now()
        ]);
        return response()->json([
            'message' => 'Satuan produk berhasil ditambahkan.'
        ], 201);
    }

    /**
     * Mengupdate satuan produk.
     */
    public function update(Request $request, SatuanProduk $satuanproduk)
    {
        $primaryKeyColumnName = $satuanproduk->getKeyName();

        $validator = Validator::make($request->all(), [
            'UOM_Code' => 'required|unique:m_uom,UOM_Code,' . $satuanproduk->getKey() . ',' . $primaryKeyColumnName . '|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $dataToUpdate = [
            'UOM_Code' => $request->input('UOM_Code'),
        ];

        if ($request->has('UOM_Amount')) {
            $dataToUpdate['UOM_Amount'] = $request->input('UOM_Amount');
        }

        if (Auth::check()) {
            $dataToUpdate['UOM_UpdateID'] = Auth::id();
        }

        $satuanproduk->update($dataToUpdate);

        return response()->json([
            'message' => 'Satuan produk berhasil diperbarui.'
        ], 200);
    }

    /**
     * Menghapus satuan produk.
     */
    public function destroy($id)
    {
        $satuan = Satuanproduk::find($id);

        if (!$satuan) {
            return response()->json([
                'message' => 'Data satuan produk tidak ditemukan'
            ], 404);
        }

        $satuan->delete();

        return response()->json([
            'message' => 'Satuan produk berhasil dihapus'
        ]);
    }
}