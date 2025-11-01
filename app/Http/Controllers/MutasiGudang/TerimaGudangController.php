<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MutasiGudang\TerimaGudangHeader;
use App\Models\MutasiGudang\TerimaGudangDetail;
use App\Models\MutasiGudang\TransferHeader;;   // Untuk mengambil data user
use App\Models\MutasiGudang\Warehouse; // Pastikan nama model ini benar
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TerimaGudangController extends Controller
{
    /**
     * Menampilkan halaman daftar penerimaan.
     */
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1); // Cek Super Admin
        $accessibleWarehouses = $user->warehouse_access ?? []; // Ambil hak akses

        // Ambil query dasar untuk Terima Gudang (th_slsgtrcv)
        $query = \App\Models\MutasiGudang\TerimaGudangHeader::with('gudangPengirim', 'gudangPenerima');

        // Jika BUKAN Super Admin, terapkan filter
        if (!$isSuperAdmin) {
            // User harus bisa akses Gudang Pengirim (WH_Send)
            // ATAU Gudang Penerima (WH_Rcv)
            $query->where(function ($q) use ($accessibleWarehouses) {
                $q->whereIn('WH_Send', $accessibleWarehouses)
                  ->orWhereIn('WH_Rcv', $accessibleWarehouses);
            });
        }
        
        $terimas = $query->orderBy('Pur_Auto', 'desc')->get();
        
        // Ambil semua gudang untuk dropdown filter di halaman (jika ada)
        $warehouses = Warehouse::all(); 

        return view('mutasigudang.terimagudang.index', compact('terimas', 'warehouses'));
    }

    /**
     * Menampilkan halaman form untuk membuat penerimaan baru.
     */
    public function create()
    {
        // Ambil daftar transfer yang bisa dipilih
        // Pastikan nama kolom 'trx_posting' dan valuenya 'T' sesuai dengan tabel transfer Anda
        $postedTransfers = TransferHeader::where('trx_posting', 'T')
            ->whereDoesntHave('penerimaan') // Opsi: Hanya tampilkan transfer yang belum pernah dibuatkan penerimaannya
            ->get();

        // PERBAIKAN: Buat objek kosong agar view bisa merender form create
        $penerimaan = new TerimaGudangHeader();

        // PERBAIKAN: Mengirim ke view 'index' yang sama, bukan 'create'
        return view('mutasigudang.terimagudang.index', compact('penerimaan', 'postedTransfers'));
    }

    /**
     * Menampilkan form untuk mengedit penerimaan yang sudah ada.
     */
    public function edit($id)
    {
        // Cari data penerimaan yang akan diedit, beserta detailnya
        $penerimaan = TerimaGudangHeader::with('details')->findOrFail($id);

        // Ambil daftar transfer (diperlukan untuk menampilkan nomor ref di dropdown, meskipun disabled)
        // Pastikan nama kolom 'trx_posting' dan valuenya 'T' sesuai dengan tabel transfer Anda
        $postedTransfers = TransferHeader::where('trx_posting', 'T')->get();

        return view('mutasigudang.terimagudang.index', compact('penerimaan', 'postedTransfers'));
    }

    /**
     * Menyimpan data penerimaan baru dari form.
     */
    public function store(Request $request)
    {
        // (Validasi Anda sudah cukup baik, kita gunakan itu)
        $request->validate([
            'Rcv_Date' => 'required|date',
            'ref_trx_auto' => 'required|numeric',
            'details' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // =====================================================================
            // == LOGIKA PEMBUATAN NOMOR PENERIMAAN BARU ==
            // =====================================================================

            // 1. Ambil tanggal transaksi dari request form.
            $transactionDate = new \DateTime($request->Rcv_Date);

            // 2. Cari penerimaan terakhir yang dibuat pada tanggal yang sama.
            $lastPenerimaanToday = TerimaGudangHeader::whereDate('Rcv_Date', $transactionDate->format('Y-m-d'))
                                                    ->latest('id') // Urutkan berdasarkan ID terbaru
                                                    ->first();

            $nextSequence = 1; // 3. Set nomor urut default ke 1.

            // 4. Jika ada penerimaan di hari ini, hitung nomor urut berikutnya.
            if ($lastPenerimaanToday) {
                // Ambil 3 digit terakhir dari nomor sebelumnya (misal: '005')
                $lastSequence = (int) substr($lastPenerimaanToday->Rcv_number, -3);
                $nextSequence = $lastSequence + 1;
            }

            // 5. Format tanggal (DDMMYY) dan nomor urut (001, 015, 123).
            $datePart = $transactionDate->format('dmy');
            $sequencePart = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

            // 6. Gabungkan semua bagian menjadi nomor penerimaan yang final.
            $newRcvNumber = 'RCV-' . $datePart . $sequencePart;
            // =====================================================================
            // == AKHIR LOGIKA PEMBUATAN NOMOR ==
            // =====================================================================
            $isPosting = $request->input('action') === 'save_post';

            $header = TerimaGudangHeader::create([
                'Rcv_number' => $newRcvNumber,
                'ref_trx_auto' => $request->ref_trx_auto,
                'user_id' => Auth::id(),
                'Rcv_Date' => $request->Rcv_Date,
                'Rcv_WareCode' => $request->Rcv_WareCode,
                'Rcv_From' => $request->Rcv_From,
                'Rcv_Note' => $request->Rcv_Note,
                'rcv_posting' => $isPosting ? 'T' : 'F', // Status 'T' jika posting, 'F' jika draft
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
            }

            DB::commit();
            return redirect()->route('terimagudang.index')->with('success', 'Penerimaan barang berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Memperbarui penerimaan yang ada (khusus draft).
     */
    public function update(Request $request, $id)
    {
        $header = TerimaGudangHeader::findOrFail($id);

        if ($header->rcv_posting === 'T') {
            return redirect()->back()->with('error', 'Penerimaan sudah di-posting dan tidak bisa diubah.');
        }

        DB::beginTransaction();
        try {
            $isPosting = $request->input('action') === 'save_post';

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
            }

            DB::commit();
            $message = $isPosting ? 'Penerimaan berhasil diposting.' : 'Draft penerimaan berhasil diperbarui.';
            return redirect()->route('terimagudang.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat update: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus draft penerimaan.
     */
    public function destroy($id)
    {
        $header = TerimaGudangHeader::findOrFail($id);

        if ($header->rcv_posting === 'T') {
            return redirect()->back()->with('error', 'Penerimaan yang sudah di-posting tidak dapat dihapus.');
        }

        // Transaksi untuk memastikan detail dan header terhapus bersamaan
        DB::transaction(function () use ($header) {
            $header->details()->delete();
            $header->delete();
        });

        return redirect()->route('terimagudang.index')->with('success', 'Draft penerimaan berhasil dihapus.');
    }

    /**
     * Mengambil detail transfer untuk AJAX.
     */
    public function getTransferDetails($transferId)
    {
        $transfer = TransferHeader::with('details')->find($transferId);

        if (!$transfer) {
            return response()->json(['error' => 'Transfer tidak ditemukan'], 404);
        }

        return response()->json($transfer);
    }
}
