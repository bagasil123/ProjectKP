<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Models\MutasiGudang\TransferHeader;
use App\Models\MutasiGudang\TransferDetail;
use App\Models\MutasiGudang\Warehouse;
use App\Models\MutasiGudang\GudangOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferGudangController extends Controller
{


    /**
     * Menampilkan halaman daftar transfer.
     */
    public function index()
    {
        // Menggunakan latest() untuk mengurutkan berdasarkan data terbaru.
        $transfers = TransferHeader::latest('Trx_Auto')->paginate(15);
        return view('mutasigudang.transfergudang.index', compact('transfers'));
    }

    /**
     * Membuat draft transfer baru dan langsung redirect ke halaman edit.
     */
    public function create()
    {
        DB::beginTransaction();
        try {
            $transactionDate = now();
            $lastTransferToday = TransferHeader::whereDate('Trx_Date', $transactionDate->format('Y-m-d'))->latest('Trx_Auto')->first();
            $nextSequence = $lastTransferToday ? ((int) substr($lastTransferToday->trx_number, -3)) + 1 : 1;
            $newTrxNumber = 'TRF-' . $transactionDate->format('dmy') . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

            $transfer = TransferHeader::create([
                'trx_number'  => $newTrxNumber,
                'Trx_Date'    => $transactionDate,
                'trx_posting' => 'F', // Status Draft
            ]);

            DB::commit();
            return redirect()->route('transfergudang.edit', $transfer->Trx_Auto);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('transfergudang.index')->with('error', 'Gagal membuat draft baru: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form untuk mengedit draft transfer.
     */
    public function edit($id)
    {
        $transfer = TransferHeader::with(['details', 'permintaanGudang'])->findOrFail($id);
        $warehouses = Warehouse::all();
        $permintaanGudang = GudangOrder::where('pur_status', 'submitted')->orderBy('Pur_Date', 'desc')->get();
        return view('mutasigudang.transfergudang.index', compact('transfer', 'warehouses', 'permintaanGudang'));
    }

    /**
     * Memperbarui informasi header dari draft transfer.
     */
    public function updateHeader(Request $request, $id)
    {
        $transfer = TransferHeader::findOrFail($id);
        if ($transfer->trx_posting === 'F') {
            $transfer->update($request->only(['Trx_Date', 'Trx_WareCode', 'Trx_RcvNo', 'Trx_Note']));
            return response()->json(['success' => true, 'message' => 'Header diperbarui.']);
        }
        return response()->json(['success' => false, 'message' => 'Data sudah diposting.'], 403);
    }

    /**
     * Mengambil detail dari Permintaan Gudang via AJAX.
     */
    public function fetchPermintaanDetails($permintaanId)
    {
        try {
            $permintaan = GudangOrder::with('details')->findOrFail($permintaanId);
            return response()->json(['success' => true, 'data' => $permintaan]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Permintaan Gudang tidak ditemukan.'], 404);
        }
    }

    /**
     * Mengganti detail transfer dengan data dari Permintaan Gudang.
     */
    public function syncDetailsFromPermintaan(Request $request, $id)
    {
        $request->validate(['permintaan_id' => 'required|exists:th_gudangorder,Pur_Auto']);
        $transfer = TransferHeader::findOrFail($id);
        $permintaan = GudangOrder::with('details')->find($request->permintaan_id);

        DB::beginTransaction();
        try {
            $transfer->details()->delete();

            foreach ($permintaan->details as $pDetail) {
                TransferDetail::create([
                    'trx_number'    => $transfer->trx_number,
                    'Trx_Auto'      => $transfer->Trx_Auto,
                    'Trx_ProdCode'  => $pDetail->Pur_ProdCode,
                    'trx_prodname'  => $pDetail->pur_prodname,
                    'trx_uom'       => $pDetail->Pur_UOM,
                    'Trx_QtyTrx'    => $pDetail->Pur_Qty,
                    'trx_cogs'      => $pDetail->Pur_GrossPrice,
                    'trx_discount'  => $pDetail->Pur_Discount ?? 0,
                    'trx_taxes'     => $pDetail->Pur_Taxes ?? 0,
                    'trx_nettprice' => $pDetail->Pur_NettPrice ?? 0,
                ]);
            }
            $transfer->update([
                'ref_pur_auto' => $permintaan->Pur_Auto,
                'Trx_WareCode' => $permintaan->pur_warehouse,
                'Trx_RcvNo'    => $permintaan->pur_destination,
                'Trx_Note'     => 'Transfer berdasarkan Permintaan No: ' . $permintaan->pur_ordernumber,
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Detail berhasil disinkronkan.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan item detail baru yang ditambahkan secara manual.
     */
    public function storeDetail(Request $request)
    {
        $validated = $request->validate([
            'Trx_Auto' => 'required|exists:th_slsgt,Trx_Auto',
            'Trx_ProdCode' => 'required|string|max:50',
            'trx_prodname' => 'required|string|max:255',
            'trx_uom' => 'required|string',
            'Trx_QtyTrx' => 'required|numeric|min:0',
            'trx_cogs' => 'required|numeric|min:0',
            'trx_discount' => 'nullable|numeric|min:0',
            'trx_taxes' => 'nullable|numeric|min:0',
        ]);

        // [FIX] Ambil header untuk mendapatkan trx_number
        $transferHeader = TransferHeader::find($validated['Trx_Auto']);
        if (!$transferHeader) {
            return response()->json(['message' => 'Header transfer tidak ditemukan.'], 404);
        }

        // [FIX] Sertakan trx_number dari header ke dalam data yang divalidasi
        $validated['trx_number'] = $transferHeader->trx_number;

        // [FIX] Lakukan kalkulasi netto di backend untuk keamanan data
        $validated['trx_nettprice'] = ($validated['Trx_QtyTrx'] * $validated['trx_cogs']) - ($validated['trx_discount'] ?? 0) + ($validated['trx_taxes'] ?? 0);

        $detail = TransferDetail::create($validated);

        return response()->json(['success' => true, 'message' => 'Barang berhasil ditambahkan.']);
    }


    /**
     * Menghapus item detail dari draft transfer.
     */
    public function destroyDetail($transfer_id, $detail_id)
    {
        $detail = TransferDetail::findOrFail($detail_id);
        $detail->delete();
        return response()->json(['success' => true, 'message' => 'Item berhasil dihapus.']);
    }

    /**
     * Mengubah status transfer dari 'Draft' menjadi 'Posted'.
     */
    public function submit($id)
    {
        $transfer = TransferHeader::with('details')->findOrFail($id);
        if ($transfer->details->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa posting. Belum ada barang yang ditambahkan.'], 422);
        }
        $transfer->update(['trx_posting' => 'T']);
        return response()->json(['success' => true, 'message' => 'Transfer berhasil diposting.']);
    }

    /**
     * Menghapus seluruh draft transfer (header dan detail).
     */
    public function destroy($id)
    {
        $transfer = TransferHeader::findOrFail($id);
        if ($transfer->trx_posting === 'F') {
            DB::transaction(function () use ($transfer) {
                $transfer->details()->delete();
                $transfer->delete();
            });
            return response()->json(['success' => true, 'message' => 'Draft berhasil dihapus.']);
        }
        return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa dihapus.'], 403);
    }
}
