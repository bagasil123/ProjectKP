<?php

namespace App\Http\Controllers\Retur;

use App\Http\Controllers\Controller;
use App\Models\keamanan\Member;
use App\Models\Retur\ThTrxSalesRtr;
use App\Models\Retur\TdTrxSalesRtr;
use App\Models\SPModels\Pelanggan;
use App\Models\MutasiGudang\Warehouse;
use App\Models\Inventory\Dtproduk;
use App\Models\Inventory\SatuanProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ReturPenjualanController extends Controller
{
    // Tampilan index
    public function index()
    {
        return view('retur.penjualan.index');
    }

    // JSON untuk DataTables index
    public function dataJson(Request $request)
    {
        // Hanya ambil data dengan posting F atau T dan eager load user
        $query = ThTrxSalesRtr::with([
            'user:Mem_Auto,Mem_ID,Mem_UserName',
            'customer:id,kode,anggota'
        ])
            ->whereIn('trx_posting', ['F', 'T']);

        // Filter
        if ($request->filled('filter_date_from')) {
            $query->whereDate('Trx_Date', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('Trx_Date', '<=', $request->filter_date_to);
        }

        if ($request->filled('filter_sup_code')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('kode', 'like', '%' . $request->filter_sup_code . '%');
            });
        }

        $rows = $query->get();

        $data = $rows->map(function ($r) {
            // Gunakan relasi user yang sudah di-eager load
            // Prioritas: Mem_UserName > Mem_ID > Trx_UserID
            $userName = $r->Trx_UserID; // fallback

            if ($r->user) {
                if (!empty($r->user->Mem_UserName)) {
                    $userName = $r->user->Mem_UserName;
                } elseif (!empty($r->user->Mem_ID)) {
                    $userName = $r->user->Mem_ID; // Tampilkan custom ID untuk user
                }
            }

            return [
                'Trx_Auto'       => $r->Trx_Auto,
                'Trx_SupCode'    => $r->customer ? $r->customer->kode : $r->Trx_SupCode,
                'trx_number'     => $r->trx_number,
                'Trx_Date'       => $r->Trx_Date?->format('Y-m-d'),
                'Trx_GrossPrice' => number_format($r->Trx_GrossPrice, 2, ',', '.'),
                'Trx_TotDiscount' => number_format($r->Trx_TotDiscount, 2, ',', '.'),
                'Trx_Taxes'      => number_format($r->Trx_Taxes, 2, ',', '.'),
                'Trx_NettPrice'  => number_format($r->Trx_NettPrice, 2, ',', '.'),
                'Trx_UserID'     => $userName,
                'Trx_LastUpdate' => $r->Trx_LastUpdate?->format('Y-m-d H:i:s'),
                'trx_posting'    => $r->trx_posting,
            ];
        });
        return response()->json(['data' => $data]);
    }

    // Method untuk mengambil data customer untuk Select2
    public function getCustomers(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Pelanggan::query();

        // Jika ada search term, filter berdasarkan search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('anggota', 'like', "%{$search}%");
            });
        }

        $customers = $query->select('id', 'kode', 'anggota')
            ->orderBy('kode')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $customers->map(function ($customer) {
            return [
                'id' => $customer->id, // Gunakan primary key untuk relasi
                'text' => $customer->kode . ' - ' . $customer->anggota
            ];
        });

        return response()->json([
            'items' => $items,
            'total_count' => $customers->total()
        ]);
    }

    // Method untuk mengambil data warehouse untuk Select2
    public function getWarehouses(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Warehouse::query();

        // Jika ada search term, filter berdasarkan search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('WARE_Auto', 'like', "%{$search}%")
                    ->orWhere('WARE_Name', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->select('WARE_Auto', 'WARE_Name')
            ->orderBy('WARE_Auto')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $warehouses->map(function ($warehouse) {
            return [
                'id' => $warehouse->WARE_Auto,
                'text' => $warehouse->WARE_Auto . ' - ' . $warehouse->WARE_Name
            ];
        });

        return response()->json([
            'items' => $items,
            'total_count' => $warehouses->total()
        ]);
    }

    // Halaman create
    public function create()
    {
        // kalau sudah ada draft, ambil. kalau belum, buat baru
        $draft = ThTrxSalesRtr::firstOrCreate(
            ['trx_posting' => 'D'],
            [
                'trx_number'     => $this->generateNextNumber(),
                'Trx_Date'       => now()->format('Y-m-d'),
                'Trx_SupCode'    => '',
                'Trx_WareCode'   => '',
                'Trx_Note'       => '',
                'Trx_UserID'     => Auth::user()->Mem_Auto,
                'Trx_LastUpdate' => now(),
            ]
        );

        // eager load details kosong atau yang ada
        $draft->load(['details', 'warehouse', 'customer']);

        return view('retur.penjualan.create', [
            'header' => $draft,
            'details' => $draft->details,
        ]);
    }

    // helper untuk generate nomor
    protected function generateNextNumber()
    {
        $lastSeq = ThTrxSalesRtr::selectRaw("MAX(CAST(SUBSTRING(trx_number, 5) AS UNSIGNED)) AS seq")
            ->value('seq') ?? 0;
        return 'RTJ-' . ($lastSeq + 1);
    }

    public function edit($id)
    {
        // Pastikan hanya dokumen F (belum approve) yang bisa di-edit
        $header = ThTrxSalesRtr::where('Trx_Auto', $id)
            ->where('trx_posting', 'F')
            ->with(['details', 'warehouse', 'customer'])
            ->firstOrFail();

        // Eager load
        $header->load('details');

        return view('retur.penjualan.edit', [
            'header' => $header,
            'details' => $header->details,
        ]);
    }

    // Metode baru untuk update header
    public function updateHeader(Request $r, $id)
    {
        $r->validate([
            'Trx_Date'     => 'sometimes|date',
            'Trx_SupCode'  => 'sometimes|string|max:20',
            'Trx_WareCode' => 'sometimes|string|max:20',
            'Trx_Note'     => 'nullable|string',
        ]);

        $hdr = ThTrxSalesRtr::findOrFail($id);

        // Coba untuk mendapatkan User ID, atau gunakan default (misalnya null)
        $userId = null;
        try {
            if (Auth::check()) {
                $userId = Auth::user()->Mem_Auto;
            }
        } catch (\Exception $e) {
            // Tangani pengecualian jika auth() tidak berfungsi
        }

        $hdr->update([
            'Trx_Date'       => $r->input('Trx_Date', $hdr->Trx_Date),
            'Trx_SupCode'    => $r->input('Trx_SupCode', $hdr->Trx_SupCode),
            'Trx_WareCode'   => $r->input('Trx_WareCode', $hdr->Trx_WareCode),
            'Trx_Note'       => $r->input('Trx_Note', $hdr->Trx_Note),
            'Trx_UserID'     => $userId ?? null,
            'Trx_LastUpdate' => now(),
        ]);

        return response()->json([
            'header'     => $hdr,
            'success'    => true
        ]);
    }

    // JSON Detail untuk satu header
    public function detailsJson($id)
    {
        $header = ThTrxSalesRtr::with('details.uom:UOM_Auto,UOM_Code')->findOrFail($id);
        return response()->json(['data' => $header->details]);
    }

    // Store Detail via AJAX
    public function storeDetail(Request $r, $id)
    {
        $r->validate([
            'Trx_ProdCode'   => 'required|string',
            'trx_prodname'   => 'required|string',
            'trx_uom'        => 'required|string|max:10',
            'Trx_QtyTrx'     => 'required|numeric|min:0',
            'Trx_GrossPrice' => 'required|numeric|min:0',
            'Trx_Discount'   => 'required|numeric|min:0',
            'Trx_Taxes'      => 'required|numeric|min:0',
            'Trx_NettPrice'  => 'required|numeric|min:0',
            'Trx_NoteDetail' => 'nullable|string',
        ]);

        $hdr    = ThTrxSalesRtr::findOrFail($id);
        $detail = $hdr->details()->create([
            'trx_number'     => $hdr->trx_number,
            'Trx_ProdCode'   => $r->Trx_ProdCode,
            'trx_prodname'   => $r->trx_prodname,
            'trx_uom'        => $r->trx_uom,
            'Trx_QtyTrx'     => $r->Trx_QtyTrx,
            'Trx_GrossPrice' => $r->Trx_GrossPrice,
            'Trx_Discount'   => $r->Trx_Discount,
            'Trx_Taxes'      => $r->Trx_Taxes,
            'Trx_NettPrice'  => $r->Trx_NettPrice,
            'Trx_Note'       => $r->input('Trx_NoteDetail'),
            'trx_posting'    => 'D',
            'Trx_LastUpdate' => now(),
        ]);

        // Update juga total di header
        $this->updateHeaderTotals($hdr->Trx_Auto);

        return response()->json(['detail' => $detail]);
    }

    // Update Detail via AJAX
    public function updateDetail(Request $r, $id, $detailId)
    {
        $r->validate([
            'Trx_ProdCode'   => 'required|string',
            'trx_prodname'   => 'required|string',
            'trx_uom'        => 'required|string|max:10',
            'Trx_QtyTrx'     => 'required|numeric|min:0',
            'Trx_GrossPrice' => 'required|numeric|min:0',
            'Trx_Discount'   => 'required|numeric|min:0',
            'Trx_Taxes'      => 'required|numeric|min:0',
            'Trx_NettPrice'  => 'required|numeric|min:0',
            'Trx_NoteDetail' => 'nullable|string',
        ]);

        // update detail
        $det = TdTrxSalesRtr::findOrFail($detailId);
        $det->update([
            'Trx_ProdCode'   => $r->Trx_ProdCode,
            'trx_prodname'   => $r->trx_prodname,
            'trx_uom'        => $r->trx_uom,
            'Trx_QtyTrx'     => $r->Trx_QtyTrx,
            'Trx_GrossPrice' => $r->Trx_GrossPrice,
            'Trx_Discount'   => $r->Trx_Discount,
            'Trx_Taxes'      => $r->Trx_Taxes,
            'Trx_NettPrice'  => $r->Trx_NettPrice,
            'Trx_Note'       => $r->input('Trx_NoteDetail'),
            'Trx_LastUpdate' => now(),
        ]);

        // Update juga total di header
        $this->updateHeaderTotals($id);

        return response()->json(['detail' => $det]);
    }

    // Helper method untuk mengupdate total di header
    protected function updateHeaderTotals($headerId)
    {
        $header = ThTrxSalesRtr::findOrFail($headerId);

        // Menyiapkan kueri untuk mendapatkan detail dari header
        $details = $header->details()->get();

        // Inisialisasi variabel untuk totals
        $grossTotal = 0;
        $discountTotal = 0;
        $taxesTotal = 0;
        $nettTotal = 0;

        // Iterasi setiap detail untuk mendapatkan totals
        foreach ($details as $detail) {
            $subtotal = $detail->Trx_GrossPrice * $detail->Trx_QtyTrx;
            $grossTotal += $subtotal;

            // Hitung diskon dan pajak sebagai nilai absolut
            $discountAmount = $subtotal * ($detail->Trx_Discount / 100);
            $discountTotal += $discountAmount;

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $afterDiscount * ($detail->Trx_Taxes / 100);
            $taxesTotal += $taxAmount;

            $nettTotal += $detail->Trx_NettPrice;
        }

        // Update header dengan nilai absolut untuk diskon dan pajak
        $header->update([
            'Trx_GrossPrice' => $grossTotal,
            'Trx_TotDiscount'   => $discountTotal,
            'Trx_Taxes'      => $taxesTotal,
            'Trx_NettPrice'  => $nettTotal,
            'Trx_LastUpdate' => now()
        ]);
    }

    // Destroy Detail via AJAX
    public function destroyDetail($id, $detailId)
    {
        TdTrxSalesRtr::findOrFail($detailId)->delete();

        // Update juga total di header
        $this->updateHeaderTotals($id);

        return response()->json(['success' => true]);
    }

    // Destroy Header & cascade details
    public function destroyHeader($id)
    {
        $header = ThTrxSalesRtr::findOrFail($id);

        // Delete details dulu
        TdTrxSalesRtr::where('trx_number', $header->trx_number)->delete();

        // Kemudian delete header
        $header->delete();

        return response()->json(['success' => true]);
    }

    // Publish Draft
    public function publish(Request $r, $id)
    {
        $hdr = ThTrxSalesRtr::findOrFail($id);

        // Cek jika draft punya details
        $detailCount = TdTrxSalesRtr::where('trx_number', $hdr->trx_number)->count();
        if ($detailCount == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menyimpan draft kosong. Tambahkan minimal satu item.'
            ], 422);
        }

        // Update header
        $hdr->update([
            'trx_posting' => 'F',
            'Trx_UserID'     => Auth::user()->Mem_Auto,
            'Trx_LastUpdate' => now()
        ]);

        // Update semua details agar sama
        TdTrxSalesRtr::where('trx_number', $hdr->trx_number)
            ->update([
                'trx_posting' => 'F',
                'Trx_LastUpdate' => now()
            ]);

        return response()->json(['success' => true]);
    }

    // Publish halaman edit
    public function publishEdit(Request $r, $id)
    {
        $r->validate([
            'Trx_Date'       => 'required|date',
            'Trx_SupCode'    => 'required|string|max:20',
            'Trx_WareCode'   => 'nullable|string|max:20',
            'Trx_Note'       => 'nullable|string',
            'details'        => 'required|array|min:1',
            // validasi tiap field detail
            'details.*.Trx_ProdCode'   => 'required|string',
            'details.*.trx_prodname'   => 'required|string',
            'details.*.trx_uom'        => 'required|string|max:10',
            'details.*.Trx_QtyTrx'     => 'required|numeric|min:1',
            'details.*.Trx_GrossPrice' => 'required|numeric|min:0',
            'details.*.Trx_Discount'   => 'required|numeric|min:0',
            'details.*.Trx_Taxes'      => 'required|numeric|min:0',
            'details.*.Trx_NettPrice'  => 'required|numeric|min:0',
        ]);

        // Ambil header
        $hdr = ThTrxSalesRtr::findOrFail($id);
        // Update header fields + set posting=F
        $hdr->update([
            'Trx_Date'     => $r->Trx_Date,
            'Trx_SupCode'  => $r->Trx_SupCode,
            'Trx_WareCode' => $r->Trx_WareCode,
            'Trx_Note'     => $r->Trx_Note,
            'trx_posting'  => 'F',
            'Trx_UserID'   => Auth::user()->Mem_Auto,
            'Trx_LastUpdate' => now(),
        ]);

        // Hapus semua detail lama, lalu simpan yang baru
        TdTrxSalesRtr::where('trx_number', $hdr->trx_number)->delete();

        $totGross = $totDisc = $totTax = $totNett = 0;
        foreach ($r->details as $dt) {
            $d = $hdr->details()->create(array_merge($dt, [
                'trx_number'     => $hdr->trx_number,
                'trx_posting'    => 'F',
                'Trx_LastUpdate' => now(),
            ]));
            $sub = $d->Trx_GrossPrice * $d->Trx_QtyTrx;
            $discAmt = $sub * ($d->Trx_Discount / 100);
            $taxAmt  = ($sub - $discAmt) * ($d->Trx_Taxes / 100);
            $totGross += $sub;
            $totDisc  += $discAmt;
            $totTax   += $taxAmt;
            $totNett  += $d->Trx_NettPrice;
        }

        // Update totals header
        $hdr->update([
            'Trx_GrossPrice'  => $totGross,
            'Trx_TotDiscount' => $totDisc,
            'Trx_Taxes'       => $totTax,
            'Trx_NettPrice'   => $totNett,
        ]);

        return response()->json(['success' => true]);
    }

    // Approve All 
    public function approveAll(Request $request)
    {
        // Ambil semua header yang akan diapprove
        $headers = ThTrxSalesRtr::where('trx_posting', 'F')->get();

        foreach ($headers as $hdr) {
            // Update header menjadi status T
            $hdr->update([
                'trx_posting' => 'T',
                'Trx_UserID' => Auth::user()->Mem_Auto,
                'Trx_LastUpdate' => now()
            ]);

            // Update semua detail terkait dan update stok
            $details = TdTrxSalesRtr::where('trx_number', $hdr->trx_number)->get();

            foreach ($details as $detail) {
                // Update posting status
                $detail->update([
                    'trx_posting' => 'T',
                    'Trx_LastUpdate' => now()
                ]);

                // Update stok produk (retur penjualan = stok bertambah)
                $product = Dtproduk::where('kode_produk', $detail->Trx_ProdCode)->first();
                if ($product) {
                    // Pastikan stok tidak negatif
                    if ($product->qty >= $detail->Trx_QtyTrx) {
                        $product->increment('qty', $detail->Trx_QtyTrx);
                    } else {
                        // Log warning jika stok tidak mencukupi
                        Log::warning("Stok tidak mencukupi untuk produk {$detail->Trx_ProdCode}. Stok tersedia: {$product->qty}, Qty retur: {$detail->Trx_QtyTrx}");
                        $product->update(['qty' => 0]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'count' => $headers->count()
        ]);
    }

    // Approve Single  
    public function approve($id)
    {
        $hdr = ThTrxSalesRtr::findOrFail($id);

        // Pastikan hanya dokumen yang sudah F yang bisa disetujui
        if ($hdr->trx_posting !== 'F') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dokumen yang sudah disimpan yang dapat disetujui.'
            ], 422);
        }

        // Update header menjadi status T
        $hdr->update([
            'trx_posting' => 'T',
            'Trx_UserID' => Auth::user()->Mem_Auto,
            'Trx_LastUpdate' => now()
        ]);

        // Update semua detail terkait dan update stok
        $details = TdTrxSalesRtr::where('trx_number', $hdr->trx_number)->get();

        foreach ($details as $detail) {
            // Update posting status
            $detail->update([
                'trx_posting' => 'T',
                'Trx_LastUpdate' => now()
            ]);

            // Update stok produk (retur penjualan = stok bertambah)
            $product = Dtproduk::where('kode_produk', $detail->Trx_ProdCode)->first();
            if ($product) {
                // Pastikan stok tidak negatif
                if ($product->qty >= $detail->Trx_QtyTrx) {
                    $product->increment('qty', $detail->Trx_QtyTrx);
                } else {
                    // Log warning jika stok tidak mencukupi
                    Log::warning("Stok tidak mencukupi untuk produk {$detail->Trx_ProdCode}. Stok tersedia: {$product->qty}, Qty retur: {$detail->Trx_QtyTrx}");
                    $product->update(['qty' => 0]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    // Print All
    public function printAll(Request $request)
    {
        $cols = [
            'Trx_SupCode',
            'trx_number',
            'Trx_Date',
            'Trx_GrossPrice',
            'Trx_TotDiscount',
            'Trx_Taxes',
            'Trx_NettPrice',
            'Trx_UserID'
        ];

        $query = ThTrxSalesRtr::select(array_merge(['Trx_Auto', 'trx_posting'], $cols))
            ->with([
                'user:Mem_Auto,Mem_ID,Mem_UserName',
                'customer:id,kode,anggota'
            ])
            ->whereIn('trx_posting', ['F', 'T']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search, $cols) {
                foreach ($cols as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        // Filter
        if ($request->filled('filter_date_from')) {
            $query->whereDate('Trx_Date', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('Trx_Date', '<=', $request->filter_date_to);
        }

        if ($request->filled('filter_sup_code')) {
            $query->where('Trx_SupCode', 'like', '%' . $request->filter_sup_code . '%');
        }

        $rows = $query->get();

        // Filter catatan yang dihapus dan hitung total
        $filteredRows = $rows->where('trx_posting', '!=', 'D');

        // Hitung total untuk data yang difilter
        $totalGrossPrice = $filteredRows->sum('Trx_GrossPrice');
        $totalDiscount = $filteredRows->sum('Trx_TotDiscount');
        $totalTaxes = $filteredRows->sum('Trx_Taxes');
        $totalNettPrice = $filteredRows->sum('Trx_NettPrice');

        $tanggalCetak = now()->setTimezone('Asia/Jakarta')->format('d F Y H:i:s');
        $currentUser = auth()->user();

        // Cek apakah ada filter yang diterapkan
        $hasFilters = $request->filled('filter_date_from') ||
            $request->filled('filter_date_to') ||
            $request->filled('filter_sup_code') ||
            $request->filled('search');

        $pdf = Pdf::loadView('retur.penjualan.print', compact(
            'rows',
            'totalGrossPrice',
            'totalDiscount',
            'totalTaxes',
            'totalNettPrice',
            'tanggalCetak',
            'currentUser'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('retur-penjualan.pdf');
    }

    // Print Single
    public function print($id)
    {
        $row = ThTrxSalesRtr::with([
            'details.uom:UOM_Auto,UOM_Code',
            'user:Mem_Auto,Mem_ID,Mem_UserName',
            'customer:id,kode,anggota'
        ])->findOrFail($id);
        $details = $row->details;

        $currentUser = auth()->user();
        $tanggalCetak = now()->setTimezone('Asia/Jakarta')->format('d F Y H:i:s');

        $pdf = Pdf::loadView('retur.penjualan.print', compact('row', 'details', 'tanggalCetak', 'currentUser'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("retur-penjualan-{$row->trx_number}.pdf");
    }

    // autocomplete produk
    public function getProductData(Request $request)
    {
        $kode = $request->get('kode_produk');

        if (!$kode) {
            return response()->json(['error' => 'Kode produk harus diisi'], 400);
        }

        $product = Dtproduk::with('supplier')
            ->where('kode_produk', $kode)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kode_produk' => $product->kode_produk,
                'nama_produk' => $product->nama_produk,
                'harga_jual' => $product->harga_jual,
                'harga_beli' => $product->harga_beli,
                'qty' => $product->qty,
                'uom_code' => 'PCS', // Default UOM
                'supplier_name' => $product->supplier ? $product->supplier->nama_supplier : ''
            ]
        ]);
    }

    // Method untuk mengambil data UOM
    public function getUoms(Request $request)
    {
        $selectedValue = $request->get('selected'); // Untuk mendapatkan nilai yang sudah dipilih

        $uoms = SatuanProduk::select('UOM_Code', 'UOM_Auto')
            ->orderBy('UOM_Code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $uoms,
            'selected' => $selectedValue // Kirim kembali nilai yang dipilih
        ]);
    }
}
