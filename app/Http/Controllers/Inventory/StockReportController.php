<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Dtproduk;
use App\Models\MutasiGudang\Warehouse;
use Illuminate\Support\Facades\Auth;

class StockReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user(); 
        
        // 1. Ambil daftar ID gudang yang bisa diakses user, cth: ["0"] atau ["1"]
        $accessibleWarehouses = $user->warehouse_access ?? [];
        
        // 2. LOGIKA BARU ANDA: Cek apakah "0" ada di dalam daftar akses
        //    Kita cek "0" (string) dan 0 (angka) untuk keamanan
        $isSuperAdmin = in_array("0", $accessibleWarehouses) || in_array(0, $accessibleWarehouses);
        $isSuperAdmin = ($user->role_id == 1);
        
        // 3. Memulai query ke tabel produk (m_product)
        $query = Dtproduk::with('warehouse', 'supplier');

        // 4. Ambil ID gudang yang dipilih dari dropdown filter
        $filterWarehouseID = $request->input('WARE_Auto'); 

        if ($isSuperAdmin) {
            // --- Logika Super Admin (Akses "0") ---
            
            // Dropdown bisa lihat semua gudang
            $userWarehouses = Warehouse::all();
            
            // Filter HANYA JIKA Super Admin memilih gudang (dan bukan 'all')
            if ($filterWarehouseID && $filterWarehouseID != 'all') {
                $query->where('WARE_Auto', $filterWarehouseID);
            }
            // Jika filter 'all' atau kosong, tidak ada filter (tampilkan semua stok)

        } else {
            // --- Logika User Biasa (Akses "1", "2", dll) ---
            
            // Dropdown HANYA bisa lihat gudang yang diizinkan
            $userWarehouses = Warehouse::whereIn('WARE_Auto', $accessibleWarehouses)->get();
            
            // FILTER DASAR (WAJIB)
            // Paksa query HANYA ambil data dari gudang yang diizinkan
            $query->whereIn('WARE_Auto', $accessibleWarehouses); 
            

            // (Filter tambahan jika user memilih dari dropdown - sudah aman)
            if ($filterWarehouseID && $filterWarehouseID != 'all') {
                 $query->where('WARE_Auto', $filterWarehouseID);
            }
        }

        // 5. Ambil data stok setelah difilter
        $stocks = $query->get();
        
        // 6. Atur dropdown agar "mengingat" pilihan terakhir
        $selectedWarehouse = $request->input('WARE_Auto'); 

        // 7. Atur nilai default dropdown saat halaman pertama kali dibuka
        if ($isSuperAdmin) {
            if ($selectedWarehouse == null) {
                $selectedWarehouse = 'all'; // Default Super Admin adalah 'all'
            }
        } else {
            // Default user biasa adalah gudang pertama yang dia miliki
            if ($selectedWarehouse == null && count($accessibleWarehouses) > 0) {
                $selectedWarehouse = $accessibleWarehouses[0];
            }
        }
        
        // 8. Kirim semua data ke view
        return view('inventory.stock_report.index', compact(
            'stocks', 
            'userWarehouses', 
            'selectedWarehouse',
            'isSuperAdmin'
        ));
    }
}