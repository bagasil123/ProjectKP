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
        $accessibleWarehouses = $user->warehouse_access ?? [];
        
        // 1. DEFINISIKAN VARIABEL $isSuperAdmin
        $isSuperAdmin = ($user->role_id == 1);
        
        $query = Dtproduk::with('warehouse', 'supplier');
        $filterWarehouseID = $request->input('WARE_Auto'); 

        if ($isSuperAdmin) {
            // --- Logika Super Admin (role_id == 1) ---
            $userWarehouses = Warehouse::all();
            
            if ($filterWarehouseID && $filterWarehouseID != 'all') {
                $query->where('WARE_Auto', $filterWarehouseID);
            }

        } else {
            // --- Logika User Biasa (role_id BUKAN 1) ---
            $userWarehouses = Warehouse::whereIn('WARE_Auto', $accessibleWarehouses)->get();
            $query->whereIn('WARE_Auto', $accessibleWarehouses); 

            if ($filterWarehouseID && $filterWarehouseID != 'all') {
                 $query->where('WARE_Auto', $filterWarehouseID);
            }
        }

        $stocks = $query->get();
        $selectedWarehouse = $request->input('WARE_Auto'); 

        if ($isSuperAdmin) {
            if ($selectedWarehouse == null) {
                $selectedWarehouse = 'all'; 
            }
        } else {
            if ($selectedWarehouse == null && count($accessibleWarehouses) > 0) {
                $selectedWarehouse = $accessibleWarehouses[0];
            }
        }
        
        // 2. KIRIM VARIABEL $isSuperAdmin KE VIEW
        return view('inventory.stock_report.index', compact(
            'stocks', 
            'userWarehouses', 
            'selectedWarehouse',
            'isSuperAdmin' // <-- TAMBAHKAN INI
        ));
    }
}