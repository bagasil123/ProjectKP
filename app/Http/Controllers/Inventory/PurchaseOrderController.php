<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderDetail;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Dtproduk;
use App\Models\Inventory\SatuanProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('supplier')->latest()->get();
        return view('inventory.purchaseorder.index', [
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    public function create()
    {
        // Dapatkan supplier dummy (misalnya supplier pertama)
        $dummySupplier = Supplier::first();
        
        // Jika tidak ada supplier, buat satu
        if (!$dummySupplier) {
            $dummySupplier = Supplier::create([
                'kode_supplier' => 'SUP-DUMMY',
                'nama_supplier' => 'Supplier Dummy',
                'alamat' => 'Alamat dummy',
                'kota' => 'Kota dummy',
                'provinsi' => 'Provinsi dummy',
                'kode_pos' => '00000',
                'nomor_telepon' => '0000000000',
                'email' => 'dummy@example.com',
                'kontak_person' => 'Contact Person Dummy'
            ]);
        }
    
        // Create new draft PO dengan nilai default
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . Str::random(6),
            'status' => 'draft',
            'supplier_id' => $dummySupplier->id, // Gunakan supplier dummy
            'purchase_type' => 'langsung', // Nilai default
            'location_id' => 'WH-A', // Nilai default
            'delivery_date' => now()->addDays(7), // Nilai default (7 hari dari sekarang)
        ]);
    
        return redirect()->route('purchase-orders.edit', $po->po_id);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_type' => 'required|in:langsung,konsinyasi',
            'location_id' => 'required',
            'delivery_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . Str::random(6),
            'status' => 'draft',
            'supplier_id' => $request->supplier_id,
            'purchase_type' => $request->purchase_type,
            'location_id' => $request->location_id,
            'delivery_date' => $request->delivery_date
        ]);

        return response()->json(['success' => true, 'id' => $po->po_id]);
    }

    public function show(PurchaseOrder $purchase_order)
    {
        $po = $purchase_order->load('details.product', 'details.uom', 'supplier');
        
        return view('inventory.purchaseorder.index', [
            'header' => $po,
            'showMode' => true,
            'suppliers' => Supplier::all(),
            'products' => Dtproduk::all(),
            'uoms' => SatuanProduk::all(),
            'locations' => ['WH-A', 'WH-B', 'WH-C']
        ]);
    }

    public function edit(PurchaseOrder $purchase_order)
    {
        $po = $purchase_order->load('details.product', 'details.uom');

        return view('inventory.purchaseorder.index', [
        'header' => $po,
        // Gunakan unique() untuk menghindari duplikat nama supplier
        'suppliers' => Supplier::orderBy('nama_supplier')->get()->unique('nama_supplier'),
        'products' => Dtproduk::all(),
        'uoms' => SatuanProduk::all(),
        'locations' => ['WH-A', 'WH-B', 'WH-C']
    ]);
    }

    public function update(Request $request, PurchaseOrder $purchase_order)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_type' => 'required|in:langsung,konsinyasi',
            'location_id' => 'required',
            'delivery_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $purchase_order->update($request->all());

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        
        // Hapus semua detail terkait
        $po->details()->delete();
        // Hapus PO secara permanen
        $po->forceDelete();
    
        return response()->json(['success' => true]);
    }
    
    // Custom methods
    public function updateHeader(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_type' => 'required|in:langsung,konsinyasi',
            'location_id' => 'required',
            'delivery_date' => 'required|date',
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $po->update($request->all());

        return response()->json(['success' => true]);
    }

    public function storeDetail(Request $request, $poId)
    {
        $request->validate([
            'product_id' => 'required|exists:dataproduk_tabel,id',
            'uom_id' => 'required|exists:m_uom,UOM_Auto',
            'qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        PurchaseOrderDetail::create([
            'po_id' => $poId,
            'product_id' => $request->product_id,
            'uom_id' => $request->uom_id,
            'qty' => $request->qty,
            'unit_price' => $request->unit_price,
            'tax_percent' => $request->tax_percent ?? 0,
            'discount_percent' => $request->discount_percent ?? 0,
            'note' => $request->note
        ]);

        return response()->json(['success' => true]);
    }

    public function updateDetail(Request $request, $poId, $detailId)
    {
        $request->validate([
            'product_id' => 'required|exists:dataproduk_tabel,id',
            'uom_id' => 'required|exists:m_uom,UOM_Auto',
            'qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $detail = PurchaseOrderDetail::where('po_id', $poId)
            ->where('detail_id', $detailId)
            ->firstOrFail();

        $detail->update($request->all());

        return response()->json(['success' => true]);
    }

    public function deleteDetail($poId, $detailId)
    {
        $detail = PurchaseOrderDetail::where('po_id', $poId)
            ->where('detail_id', $detailId)
            ->firstOrFail();

        $detail->delete();

        return response()->json(['success' => true]);
    }

    public function publish($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        
        // Validate draft status
        if ($po->status !== 'draft') {
            return response()->json(['error' => 'Hanya PO draft yang bisa dipublish'], 400);
        }

        // Validate header completeness
        if (!$po->supplier_id || !$po->purchase_type || !$po->location_id || !$po->delivery_date) {
            return response()->json(['error' => 'Lengkapi data header PO terlebih dahulu'], 400);
        }

        // Validate at least one item
        if ($po->details()->count() === 0) {
            return response()->json(['error' => 'PO harus memiliki minimal 1 item'], 400);
        }

        $po->update(['status' => 'published']);
        return response()->json(['success' => true]);
    }

    public function cancel($id)
    {
        DB::transaction(function () use ($id) {
            $po = PurchaseOrder::findOrFail($id);
            
            // Hanya batalkan jika status draft
            if ($po->status === 'draft') {
                // Hapus permanen detail dan header
                $po->details()->forceDelete();
                $po->forceDelete();
            }
        });
    
        return response()->json(['success' => true]);
    }
}