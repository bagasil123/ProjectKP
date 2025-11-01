<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Dtproduk;
use App\Models\Inventory\Supplier;
use App\Models\MutasiGudang\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DtprodukController extends Controller
{
    public function index(Request $request)
    
    {    
        $dtproduks = Dtproduk::with('supplier')
            ->orderBy('kode_produk', 'asc')
            ->get();
            
        // Menggunakan groupBy untuk menghindari duplikat nama supplier
        $suppliers = Supplier::select('id', 'nama_supplier')
            ->orderBy('nama_supplier', 'asc')
            ->get()
            ->unique('nama_supplier');

        $warehouses = Warehouse::all();
    
        return view('inventory.dataproduk.index', compact('dtproduks', 'suppliers', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => 'required|unique:dataproduk_tabel,kode_produk',
            'nama_produk' => 'required|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'qty' => 'required|integer|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id(); // Jika diperlukan

        // Include WARE_Auto into the data array before creating the model
        if ($request->has('WARE_Auto')) {
            $data['WARE_Auto'] = $request->WARE_Auto;
        }

        $produk = Dtproduk::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $produk
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_produk' => 'required|unique:dataproduk_tabel,kode_produk,'.$id,
            'nama_produk' => 'required|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'qty' => 'required|integer|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
        ]);

    $produk = Dtproduk::findOrFail($id);
    $data = $request->all();
    if ($request->has('WARE_Auto')) {
        $data['WARE_Auto'] = $request->WARE_Auto;
    }
    $produk->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $produk
        ]);
    }

    public function destroy($id)
    {
        $produk = Dtproduk::findOrFail($id);
        try {
            $produk->delete();
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk: '.$e->getMessage()
            ], 500);
        }
    }
}