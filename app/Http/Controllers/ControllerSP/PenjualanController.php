<?php

namespace App\Http\Controllers\ControllerSP;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SPModels\Penjualan;
use App\Models\SPModels\PenjualanDetail;
use App\Models\SPModels\Pelanggan;
use App\Models\SPModels\CustomerOrder;
use App\Models\Product; // <-- Tambahkan ini untuk menggunakan model Product
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception; // <-- Tambahkan ini untuk menangani exception

class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar jualan.
     */
    public function index()
    {
        $jualans = Penjualan::with(['pelanggan', 'customerOrder'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $pelanggans = Pelanggan::where('status', 'Aktif')->orderBy('anggota')->get();
        
        return view('SistemPenjualan.Penjualan', compact('jualans', 'pelanggans'));
    }

    /**
     * Menyimpan data jualan baru dan mengurangi stok produk.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id' => 'required|exists:daftarpelanggan,id',
            'customer_order_id' => 'required|exists:customer_orders,id',
            'tgl_kirim' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Gunakan transaksi database untuk memastikan semua operasi berhasil atau gagal bersamaan
        DB::beginTransaction();
        try {
            $customerOrder = CustomerOrder::with('details.product')->find($request->customer_order_id);
            if (!$customerOrder) {
                throw new Exception('Customer Order tidak ditemukan.');
            }
            $coDetailsMap = $customerOrder->details->keyBy('id');

            // Kalkulasi total
            $bruto = 0;
            $totalDisc = 0;
            $totalPajak = 0;
            foreach ($request->items as $coDetailId => $itemData) {
                if (!isset($coDetailsMap[$coDetailId])) continue;
                $detail = $coDetailsMap[$coDetailId];
                $hargaTotalItem = (float)$itemData['qty'] * (float)$detail->harga;
                $bruto += $hargaTotalItem;
                $totalDisc += $hargaTotalItem * ((float)$itemData['disc'] / 100);
                $totalPajak += (float)$itemData['pajak'];
            }
            $netto = $bruto - $totalDisc + $totalPajak;

            // Buat header Penjualan
            $jualan = Penjualan::create([
                'no_jualan' => $this->generateJualanNumber(),
                'customer_order_id' => $request->customer_order_id,
                'pelanggan_id' => $request->pelanggan_id,
                'tgl_kirim' => $request->tgl_kirim,
                'jatuh_tempo' => $request->jatuh_tempo,
                'po_pelanggan' => $customerOrder->po_pelanggan,
                'bruto' => $bruto,
                'total_disc' => $totalDisc,
                'total_pajak' => $totalPajak,
                'netto' => $netto,
                'pengguna' => Auth::user()->name,
                'status' => 'Draft',
            ]);
            
            // Simpan detail dan kurangi stok
            foreach ($request->items as $coDetailId => $itemData) {
                if (isset($coDetailsMap[$coDetailId])) {
                    $detail = $coDetailsMap[$coDetailId];
                    $qtyJual = (float)$itemData['qty'];

                    // --- LOGIKA PENGURANGAN STOK DIMULAI DI SINI ---
                    $product = Product::find($detail->product_id);

                    if (!$product) {
                        throw new Exception("Produk dengan ID {$detail->product_id} tidak ditemukan.");
                    }

                    // Periksa apakah stok mencukupi
                    if ($product->stok < $qtyJual) {
                        // Jika tidak cukup, batalkan transaksi
                        throw new Exception("Stok untuk produk '{$product->nama}' tidak mencukupi. Sisa stok: {$product->stok}.");
                    }

                    // Kurangi stok dan simpan
                    $product->stok -= $qtyJual;
                    $product->save();
                    // --- LOGIKA PENGURANGAN STOK SELESAI ---

                    PenjualanDetail::create([
                        'penjualan_id' => $jualan->id,
                        'product_id' => $detail->product_id,
                        'qty' => $qtyJual,
                        'satuan' => $detail->satuan,
                        'harga' => $detail->harga,
                        'disc' => $itemData['disc'],
                        'pajak' => $itemData['pajak'],
                        'nominal' => (float)$itemData['qty'] * (float)$detail->harga,
                        'catatan' => $itemData['catatan'],
                    ]);
                }
            }
            
            DB::commit(); // Jika semua berhasil, simpan perubahan ke database

            return response()->json(['message' => 'Data Penjualan berhasil disimpan dengan No: ' . $jualan->no_jualan]);

        } catch (Exception $e) {
            DB::rollBack(); // Jika ada error (misal: stok tidak cukup), batalkan semua operasi
            // Kirim pesan error yang jelas ke pengguna
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    
    // --- API METHODS ---

    public function getOutstandingOrders(Pelanggan $pelanggan)
    {
        $orders = CustomerOrder::where('pelanggan_id', $pelanggan->id)
            ->where('status', '!=', 'Selesai')
            ->get(['id', 'no_order', 'po_pelanggan']);
            
        return response()->json($orders);
    }
    
    public function getOrderDetails(CustomerOrder $customerOrder)
    {
        $details = $customerOrder->details()->with('product')->get();
        return response()->json($details);
    }

    /**
     * Fungsi helper untuk membuat nomor jualan.
     */
    private function generateJualanNumber()
    {
        $prefix = 'JUAL/' . date('Ym') . '/';
        $last = Penjualan::where('no_jualan', 'like', $prefix . '%')->latest('id')->first();
        $sequence = $last ? (int) substr($last->no_jualan, -4) + 1 : 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
