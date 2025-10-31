<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Models\Akuntansi\AccDtjurnal;
use App\Models\Akuntansi\AccHdjurnal;
use App\Models\Akuntansi\AccKira;
use App\Models\MutasiGudang\Warehouse; // Pastikan ini ada jika Anda menggunakan relasi gudang
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Support\Facades\Validator; // Untuk validasi
use Carbon\Carbon;

class JurnalUmumController extends Controller
{
    // Fungsi helper untuk generate nomor jurnal
    private function generateNoJurnal(): string
    {
        $currentYearMonth = date('dm'); // Format TahunBulan (e.g., 2307)
        $prefix = date('d') . date('m') . '-'; // e.g., "2307-"
        $suffix = '-JU';

        // Cari nomor urut terakhir di bulan ini
        $lastJurnal = AccHdjurnal::where('no_jurnal', 'like', $prefix . '%')
                                  ->orderBy('no_jurnal', 'desc')
                                  ->first();

        $newNumber = 10001; // Nomor awal jika belum ada jurnal di bulan ini
        if ($lastJurnal) {
            // Ekstrak nomor urut dari no_jurnal terakhir
            $parts = explode('-', $lastJurnal->no_jurnal);
            if (count($parts) === 3 && is_numeric($parts[1])) {
                $newNumber = (int)$parts[1] + 1;
            }
        }

        // Format nomor urut menjadi 5 digit dengan padding nol
        $sequence = str_pad($newNumber, 5, '0', STR_PAD_LEFT); // 10001 -> "10001"

        return $prefix . $sequence . $suffix; // e.g., "2307-10001-JU"
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil daftar jurnal header dengan relasi user
        $jurnals = AccHdjurnal::with('user')
                ->orderBy('tanggal_buat', 'desc')
                ->orderBy('no_jurnal', 'desc')
                ->paginate(15); // Sesuaikan paginasi

        $warehouses = Warehouse::orderBy('WARE_Name')->get(['WARE_Name']); // Cukup ambil WARE_Name

        // Ambil daftar perkiraan untuk dropdown di modal
        $perkiraan = AccKira::orderBy('cls_kiraid')->get(['id', 'cls_kiraid', 'cls_ina']);

        return view('akunting.jurnalumum.index', compact('jurnals', 'perkiraan', 'warehouses'));

    }

    /**
     * Show the form for creating a new resource.
     * (Tidak digunakan langsung karena pakai modal, tapi bisa untuk AJAX get data awal)
     */
    public function create()
    {
        // Bisa digunakan untuk AJAX request mendapatkan nomor jurnal baru
         return response()->json(['no_jurnal' => $this->generateNoJurnal()]);
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
            'tanggal_buat' => 'required|date',
            'lokasi_nama' => 'nullable|string|max:255',
            'referensi' => 'nullable|string|max:255',
            'catatan_header' => 'nullable|string',
            'details' => 'required|array|min:2', // Minimal 2 baris detail (debet & kredit)
            'details.*.acc_kira_id' => 'required|exists:acc_kira,id',
            'details.*.debet' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.catatan_detail' => 'nullable|string',
        ], [
            'details.required' => 'Minimal harus ada detail jurnal.',
            'details.min' => 'Minimal harus ada 2 baris detail (debet & kredit).',
            'details.*.acc_kira_id.required' => 'Kode Perkiraan pada baris :position harus diisi.',
            'details.*.acc_kira_id.exists' => 'Kode Perkiraan pada baris :position tidak valid.',
            'details.*.debet.required' => 'Debet pada baris :position harus diisi.',
            'details.*.debet.numeric' => 'Debet pada baris :position harus angka.',
            'details.*.kredit.required' => 'Kredit pada baris :position harus diisi.',
            'details.*.kredit.numeric' => 'Kredit pada baris :position harus angka.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Hitung total debet dan kredit dari detail
        $totalDebet = 0;
        $totalKredit = 0;
        foreach ($request->details as $detail) {
            $totalDebet += (float)$detail['debet'];
            $totalKredit += (float)$detail['kredit'];

            // Validasi tambahan: Debet atau Kredit salah satu harus 0 jika lainnya > 0
            if((float)$detail['debet'] > 0 && (float)$detail['kredit'] > 0) {
                 return response()->json(['errors' => ['balance' => ['Pada baris dengan akun ID ' . $detail['acc_kira_id'] . ', Debet dan Kredit tidak bisa diisi bersamaan.']]], 422);
            }
             if((float)$detail['debet'] == 0 && (float)$detail['kredit'] == 0) {
                 return response()->json(['errors' => ['balance' => ['Pada baris dengan akun ID ' . $detail['acc_kira_id'] . ', Debet atau Kredit harus diisi salah satu.']]], 422);
            }
        }

        // Validasi Balance
        // Gunakan perbandingan dengan toleransi kecil untuk floating point
        if (abs($totalDebet - $totalKredit) > 0.001) {
             return response()->json(['errors' => ['balance' => ['Total Debet ('.number_format($totalDebet, 2).') dan Kredit ('.number_format($totalKredit, 2).') harus seimbang.']]], 422);
        }

        // --- Proses Penyimpanan ---
        DB::beginTransaction();
        try {
            $noJurnal = $this->generateNoJurnal();
            $now = Carbon::now();

            // 1. Simpan Header Jurnal
            $header = AccHdjurnal::create([
                'no_jurnal' => $noJurnal,
                'tanggal_buat' => $request->tanggal_buat,
                'tanggal_edit' => $now,
                'lokasi_nama' => $request->lokasi_nama,
                'referensi' => $request->referensi,
                'catatan' => $request->catatan_header,
                'user_id' => Auth::id(),
                'nominal' => $totalDebet,
                'tipe_jurnal' => 'JU', // <-- SET TIPE JURNAL
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            // 2. Simpan Detail Jurnal
            foreach ($request->details as $detailData) {
                AccDtjurnal::create([
                    'acc_hd_jurnal_id' => $header->id,
                    'acc_kira_id' => $detailData['acc_kira_id'],
                    'debet' => (float)$detailData['debet'],
                    'kredit' => (float)$detailData['kredit'],
                    'catatan' => $detailData['catatan_detail'],
                ]);
            }

            DB::commit(); // Simpan permanen jika semua berhasil

            return response()->json(['success' => 'Jurnal berhasil disimpan dengan nomor: ' . $noJurnal], 201); // Created

        } catch (\Exception $e) {
            DB::rollBack();
            // Tampilkan pesan error asli dari database ke frontend
            return response()->json(['errors' => ['server' => [$e->getMessage()]]], 500);
        }
    }


    /**
     * Display the specified resource.
     * (Tidak digunakan secara langsung di route list, mungkin untuk API detail)
     */
    public function show(string $id)
    {
        $jurnal = AccHdjurnal::with('details.perkiraan', 'user')->find($id);
         if (!$jurnal) {
            return response()->json(['message' => 'Jurnal tidak ditemukan'], 404);
        }
        return response()->json($jurnal);
    }

    /**
     * Show the form for editing the specified resource.
     * (Digunakan untuk AJAX get data ke modal edit)
     */
    public function edit(string $id)
    {
        $jurnal = AccHdjurnal::with('details.perkiraan')->find($id); // Eager load details dan perkiraan
        if (!$jurnal) {
            return response()->json(['error' => 'Data jurnal tidak ditemukan.'], 404);
        }

        // Tambahkan data perkiraan untuk dropdown di modal edit
        $perkiraan = AccKira::orderBy('cls_kiraid')->get(['id', 'cls_kiraid', 'cls_ina']);

        return response()->json([
            'jurnal' => $jurnal,
            'perkiraan' => $perkiraan // Kirim juga list perkiraan jika diperlukan di frontend
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi Input (Mirip dengan store, tapi ID jurnal sudah ada)
         $validator = Validator::make($request->all(), [
            'tanggal_buat' => 'required|date',
            'lokasi_nama' => 'nullable|string|max:255',
            'referensi' => 'nullable|string|max:255',
            'catatan_header' => 'nullable|string',
            'details' => 'required|array|min:2',
            'details.*.acc_kira_id' => 'required|exists:acc_kira,id',
            'details.*.debet' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.catatan_detail' => 'nullable|string',
        ], [
            // Pesan error sama seperti store
             'details.required' => 'Minimal harus ada detail jurnal.',
            'details.min' => 'Minimal harus ada 2 baris detail (debet & kredit).',
            'details.*.acc_kira_id.required' => 'Kode Perkiraan pada baris :position harus diisi.',
            'details.*.acc_kira_id.exists' => 'Kode Perkiraan pada baris :position tidak valid.',
            'details.*.debet.required' => 'Debet pada baris :position harus diisi.',
            'details.*.debet.numeric' => 'Debet pada baris :position harus angka.',
            'details.*.kredit.required' => 'Kredit pada baris :position harus diisi.',
            'details.*.kredit.numeric' => 'Kredit pada baris :position harus angka.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

         // Hitung total debet dan kredit & validasi balance (sama seperti store)
        $totalDebet = 0;
        $totalKredit = 0;
         foreach ($request->details as $index => $detail) { // Tambah index untuk pesan error
             $totalDebet += (float)$detail['debet'];
             $totalKredit += (float)$detail['kredit'];

             if((float)$detail['debet'] > 0 && (float)$detail['kredit'] > 0) {
                 // Beri nomor baris yang lebih user-friendly (index + 1)
                 return response()->json(['errors' => ['balance' => ['Pada baris ke-' . ($index + 1) . ', Debet dan Kredit tidak bisa diisi bersamaan.']]], 422);
             }
              if((float)$detail['debet'] == 0 && (float)$detail['kredit'] == 0) {
                 return response()->json(['errors' => ['balance' => ['Pada baris ke-' . ($index + 1) . ', Debet atau Kredit harus diisi salah satu.']]], 422);
             }
         }

         if (abs($totalDebet - $totalKredit) > 0.001) {
             return response()->json(['errors' => ['balance' => ['Total Debet ('.number_format($totalDebet, 2).') dan Kredit ('.number_format($totalKredit, 2).') harus seimbang.']]], 422);
         }

        // --- Proses Update ---
        DB::beginTransaction();
        try {
            $header = AccHdjurnal::find($id);
            if (!$header) {
                DB::rollBack();
                return response()->json(['errors' => ['server' => ['Jurnal yang akan diedit tidak ditemukan.']]], 404);
            }

            $now = Carbon::now();

            // 1. Update Header Jurnal
            $header->update([
                'tanggal_buat' => $request->tanggal_buat,
                'tanggal_edit' => $now, // Update tanggal edit
                'lokasi_nama' => $request->lokasi_nama,
                'referensi' => $request->referensi,
                'catatan' => $request->catatan_header,
                'user_id' => Auth::id(), // Update user yang terakhir edit
                'nominal' => $totalDebet, // Update nominal
            ]);

            // 2. Hapus Detail Lama & Tambah Detail Baru (Strategi paling mudah)
            $header->details()->delete(); // Hapus semua detail terkait

            foreach ($request->details as $detailData) {
                AccDtjurnal::create([
                    'acc_hd_jurnal_id' => $header->id,
                    'acc_kira_id' => $detailData['acc_kira_id'],
                    'debet' => (float)$detailData['debet'],
                    'kredit' => (float)$detailData['kredit'],
                    'catatan' => $detailData['catatan_detail'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Jurnal ' . $header->no_jurnal . ' berhasil diperbarui.'], 200); // OK

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating journal ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['errors' => ['server' => ['Terjadi kesalahan saat memperbarui jurnal. Silakan coba lagi.']]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $header = AccHdjurnal::find($id);
            if (!$header) {
                DB::rollBack();
                // return redirect()->route('jurnalumum.index')->with('error', 'Jurnal tidak ditemukan.');
                 return response()->json(['error' => 'Jurnal tidak ditemukan.'], 404); // Untuk AJAX
            }

            $noJurnal = $header->no_jurnal; // Simpan nomor untuk pesan sukses

            // Hapus detail dulu (atau biarkan cascade delete bekerja jika di-setting di migration)
            // $header->details()->delete(); // Tidak perlu jika cascade delete aktif

            $header->delete(); // Hapus header, detail akan terhapus otomatis jika cascade

            DB::commit();
            // return redirect()->route('jurnalumum.index')->with('success', 'Jurnal ' . $noJurnal . ' berhasil dihapus.');
             return response()->json(['success' => 'Jurnal ' . $noJurnal . ' berhasil dihapus.'], 200); // Untuk AJAX

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting journal ID ' . $id . ': ' . $e->getMessage());
            // return redirect()->route('jurnalumum.index')->with('error', 'Gagal menghapus jurnal.');
             return response()->json(['error' => 'Gagal menghapus jurnal.'], 500); // Untuk AJAX
        }
    }

     /**
     * AJAX endpoint untuk mendapatkan nama perkiraan berdasarkan ID.
     */
    public function getNamaPerkiraan($id)
    {
        $perkiraan = AccKira::find($id);
        if ($perkiraan) {
            return response()->json(['nama_perkiraan' => $perkiraan->cls_ina]);
        } else {
            return response()->json(['nama_perkiraan' => ''], 404); // Not found
        }
    }

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
}
