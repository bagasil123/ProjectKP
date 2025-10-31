<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akuntansi\AccDtJurnal;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuBesarController extends Controller
{
    public function index(Request $request)
    {
        // Mulai dengan Query Builder
        $query = AccDtJurnal::with(['header', 'perkiraan']);

        // Filter by Tanggal (ada di tabel header)
        $query->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
            $q->whereHas('header', function ($subQuery) use ($request) {
                $subQuery->whereBetween('tanggal_buat', [$request->start_date, $request->end_date]);
            });
        });

        // Filter by No. Referensi / No. Jurnal (ada di tabel header)
        $query->when($request->filled('referensi'), function ($q) use ($request) {
            $q->whereHas('header', function ($subQuery) use ($request) {
                // Ganti 'no_jurnal' dengan nama kolom yang benar jika berbeda
                $subQuery->where('no_jurnal', 'like', '%' . $request->referensi . '%');
            });
        });

        // Filter by No. Rekening (ada di tabel perkiraan)
        $query->when($request->filled('no_rekening'), function ($q) use ($request) {
        $q->whereHas('perkiraan', function ($subQuery) use ($request) {
            // GANTI 'no_rekening' menjadi 'cls_kiraid'
            $subQuery->where('cls_kiraid', 'like', '%' . $request->no_rekening . '%');
            });
        });

        // Filter by Nama Perkiraan (ada di tabel perkiraan)
        $query->when($request->filled('nama_perkiraan'), function ($q) use ($request) {
        $q->whereHas('perkiraan', function ($subQuery) use ($request) {
            // GANTI 'nama_perkiraan' menjadi 'cls_ina'
            $subQuery->where('cls_ina', 'like', '%' . $request->nama_perkiraan . '%');
            });
        });


        // Clone query SEBELUM paginasi untuk menghitung total
        $filteredQuery = clone $query;
        $totalDebetKeseluruhan = $filteredQuery->sum('debet');
        $totalKreditKeseluruhan = $filteredQuery->sum('kredit');
        $selisihKeseluruhan = $totalDebetKeseluruhan - $totalKreditKeseluruhan;

        // --- AMBIL DATA UNTUK DITAMPILKAN ---
        // Lanjutkan query utama dengan ordering dan paginasi
        $jurnalEntries = $query->join(
                'acc_hd_jurnal',
                'acc_dt_jurnal.acc_hd_jurnal_id',
                '=',
                'acc_hd_jurnal.id'
            )
            ->select('acc_dt_jurnal.*') // Pastikan select setelah join
            ->orderBy('acc_hd_jurnal.tanggal_buat', 'asc')
            ->orderBy('acc_hd_jurnal.no_jurnal', 'asc')
            ->orderBy('acc_dt_jurnal.id', 'asc')
            ->paginate(25); // Paginasi dilakukan di akhir

        return view('akunting.bukubesar.index', compact(
            'jurnalEntries',
            'totalDebetKeseluruhan',
            'totalKreditKeseluruhan',
            'selisihKeseluruhan'
        ));
    }

    // Tambahkan Request $request agar bisa menerima filter dari URL
    public function generatePDF(Request $request)
    {
        // Logika query ini SAMA PERSIS dengan di method index,
        // hanya diakhiri dengan ->get() bukan ->paginate()

        $query = AccDtJurnal::with(['header', 'perkiraan']);

        // APLIKASIKAN FILTER (Sama seperti di atas)
        $query->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
            $q->whereHas('header', function ($subQuery) use ($request) {
                $subQuery->whereBetween('tanggal_buat', [$request->start_date, $request->end_date]);
            });
        });
        $query->when($request->filled('referensi'), function ($q) use ($request) {
            $q->whereHas('header', function ($subQuery) use ($request) {
                $subQuery->where('no_jurnal', 'like', '%' . $request->referensi . '%');
            });
        });
        $query->when($request->filled('no_rekening'), function ($q) use ($request) {
        $q->whereHas('perkiraan', function ($subQuery) use ($request) {
            // GANTI 'no_rekening' menjadi 'cls_kiraid'
            $subQuery->where('cls_kiraid', 'like', '%' . $request->no_rekening . '%');
            });
        });
        $query->when($request->filled('nama_perkiraan'), function ($q) use ($request) {
        $q->whereHas('perkiraan', function ($subQuery) use ($request) {
            // GANTI 'nama_perkiraan' menjadi 'cls_ina'
            $subQuery->where('cls_ina', 'like', '%' . $request->nama_perkiraan . '%');
            });
        });

        // Ambil SEMUA data yang sudah terfilter
        $jurnalEntries = $query->join(
                'acc_hd_jurnal',
                'acc_dt_jurnal.acc_hd_jurnal_id',
                '=',
                'acc_hd_jurnal.id'
            )
            ->select('acc_dt_jurnal.*')
            ->orderBy('acc_hd_jurnal.tanggal_buat', 'asc')
            ->orderBy('acc_hd_jurnal.no_jurnal', 'asc')
            ->orderBy('acc_dt_jurnal.id', 'asc')
            ->get(); // Gunakan get() untuk mengambil semua record yang terfilter

        // Hitung total dari data yang sudah diambil
        $totalDebetKeseluruhan = $jurnalEntries->sum('debet');
        $totalKreditKeseluruhan = $jurnalEntries->sum('kredit');
        $selisihKeseluruhan = $totalDebetKeseluruhan - $totalKreditKeseluruhan;

        $data = [
            'jurnalEntries' => $jurnalEntries,
            'totalDebetKeseluruhan' => $totalDebetKeseluruhan,
            'totalKreditKeseluruhan' => $totalKreditKeseluruhan,
            'selisihKeseluruhan' => $selisihKeseluruhan,
            'tanggalCetak' => now()->translatedFormat('d F Y H:i'),
        ];

        $pdf = Pdf::loadView('akunting.bukubesar.pdf_view', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('buku-besar-umum-'.date('YmdHis').'.pdf');
    }
}
