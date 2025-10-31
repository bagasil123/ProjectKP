<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Http\Controllers\Controller;
use App\Models\MutasiGudang\GudangOrder;
use App\Models\MutasiGudang\GudangOrderDetail;
use App\Models\MutasiGudang\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;



class GudangOrderController extends Controller
{

    private function generateOrderNumber()
    {
        $today = Carbon::now()->format('dm y'); // 301225
        $prefix = 'PG-' . str_replace(' ', '', $today); // PG-301225

        // Hitung jumlah orderan hari ini
        $todayDate = Carbon::now()->toDateString(); // Format Y-m-d

        $count = DB::table('th_gudangorder')
            ->whereDate('Pur_Date', $todayDate)
            ->count();

        // Tambahkan 1 untuk urutan baru
        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT); // 001, 002, dst

        return $prefix . $nextNumber; // PG-301225001
    }
    public function index()
    {
        $orders = GudangOrder::orderBy('Pur_Date', 'desc')->paginate(15);
        return view('mutasigudang.gudangorder.index', compact('orders'));
    }


    public function create()
    {
        // 1. Buat record baru untuk mendapatkan ID (Pur_Auto)
        $newOrder = GudangOrder::create([
            'pur_ordernumber' => $this->generateOrderNumber(), // Nilai sementara
            'Pur_Date' => Carbon::now(),
            'pur_status' => 'draft',
            'pur_emp' => Auth::user()->name,
            'pur_warehouse' => '', // Dikosongkan agar dipilih di form
            'pur_destination' => '', // Dikosongkan agar dipilih di form
        ]);

        $newOrder->save();

        // 3. Redirect ke halaman edit dengan membawa ID order
        return redirect()->route('gudangorder.edit', ['id' => $newOrder->Pur_Auto]);
    }

    /**
     * Modifikasi: Mengganti nama view ke view yang lebih spesifik untuk form (edit.blade.php)
     * Pastikan data 'warehouses' sudah dikirim ke view.
     */
    public function edit($id)
    {
        $order = GudangOrder::with('details')->findOrFail($id);
        $warehouses = Warehouse::all(); // Mengambil semua data gudang

        // Pastikan Anda memiliki view 'edit.blade.php' di dalam folder 'mutasigudang.gudangorder'
        // Mengirim data order dan warehouses ke view
        return view('mutasigudang.gudangorder.index', compact('order', 'warehouses'));
    }

    public function show($id)
    {
        $order = GudangOrder::with('details')->findOrFail($id);
        $warehouses = Warehouse::all(); // Mengambil semua data gudang

        // Pastikan Anda memiliki view 'show.blade.php' untuk menampilkan detail
        return view('mutasigudang.gudangorder.index', [
            'order'      => $order,
            'warehouses' => $warehouses,
            'showMode'   => true
        ]);
    }

    /**
     * Modifikasi: Validasi untuk 'pur_destination' ditambahkan.
     */
    public function updateHeader(Request $request, $id)
    {
        $order = GudangOrder::findOrFail($id);

        if ($order->pur_status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Hanya order draft yang bisa diubah.'], 403);
        }

        // Validasi input dari form
        $validatedData = $request->validate([
            'pur_ordernumber'   => 'required|string|max:255',
            'Pur_Date'          => 'required|date',
            'pur_warehouse'     => 'required|string|max:255', // Lokasi Asal
            'pur_destination'   => 'required|string|max:255', // Lokasi Tujuan
            'Pur_Note'          => 'nullable|string',
        ]);

        $order->update($validatedData);

        return response()->json(['success' => true, 'message' => 'Header berhasil diperbarui.']);
    }

    // --- FUNGSI LAINNYA (storeDetail, destroy, destroyDetail, submit) TETAP SAMA ---
    // ... (kode fungsi lainnya tidak diubah)
    public function destroy($id)
    {
        // 1. Temukan order berdasarkan ID. Jika tidak ada, Laravel akan otomatis menampilkan error 404.
        $order = GudangOrder::findOrFail($id);

        // 2. Lakukan validasi. Hanya order dengan status 'draft' yang boleh dihapus.
        if ($order->pur_status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya permintaan dengan status DRAFT yang dapat dihapus.'
            ], 403); // Kode 403 artinya "Forbidden" atau tidak diizinkan.
        }

        // 3. Mulai transaksi database untuk menjaga integritas data.
        DB::beginTransaction();

        try {
            // 4. Hapus semua record detail yang terkait dengan order ini terlebih dahulu.
            // Ini penting untuk menghindari error foreign key constraint.
            $order->details()->delete();

            // 5. Setelah semua detail berhasil dihapus, hapus data header order.
            $order->delete();

            // 6. Jika tidak ada error, konfirmasi semua perubahan ke database.
            DB::commit();

            // 7. Kirim respons sukses kembali ke JavaScript.
            return response()->json([
                'success' => true,
                'message' => 'Draft permintaan berhasil dihapus.'
            ]);

        } catch (Exception $e) {
            // 8. Jika terjadi kesalahan di tengah proses (langkah 4 atau 5),
            // batalkan semua query yang sudah dijalankan.
            DB::rollBack();

            // 9. Kirim respons error kembali ke JavaScript, sertakan pesan error untuk debugging.
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ], 500); // Kode 500 artinya "Internal Server Error".
        }
    }

    public function storeDetail(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Pur_Auto' => 'required|exists:th_gudangorder,Pur_Auto',
                'Pur_ProdCode' => 'required|string',
                'pur_prodname' => 'required|string',
                'Pur_UOM' => 'required|string|max:50',
                'Pur_Qty' => 'required|numeric|min:1',
                'Pur_GrossPrice' => 'required|numeric|min:0',
                'Pur_Discount' => 'nullable|numeric|min:0', // Diskon bisa jadi 0 atau null
                'Pur_Taxes' => 'nullable|numeric|min:0',    // Pajak bisa jadi 0 atau null

            ]);

           // Ini adalah praktik terbaik untuk memastikan data akurat
        $qty = (float) $validatedData['Pur_Qty'];
        $grossPrice = (float) $validatedData['Pur_GrossPrice'];
        $discount = (float) ($validatedData['Pur_Discount'] ?? 0); // Default ke 0 jika null
        $taxes = (float) ($validatedData['Pur_Taxes'] ?? 0);       // Default ke 0 jika null

        $subtotal = $qty * $grossPrice;
        $netPrice = ($subtotal - $discount) + $taxes;

        // 3. Tambahkan harga bersih yang sudah dihitung ke dalam data yang akan disimpan
        $validatedData['Pur_NettPrice'] = $netPrice;

        // 4. Simpan data ke database
        $detail = GudangOrderDetail::create($validatedData);

        return response()->json(['success' => true, 'data' => $detail]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function destroyDetail($orderId, $detailId)
    {
        $detail = GudangOrderDetail::findOrFail($detailId);
        if ($detail->Pur_Auto != $orderId) {
            return response()->json(['success' => false, 'message' => 'Detail tidak sesuai dengan order.'], 403);
        }
        $detail->delete();
        return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus.']);
    }

    public function submit($id)
    {
        $order = GudangOrder::findOrFail($id);
        $order->pur_status = 'submitted';
        $order->save();
        return response()->json(['success' => true, 'message' => 'Order berhasil disubmit.']);
    }
}
