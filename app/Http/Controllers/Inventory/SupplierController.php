<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\CaraBayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('caraBayar')
            ->orderBy('kode_supplier', 'asc') // Diubah dari created_at ke kode_supplier
            ->get();

        $caraBayarOptions = CaraBayar::all();
        return view('inventory.supplier.index', compact('suppliers', 'caraBayarOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_supplier' => 'required|string|max:20|unique:suppliers',
            'nama_supplier' => 'required|string|max:255',
            'alamat' => 'required|string',
            'contact_person' => 'required|string|max:100',
            'telp' => 'required|string|max:20|unique:suppliers',
            'email' => 'nullable|email|max:100|unique:suppliers',
            'tanggal' => 'required|date',
            'cara_bayar_id' => 'required|exists:cara_bayar_tabel,id',
            'lama_bayar' => 'nullable|integer|min:0',
            'potongan' => 'nullable|numeric|between:0,100',
        ], [
            'kode_supplier.unique' => 'Kode supplier sudah digunakan',
            'nama_supplier.required' => 'Nama supplier wajib diisi',
            'telp.unique' => 'Nomor telepon sudah digunakan',
            'email.unique' => 'Email sudah digunakan'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplier = Supplier::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil ditambahkan',
                'data' => $supplier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode_supplier' => 'required|string|max:20|unique:suppliers,kode_supplier,'.$supplier->id,
            'nama_supplier' => 'required|string|max:255',
            'alamat' => 'required|string',
            'contact_person' => 'required|string|max:100',
            'telp' => 'required|string|max:20|unique:suppliers,telp,'.$supplier->id,
            'email' => 'nullable|email|max:100|unique:suppliers,email,'.$supplier->id,
            'tanggal' => 'required|date',
            'cara_bayar_id' => 'required|exists:cara_bayar_tabel,id',
            'lama_bayar' => 'nullable|integer|min:0',
            'potongan' => 'nullable|numeric|between:0,100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplier->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil diperbarui',
                'data' => $supplier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplierName = $supplier->nama_supplier;
            
            // Check if supplier is used in other tables
            if ($supplier->penerimaan()->exists() || $supplier->purchaseOrders()->exists()) {
                throw new \Exception('Supplier tidak dapat dihapus karena sudah digunakan dalam transaksi');
            }
            
            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Supplier ' . $supplierName . ' berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus supplier: ' . $e->getMessage()
            ], 500);
        }
    }
}