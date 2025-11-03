<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MutasiGudang\TerimaGudangHeader;
use App\Models\MutasiGudang\TerimaGudangDetail;
use App\Models\MutasiGudang\TransferHeader;
use App\Models\MutasiGudang\Warehouse; 
use App\Models\Inventory\Dtproduk; // <-- TAMBAHKAN INI UNTUK UPDATE STOK
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // <-- TAMBAHKAN INI
use Exception; // <-- TAMBAHKAN INI

class TerimaGudangController extends Controller
{
    
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1); 
        $accessibleWarehouses = $user->warehouse_access ?? []; 

        $query = \App\Models\MutasiGudang\TerimaGudangHeader::with('transferHeader');

        if (!$isSuperAdmin) {
            // (PERBAIKAN) Filter berdasarkan ID Gudang
            // Ambil ID gudang yang bisa diakses dari transferHeader (Trx_RcvNo)
            // atau dari terimaHeader (ref_trx_auto -> transferHeader.Trx_RcvNo)
            
            // Kita perlu mengambil ID gudang yang bisa diakses user
            // $accessibleWarehouseIds = $accessibleWarehouses; // Ini sudah ID

            // Ambil ID Transfer yang tujuannya bisa diakses user
            $accessibleTransferIds = TransferHeader::whereIn('Trx_RcvNo', $accessibleWarehouses)
                                                    ->pluck('Trx_Auto');

            $query->whereIn('ref_trx_auto', $accessibleTransferIds);
        }
        
        $penerimaanList = $query->orderBy('id', 'desc')->paginate(15);
        $warehouses = Warehouse::all(); 

        return view('mutasigudang.terimagudang.index', compact('penerimaanList', 'warehouses'));
    }

    
    public function create()
    {
        // SEMUA AKUN BISA AKSES SEMUA GUDANG
        $warehouses = Warehouse::all(); // Ambil semua gudang tanpa filter
        
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];

        // Ambil daftar transfer yang bisa dipilih
        $transferQuery = TransferHeader::where('trx_posting', 'T')
            ->whereDoesntHave('penerimaan'); // Hanya yg belum diterima
                
        if (!$isSuperAdmin) {
            // Tapi untuk transfer, tetap filter berdasarkan akses
            $transferQuery->whereIn('Trx_RcvNo', $accessibleWarehouses);
        }

        $postedTransfers = $transferQuery->get();
        $penerimaan = new TerimaGudangHeader();

        return view('mutasigudang.terimagudang.index', compact('penerimaan', 'postedTransfers', 'warehouses'));
    }


    
    public function edit($id)
    {
        $penerimaan = TerimaGudangHeader::with('details')->findOrFail($id);

        // SEMUA AKUN BISA AKSES SEMUA GUDANG
        $warehouses = Warehouse::all(); // Ambil semua gudang tanpa filter
        
        // Ambil SEMUA transfer posted
        $postedTransfers = TransferHeader::where('trx_posting', 'T')->get();

        return view('mutasigudang.terimagudang.index', compact('penerimaan', 'postedTransfers', 'warehouses'));
    }


    
    public function store(Request $request)
    {
        $request->validate([
            'Rcv_Date' => 'required|date',
            'ref_trx_auto' => 'required|numeric|exists:th_slsgt,Trx_Auto',
            'details' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // ... (Logika generate Rcv_number Anda sudah benar) ...
            $transactionDate = new \DateTime($request->Rcv_Date);
            $lastPenerimaanToday = TerimaGudangHeader::whereDate('Rcv_Date', $transactionDate->format('Y-m-d'))
                                                    ->latest('id')
                                                    ->first();
            $nextSequence = 1; 
            if ($lastPenerimaanToday) {
                $lastSequence = (int) substr($lastPenerimaanToday->Rcv_number, -3);
                $nextSequence = $lastSequence + 1;
            }
            $datePart = $transactionDate->format('dmy');
            $sequencePart = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            $newRcvNumber = 'RCV-' . $datePart . $sequencePart;
            // ... (Akhir logika Rcv_number) ...

            $isPosting = $request->input('action') === 'save_post';

            // Ambil data transfer untuk dapat ID gudang tujuan
            $transfer = TransferHeader::findOrFail($request->ref_trx_auto);
            $destinationWarehouseId = $transfer->Trx_RcvNo;

            $header = TerimaGudangHeader::create([
                'Rcv_number' => $newRcvNumber,
                'ref_trx_auto' => $request->ref_trx_auto,
                'Rcv_UserID' => Auth::id(), 
                'Rcv_Date' => $request->Rcv_Date,
                // (PERBAIKAN) Simpan NAMA gudang, sesuai model TerimaGudang
                'Rcv_WareCode' => $transfer->gudangPenerima->WARE_Name ?? null, 
                'Rcv_From'     => $transfer->gudangPengirim->WARE_Name ?? null,
                'Rcv_Note' => $request->Rcv_Note,
                'rcv_posting' => $isPosting ? 'T' : 'F', 
            ]);

            // Simpan detail
            foreach ($request->details as $item) {
                TerimaGudangDetail::create([
                    'terima_gudang_id' => $header->id,
                    'Rcv_ProdCode' => $item['Rcv_ProdCode'],
                    'Rcv_prodname' => $item['Rcv_prodname'],
                    'Rcv_uom' => $item['Rcv_uom'],
                    'Rcv_Qty_Sent' => $item['Rcv_Qty_Sent'],
                    'Rcv_Qty_Received' => $item['Rcv_Qty_Received'],
                    'Rcv_Qty_Rejected' => $item['Rcv_Qty_Rejected'] ?? 0,
                    'Rcv_cogs' => $item['Rcv_cogs'],
                    'Rcv_subtotal' => ($item['Rcv_Qty_Received'] * $item['Rcv_cogs']),
                ]);

                // (LOGIKA BARU) Jika di-posting, tambahkan stok ke gudang tujuan
                if ($isPosting && $item['Rcv_Qty_Received'] > 0) {
                    $this->addStockToWarehouse(
                        $item['Rcv_ProdCode'],
                        $destinationWarehouseId,
                        $item['Rcv_Qty_Received']
                    );
                }
            }

            DB::commit();
            $message = $isPosting ? 'Penerimaan berhasil diposting dan stok telah diperbarui.' : 'Draft penerimaan berhasil disimpan.';
            return redirect()->route('terimagudang.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan penerimaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    
    public function update(Request $request, $id)
    {
        $header = TerimaGudangHeader::with('transferHeader')->findOrFail($id);

        if ($header->rcv_posting === 'T') {
            return redirect()->back()->with('error', 'Penerimaan sudah di-posting dan tidak bisa diubah.');
        }
        
        $request->validate([
            'Rcv_Date' => 'required|date',
            'details' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $isPosting = $request->input('action') === 'save_post';
            
            // Dapatkan ID gudang tujuan dari transfer yang terhubung
            $destinationWarehouseId = $header->transferHeader->Trx_RcvNo;
            if (!$destinationWarehouseId) {
                throw new Exception("Gagal menemukan gudang tujuan dari referensi transfer.");
            }

            // Update header
            $header->update([
                'Rcv_Date' => $request->Rcv_Date,
                'Rcv_Note' => $request->Rcv_Note,
                'rcv_posting' => $isPosting ? 'T' : 'F',
            ]);

            // Hapus detail lama, lalu buat yang baru dari request
            $header->details()->delete();
            foreach ($request->details as $item) {
                TerimaGudangDetail::create([
                    'terima_gudang_id' => $header->id,
                    'Rcv_ProdCode' => $item['Rcv_ProdCode'],
                    'Rcv_prodname' => $item['Rcv_prodname'],
                    'Rcv_uom' => $item['Rcv_uom'],
                    'Rcv_Qty_Sent' => $item['Rcv_Qty_Sent'],
                    'Rcv_Qty_Received' => $item['Rcv_Qty_Received'],
                    'Rcv_Qty_Rejected' => $item['Rcv_Qty_Rejected'] ?? 0,
                    'Rcv_cogs' => $item['Rcv_cogs'],
                    'Rcv_subtotal' => ($item['Rcv_Qty_Received'] * $item['Rcv_cogs']),
                ]);

                // (LOGIKA BARU) Jika di-posting, tambahkan stok ke gudang tujuan
                if ($isPosting && $item['Rcv_Qty_Received'] > 0) {
                    $this->addStockToWarehouse(
                        $item['Rcv_ProdCode'],
                        $destinationWarehouseId,
                        $item['Rcv_Qty_Received']
                    );
                }
            }

            DB::commit();
            $message = $isPosting ? 'Penerimaan berhasil diposting dan stok telah diperbarui.' : 'Draft penerimaan berhasil diperbarui.';
            return redirect()->route('terimagudang.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update penerimaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat update: ' . $e->getMessage())->withInput();
        }
    }

    
    public function destroy($id)
    {
        $header = TerimaGudangHeader::findOrFail($id);
        if ($header->rcv_posting === 'T') {
            return redirect()->back()->with('error', 'Penerimaan yang sudah di-posting tidak dapat dihapus.');
        }
        DB::transaction(function () use ($header) {
            $header->details()->delete();
            $header->delete();
        });
        return redirect()->route('terimagudang.index')->with('success', 'Draft penerimaan berhasil dihapus.');
    }

    
    public function getTransferDetails($transferId)
    {
        try {
            Log::info("Getting transfer details for ID: {$transferId}");
            
            $transfer = TransferHeader::with([
                'details.produk', 
                'gudangPengirim', 
                'gudangPenerima'
            ])->find($transferId);

            if (!$transfer) {
                Log::error("Transfer not found: {$transferId}");
                return response()->json(['error' => 'Transfer tidak ditemukan'], 404);
            }

            // DEBUG: Cek data relasi
            Log::info("Transfer found: {$transfer->trx_number}");
            Log::info("Gudang Pengirim ID: {$transfer->Trx_WareCode}");
            Log::info("Gudang Penerima ID: {$transfer->Trx_RcvNo}");
            Log::info("Gudang Pengirim Object: " . ($transfer->gudangPengirim ? 'Exists' : 'NULL'));
            Log::info("Gudang Penerima Object: " . ($transfer->gudangPenerima ? 'Exists' : 'NULL'));

            if ($transfer->gudangPengirim) {
                Log::info("Gudang Pengirim Name: {$transfer->gudangPengirim->WARE_Name}");
            }
            if ($transfer->gudangPenerima) {
                Log::info("Gudang Penerima Name: {$transfer->gudangPenerima->WARE_Name}");
            }

            $data = $transfer->toArray();
            
            // Pastikan nama gudang terbaca
            $data['Trx_WareCode_name'] = $transfer->gudangPengirim->WARE_Name ?? 'N/A';
            $data['Trx_RcvNo_name'] = $transfer->gudangPenerima->WARE_Name ?? 'N/A';

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error("Error in getTransferDetails: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
        
    /**
 * (PERBAIKAN) Menambah/membuat stok di gudang tujuan
 * Dengan composite key: kode_produk + WARE_Auto
 */
    private function addStockToWarehouse($prodCode, $warehouseId, $qty)
    {
        if ($qty <= 0) return;

        DB::beginTransaction();
        try {
            Log::info("Adding stock: Product {$prodCode}, Warehouse {$warehouseId}, Qty {$qty}");

            // 1. Cari stok produk di gudang TUJUAN dengan composite key
            $stock = Dtproduk::where('kode_produk', $prodCode)
                            ->where('WARE_Auto', $warehouseId)
                            ->lockForUpdate()
                            ->first();
                            
            if ($stock) {
                // 2. Jika sudah ada, tambahkan stoknya
                $oldQty = $stock->qty;
                $stock->qty += $qty;
                $stock->save();
                
                Log::info("Stock updated: {$prodCode} in warehouse {$warehouseId} from {$oldQty} to {$stock->qty}");
            } else {
                // 3. Jika belum ada, buat baris stok baru
                // Ambil data produk dari gudang lain sebagai template
                $productTemplate = Dtproduk::where('kode_produk', $prodCode)->first();
                
                if (!$productTemplate) {
                    throw new Exception("Produk dengan kode {$prodCode} tidak ditemukan di sistem.");
                }
                
                $newStock = Dtproduk::create([
                    'kode_produk' => $prodCode,
                    'nama_produk' => $productTemplate->nama_produk,
                    'supplier_id' => $productTemplate->supplier_id,
                    'qty' => $qty,
                    'harga_beli' => $productTemplate->harga_beli,
                    'harga_jual' => $productTemplate->harga_jual,
                    'WARE_Auto' => $warehouseId, // INI YANG MEMBEDAKAN
                    'kelompok' => $productTemplate->kelompok,
                    'satuan' => $productTemplate->satuan,
                ]);
                
                Log::info("New stock created: {$prodCode} in warehouse {$warehouseId} with qty {$qty}");
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add stock: " . $e->getMessage());
            throw $e;
        }
    }

}