<?php

namespace App\Http\Controllers;

use App\Models\SPModels\PenjualanDetail; // Pastikan namespace ini benar
use App\Models\Inventory\Dtproduk;      // <--- SESUAIKAN DENGAN MODEL ANDA
use Carbon\Carbon;
use App\Models\Presensi\Employee; // Pastikan model Employee sudah ada
use App\Models\Presensi\RealAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

    // === 1. DATA UNTUK GRAFIK PENJUALAN PER BULAN ===
    $currentYear = Carbon::now()->year;
    // Query ini bisa Anda sesuaikan jika nama model/tabel penjualan berbeda
    $salesByMonth = PenjualanDetail::select(
    DB::raw('MONTH(created_at) as month'),
    DB::raw('SUM(nominal) as total_penjualan')
    )
    ->whereYear('created_at', $currentYear)
    ->groupBy('month')
    ->orderBy('month', 'asc')
    ->get();
    $penjualanBulananData = array_fill(0, 12, 0);
    foreach ($salesByMonth as $sale) {
    $penjualanBulananData[$sale->month - 1] = (int)$sale->total_penjualan;
    }

    // =========================================================================
    // === KODE YANG DIPERBARUI: TOP PRODUK BERDASARKAN STOK INVENTORY ===
    // =========================================================================

    // === DATA TOP PRODUK (BERDASARKAN STOK QTY) ===
    // Mengambil langsung dari model Dtproduk, diurutkan berdasarkan kolom 'qty'
    $topProductsQty = Dtproduk::select('nama_produk', 'qty as total_qty') // Menggunakan alias 'total_qty' agar sesuai dengan view
            ->orderBy('qty', 'desc')
            ->limit(5)
            ->get();

    // === DATA TOP PRODUK (BERDASARKAN NILAI INVENTORY) ===
    // Mengambil dari model Dtproduk, diurutkan berdasarkan hasil kalkulasi (qty * harga_beli)
    $topProductsNominal = Dtproduk::select(
                'nama_produk',
                // Kalikan qty dengan harga_beli untuk mendapatkan total nilai
                DB::raw('(qty * harga_beli) as total_nominal')
            )
            ->orderBy('total_nominal', 'desc') // Urutkan berdasarkan hasil kalkulasi
            ->limit(5)
            ->get();


        // === 2. DATA ANALISIS KARYAWAN (BARU) ===

    // --- Query untuk Komposisi Gender ---
    // Asumsi: kolom emp_Sex berisi 'L' untuk Laki-laki dan 'P' untuk Perempuan
    $genderCounts = Employee::select('emp_Sex', DB::raw('count(*) as total'))
                            ->where('emp_ActiveYN', 'Y') // Hanya hitung karyawan aktif
                            ->groupBy('emp_Sex')
                            ->get();

    // Proses data untuk Chart.js
    $genderLabels = [];
    $genderValues = [];
    foreach ($genderCounts as $count) {
        $genderLabels[] = ($count->emp_Sex == 'L') ? 'Laki-laki' : 'Perempuan';
        $genderValues[] = $count->total;
    }

    // --- Query untuk Karyawan per Departemen ---
    $departmentCounts = Employee::select(
        // Ambil kolom Div_Name dari tabel ts_div dan beri alias 'department_name'
        'divisions.Div_Name as department_name',
        DB::raw('count(m_employee.emp_Auto) as total')
    )
    // Gabungkan (JOIN) dengan tabel ts_div
    // 'ts_div as divisions' -> menggunakan 'divisions' sebagai nama alias sementara untuk tabel ts_div
    ->join('ts_div as divisions', 'm_employee.emp_DivCode', '=', 'divisions.div_Code') // Asumsi foreign key tetap 'div_Code'
    ->where('m_employee.emp_ActiveYN', 'Y')
    ->whereNotNull('m_employee.emp_DivCode')
    ->groupBy('divisions.Div_Name') // Kelompokkan berdasarkan nama departemen
    ->orderBy('total', 'desc')
    ->limit(5)
    ->get();

// Proses data untuk Chart.js (ini sudah benar, mengambil alias 'department_name')
$departmentLabels = $departmentCounts->pluck('department_name')->toArray();
$departmentValues = $departmentCounts->pluck('total')->toArray();

// =========================================================================
// === BARU: DATA UNTUK GRAFIK PRESENSI HARI INI ===
// =========================================================================

        // 1. Daftar status yang ingin ditampilkan
        $statusList = ['HADIR', 'SAKIT', 'IZIN', 'ALFA'];

        // 2. Buat array untuk menampung hitungan
        $attendanceCounts = array_fill_keys($statusList, 0);

        // 3. Ambil data presensi HANYA untuk hari ini DARI TABEL YANG BENAR
        $todayAttendance = Realabsensi::select('TS_STATUS', DB::raw('count(*) as total'))
            ->whereDate('TS_TANGGAL', Carbon::today()) // <-- Menggunakan kolom TS_TANGGAL
            ->groupBy('TS_STATUS')                   // <-- Menggunakan kolom TS_STATUS
            ->get();

        // 4. Isi array hitungan dengan data dari database
        foreach ($todayAttendance as $item) {
            // Gunakan strtoupper untuk memastikan konsistensi (misal: 'hadir' menjadi 'HADIR')
            $status = strtoupper($item->TS_STATUS);
            if (array_key_exists($status, $attendanceCounts)) {
                $attendanceCounts[$status] = $item->total;
            }
        }

        // 5. Siapkan label dan data untuk Chart.js
        $attendanceLabels = array_keys($attendanceCounts);
        $attendanceValues = array_values($attendanceCounts);

    // === 3. GABUNGKAN SEMUA DATA DALAM SATU RETURN VIEW ===
    return view('home', [
        // Data Analisis Penjualan
        'penjualanBulananData' => $penjualanBulananData,
        'topProductsQty' => $topProductsQty,
        'topProductsNominal' => $topProductsNominal,

        // Data Analisis Karyawan
        'genderLabels' => $genderLabels,
        'genderValues' => $genderValues,
        'departmentLabels' => $departmentLabels,
        'departmentValues' => $departmentValues,

        // Data dummy untuk Presensi (sesuai permintaan "nanti dulu")
        'attendanceLabels' => $attendanceLabels,
        'attendanceValues' => $attendanceValues,
    ]);
}
    }

