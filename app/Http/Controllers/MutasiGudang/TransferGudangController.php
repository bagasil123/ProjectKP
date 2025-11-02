<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Http\Controllers\Controller;
use App\Models\MutasiGudang\TransferHeader;
use App\Models\MutasiGudang\TransferDetail;
use App\Models\MutasiGudang\GudangOrder; // Untuk Ambil Permintaan
use App\Models\MutasiGudang\Warehouse;   // Untuk Ambil Gudang
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TransferGudangController extends Controller
{
    /**
     * Tampilan Daftar Transfer (List View)
     * PERBAIKAN: Filter berdasarkan hak akses gudang
     */
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];

        // 1. Ambil query dasar
        $query = TransferHeader::with('gudangPengirim', 'gudangPenerima');

        // 2. Jika BUKAN Super Admin, terapkan filter
        if (!$isSuperAdmin) {
            // (PERBAIKAN) Ambil NAMA gudang yang bisa diakses
            $accessibleWarehouseNames = Warehouse::whereIn('WARE_Auto', $accessibleWarehouses)
                                                 ->pluck('WARE_Name')->toArray();
            
            // Filter data tabel: user harus bisa akses Gudang Asal (Trx_WareCode)
            // ATAU Gudang Tujuan (Trx_RcvNo) (berdasarkan NAMA)
            $query->where(function ($q) use ($accessibleWarehouseNames) {
                $q->whereIn('Trx_WareCode', $accessibleWarehouseNames)
                  ->orWhereIn('Trx_RcvNo', $accessibleWarehouseNames);
            });
        }
        
        // 3. Selesaikan query (sesuai view Anda)
        $transfers = $query->orderBy('Trx_Auto', 'desc')->paginate(15); 

        return view('mutasigudang.transfergudang.index', compact('transfers'));
    }

    /**
     * Tampilan Form 'Buat Baru'
     * PERBAIKAN: Buat draft baru dan redirect ke halaman edit
     */
    public function create()
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $transactionDate = now();
            // (Menggunakan fungsi generateTrxNumber Anda yang sudah diperbaiki)
            $newTrxNumber = $this->generateTrxNumber($transactionDate);

            // (PERBAIKAN) Mengisi semua kolom yang dibutuhkan
            $transfer = TransferHeader::create([
                'trx_number'  => $newTrxNumber,
                'Trx_Date'    => $transactionDate,
                'trx_posting' => 'F', // Status Draft
                'Trx_Emp'     => $user->name, // (Asumsi dari view Anda)
                'Trx_WareCode' => null, // Wajib diisi NANTI di form edit
                'Trx_RcvNo'    => null, // Wajib diisi NANTI di form edit
            ]);

            DB::commit();
            // Redirect ke halaman edit (sesuai route Anda)
            return redirect()->route('transfergudang.edit', $transfer->Trx_Auto);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat draft Transfer: ' . $e->getMessage());
            return redirect()->route('transfergudang.index')->with('error', 'Gagal membuat draft baru: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Form 'Edit'
     * PERBAIKAN: Kirim data $warehouses dan $permintaanGudang yang sudah difilter
     */
    public function edit($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];

        // 1. Ambil data Transfer
        $transfer = TransferHeader::with('details')->findOrFail($id);
        
        // 2. Ambil daftar GUDANG (untuk dropdown) berdasarkan hak akses
        if ($isSuperAdmin) {
            $warehouses = Warehouse::all();
        } else {
            $warehouses = Warehouse::whereIn('WARE_Auto', $accessibleWarehouses)->get();
        }

        // 3. Ambil daftar PERMINTAAN (Gudang Order) yang sudah di-submit
        $permintaanQuery = GudangOrder::where('pur_status', 'submitted'); // 'pur_status' dari GudangOrder

        if (!$isSuperAdmin) {
            // Filter permintaan: user harus bisa akses Gudang Pengirim ATAU Penerima
            $permintaanQuery->where(function ($q) use ($accessibleWarehouses) {
                // Kolom di th_gudangorder adalah ID
                $q->whereIn('from_warehouse_id', $accessibleWarehouses) 
                  ->orWhereIn('to_warehouse_id', $accessibleWarehouses);
            });
        }
        $permintaanGudang = $permintaanQuery->orderBy('Pur_Auto', 'desc')->get();
        
        // 4. Kirim semua data ke view
        return view('mutasigudang.transfergudang.index', compact('transfer', 'warehouses', 'permintaanGudang'));
    }
    
    // (Fungsi show tidak ada di route Anda, digabung di edit)
    public function show($id)
    {
        return $this->edit($id); // Panggil fungsi edit
    }

    /**
     * (AJAX) Update Header
     * PERBAIKAN: Validasi berdasarkan NAMA gudang
     */
    public function updateHeader(Request $request, $id)
    {
        $transfer = TransferHeader::findOrFail($id);
        if ($transfer->trx_posting !== 'F') {
            return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa diubah.'], 403);
        }

        // Validasi nama form dari view Anda
        $validatedData = $request->validate([
            'Trx_Date'      => 'required|date',
            'Trx_WareCode'  => 'required|string|exists:m_warehouse,WARE_Name', // Validasi by NAMA
            'Trx_RcvNo'     => 'required|string|exists:m_warehouse,WARE_Name', // Validasi by NAMA
            'Trx_Note'      => 'nullable|string',
        ]);
        
        $transfer->update($validatedData);
        return response()->json(['success' => true, 'message' => 'Header berhasil diperbarui.']);
    }

    /**
     * (AJAX) Simpan Detail Barang Manual
     */
    public function storeDetail(Request $request)
    {
        $validated = $request->validate([
            'Trx_Auto' => 'required|exists:th_slsgt,Trx_Auto',
            'Trx_ProdCode' => 'required|string|max:50',
            'trx_prodname' => 'required|string|max:255',
            'trx_uom' => 'required|string',
            'Trx_QtyTrx' => 'required|numeric|min:1',
            'trx_cogs' => 'required|numeric|min:0',
            'trx_discount' => 'nullable|numeric|min:0',
            'trx_taxes' => 'nullable|numeric|min:0',
            'trx_nettprice' => 'required|numeric', // Ambil dari kalkulasi JS
        ]);

        $transferHeader = TransferHeader::find($validated['Trx_Auto']);
        if (!$transferHeader || $transferHeader->trx_posting !== 'F') {
            return response()->json(['message' => 'Header transfer tidak ditemukan atau sudah diposting.'], 404);
        }
        
        $validated['trx_number'] = $transferHeader->trx_number; // Tambahkan trx_number
        $detail = TransferDetail::create($validated);
        
        $this->recalculateTransferTotal($transferHeader->Trx_Auto); // Hitung ulang total

        return response()->json(['success' => true, 'message' => 'Barang berhasil disimpan.', 'data' => $detail]);
    }

    /**
     * (AJAX) Hapus Detail Barang
     */
    public function destroyDetail($id, $detailId) // ID Header, ID Detail
    {
        $detail = TransferDetail::findOrFail($detailId);
        if ($detail->Trx_Auto != $id) {
            return response()->json(['success' => false, 'message' => 'Detail tidak sesuai.'], 403);
        }
        $detail->delete();
        $this->recalculateTransferTotal($id); // Hitung ulang total
        return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus.']);
    }

    /**
     * (AJAX) Hapus Draft Header
     */
    public function destroy($id)
    {
        $transfer = TransferHeader::findOrFail($id);
        if ($transfer->trx_posting !== 'F') {
            return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa dihapus.'], 403);
        }
        DB::beginTransaction();
        try {
            $transfer->details()->delete();
            $transfer->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Draft transfer berhasil dihapus.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus draft: ' . $e->getMessage()], 500);
        }
    }

    /**
     * (AJAX) Submit / Posting Transfer
     */
    public function submit($id)
    {
        $transfer = TransferHeader::with('details')->findOrFail($id);
        if ($transfer->details->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada barang. Gagal posting.'], 422);
        }
        
        DB::beginTransaction();
        try {
            // Ambil gudang asal & tujuan berdasarkan NAMA (sesuai penyimpanan di header)
            $sourceWarehouse = Warehouse::where('WARE_Name', $transfer->Trx_WareCode)->first();
            $destWarehouse   = Warehouse::where('WARE_Name', $transfer->Trx_RcvNo)->first();

            if (!$sourceWarehouse || !$destWarehouse) {
            throw new Exception('Gudang asal atau tujuan tidak ditemukan.');
            }

            // Nama tabel/kolom stok -- sesuaikan dengan skema DB Anda
            $stockTable = 'm_stock';        // <-- ubah jika tabel stok berbeda
            $prodCol    = 'Prod_Code';     // <-- ubah jika kolom kode produk berbeda
            $wareCol    = 'WARE_Auto';     // <-- ubah jika kolom id gudang berbeda
            $qtyCol     = 'Stock_Qty';     // <-- ubah jika kolom kuantitas berbeda

            foreach ($transfer->details as $detail) {
            $prodCode = $detail->Trx_ProdCode;
            $qty      = $detail->Trx_QtyTrx;

            // Lock baris stok asal untuk konsistensi
            $sourceStock = DB::table($stockTable)
                ->where($wareCol, $sourceWarehouse->WARE_Auto)
                ->where($prodCol, $prodCode)
                ->lockForUpdate()
                ->first();

            $sourceQty = $sourceStock->{$qtyCol} ?? 0;
            if ($sourceQty < $qty) {
                throw new Exception("Stok tidak cukup untuk produk {$prodCode} di gudang {$sourceWarehouse->WARE_Name} (tersedia: {$sourceQty}, dibutuhkan: {$qty}).");
            }

            // Kurangi stok di gudang asal
            DB::table($stockTable)
                ->where($wareCol, $sourceWarehouse->WARE_Auto)
                ->where($prodCol, $prodCode)
                ->update([$qtyCol => DB::raw("$qtyCol - {$qty}")]);

            // Lock & update/insert stok di gudang tujuan
            $destStock = DB::table($stockTable)
                ->where($wareCol, $destWarehouse->WARE_Auto)
                ->where($prodCol, $prodCode)
                ->lockForUpdate()
                ->first();

            if ($destStock) {
                DB::table($stockTable)
                ->where($wareCol, $destWarehouse->WARE_Auto)
                ->where($prodCol, $prodCode)
                ->update([$qtyCol => DB::raw("$qtyCol + {$qty}")]);
            } else {
                DB::table($stockTable)->insert([
                $wareCol => $destWarehouse->WARE_Auto,
                $prodCol => $prodCode,
                $qtyCol  => $qty,
                'created_at' => now(),
                'updated_at' => now(),
                ]);
            }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal update stok saat posting transfer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal update stok: ' . $e->getMessage()], 500);
        }
        $transfer->update(['trx_posting' => 'T']); // 'T' = True (Posted)
        return response()->json(['success' => true, 'message' => 'Transfer berhasil di-posting.']);
    }

    /**
     * (AJAX) Ambil detail dari Permintaan Gudang (Gudang Order)
     */
    public function fetchPermintaanDetails($permintaanId)
    {
        try {
            // (PERBAIKAN) Kita perlu relasi gudangPengirim/Penerima untuk mengambil NAMA
            $permintaan = GudangOrder::with('details', 'gudangPengirim', 'gudangPenerima')->findOrFail($permintaanId); 
            
            // Data yang dikirim kembali ke JavaScript
            return response()->json([
                'success' => true,
                'data' => [
                    // (PERBAIKAN) Kirim NAMA gudang, bukan ID
                    'pur_warehouse' => $permintaan->gudangPengirim->WARE_Name ?? null, 
                    'pur_destination' => $permintaan->gudangPenerima->WARE_Name ?? null,
                    
                    'details' => $permintaan->details->map(function ($detail) {
                        // "Menerjemahkan" nama kolom dari td_gudangorderdetail ke td_slsgt
                        return [
                            'Pur_ProdCode' => $detail->kode_produk, // dari td_gudangorderdetail
                            'pur_prodname' => $detail->nama_produk, // dari td_gudangorderdetail
                            'Pur_UOM' => $detail->uom,
                            'Pur_Qty' => $detail->qty,
                            'Pur_GrossPrice' => $detail->price,
                            'Pur_Discount' => $detail->discount,
                            'Pur_Taxes' => $detail->taxes,
                            'Pur_NettPrice' => $detail->subtotal,
                        ];
                    })
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Permintaan Gudang tidak ditemukan.'], 404);
        }
    }

    /**
     * (AJAX) Simpan (Sync) detail dari Permintaan ke Transfer
     */
    public function syncDetailsFromPermintaan(Request $request, $id)
    {
        $request->validate(['permintaan_id' => 'required|exists:th_gudangorder,Pur_Auto']);
        $transfer = TransferHeader::findOrFail($id);
        $permintaan = GudangOrder::with('details', 'gudangPengirim', 'gudangPenerima')->find($request->permintaan_id);

        if ($transfer->trx_posting !== 'F') {
            return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa diubah.'], 403);
        }
        
        DB::beginTransaction();
        try {
            // 1. Hapus detail lama
            $transfer->details()->delete();

            // 2. Update header transfer (Gudang Asal & Tujuan)
            $transfer->update([
                // (PERBAIKAN) Ambil NAMA gudang dari relasi permintaan
                'Trx_WareCode' => $permintaan->gudangPengirim->WARE_Name ?? null,
                'Trx_RcvNo' => $permintaan->gudangPenerima->WARE_Name ?? null,
                'Trx_Note' => 'Transfer berdasarkan Permintaan No: ' . $permintaan->pur_ordernumber,
                'ref_pur_auto' => $permintaan->Pur_Auto, // Simpan ID Permintaan
            ]);

            // 3. Masukkan detail baru dari permintaan
            $totalBruto = 0; $totalDiscount = 0; $totalTaxes = 0; $totalNetto = 0;
            
            foreach ($permintaan->details as $detail) {
                TransferDetail::create([
                    'Trx_Auto' => $transfer->Trx_Auto,
                    'trx_number' => $transfer->trx_number,
                    'Trx_ProdCode' => $detail->kode_produk, // dari td_gudangorderdetail
                    'trx_prodname' => $detail->nama_produk, // dari td_gudangorderdetail
                    'trx_uom' => $detail->uom,
                    'Trx_QtyTrx' => $detail->qty,
                    'trx_cogs' => $detail->price, // Harga
                    'trx_discount' => $detail->discount,
                    'trx_taxes' => $detail->taxes,
                    'trx_nettprice' => $detail->subtotal, // Subtotal
                ]);
                $totalBruto += ($detail->qty * $detail->price);
                $totalDiscount += $detail->discount;
                $totalTaxes += $detail->taxes;
                $totalNetto += $detail->subtotal;
            }
            
            // 4. Update total di header transfer
            $transfer->update([
                'bruto_from_permintaan' => $totalBruto,
                'diskon_from_permintaan' => $totalDiscount,
                'pajak_from_permintaan' => $totalTaxes,
                'netto_from_permintaan' => $totalNetto,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil disinkronkan dari permintaan.']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal sync detail transfer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi data: ' . $e->getMessage()], 500);
        }
    }
    
    // Helper untuk hitung ulang total (saat tambah/hapus manual)
    protected function recalculateTransferTotal($transferId)
    {
        $transfer = TransferHeader::with('details')->findOrFail($transferId);
        
        $totalBruto = $transfer->details->sum(function($detail) {
            return $detail->Trx_QtyTrx * $detail->trx_cogs;
        });
        $totalDiscount = $transfer->details->sum('trx_discount');
        $totalTaxes = $transfer->details->sum('trx_taxes');
        $totalNetto = $transfer->details->sum('trx_nettprice');

        $transfer->update([
            'bruto_from_permintaan' => $totalBruto,
            'diskon_from_permintaan' => $totalDiscount,
            'pajak_from_permintaan' => $totalTaxes,
            'netto_from_permintaan' => $totalNetto,
        ]);
    }
    
    // Helper untuk nomor transaksi
    private function generateTrxNumber(Carbon $transactionDate)
    {
        $prefix = 'GT-'; // Gudang Transfer
        $date = $transactionDate->format('dmy'); // Format dmy
        
        $lastTrx = TransferHeader::where('trx_number', 'like', $prefix . $date . '%')
                                 ->latest('Trx_Auto') // Urutkan berdasarkan PK
                                 ->first();

        if (!$lastTrx) {
            return $prefix . $date . '001';
        }
        $lastNumber = (int)substr($lastTrx->trx_number, -3);
        return $prefix . $date . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}