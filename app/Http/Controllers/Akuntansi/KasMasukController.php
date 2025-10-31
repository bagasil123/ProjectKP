<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Models\Akuntansi\AccDtjurnal;
use App\Models\Akuntansi\AccHdjurnal;
use App\Models\Akuntansi\AccKira;
use App\Models\MutasiGudang\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class KasMasukController extends Controller
{
    private function generateNoBuktiKasMasuk(): string
    {
        $currentYearMonth = date('dm');
        $prefix = date('d') . date('m') . '-'; // e.g., "2307-"
        $suffix = '-KM'; // KM untuk Kas Masuk

        $lastBukti = AccHdjurnal::where('no_jurnal', 'like', $prefix . '%') // Masih pakai kolom no_jurnal
                                  ->where('tipe_jurnal', 'KM') // Tambahkan filter tipe jika Anda menambah kolom tipe_jurnal
                                  ->orderBy('no_jurnal', 'desc')
                                  ->first();

        $newNumber = 10001;
        if ($lastBukti) {
            $parts = explode('-', $lastBukti->no_jurnal);
            if (count($parts) === 3 && is_numeric($parts[1])) {
                $newNumber = (int)$parts[1] + 1;
            }
        }
        $sequence = str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        return $prefix . $sequence . $suffix;
    }

    public function index()
    {
        // Filter untuk menampilkan hanya Kas Masuk jika Anda sudah implementasi kolom 'tipe_jurnal'
        // $kasMasukHeaders = AccHdjurnal::with('user')->where('tipe_jurnal', 'KM')
        //                             ->orderBy('tanggal_buat', 'desc')->orderBy('no_jurnal', 'desc')->paginate(15);

        // Untuk sekarang, kita tampilkan semua jurnal umum dulu, nanti bisa difilter
        // ATAU jika kas masuk adalah *jenis* dari jurnal umum, maka field `tipe_jurnal` di AccHdjurnal PENTING
        // Untuk contoh ini, kita anggap ia disimpan sebagai jurnal umum biasa, tapi dengan logika input berbeda.
        // Jika ingin dipisahkan, Anda perlu menambah kolom `tipe_jurnal` di `acc_hdjurnal`
        // dan filter `->where('tipe_jurnal', 'KM')` saat query.
        // Untuk kesederhanaan awal, kita ambil semua jurnal lalu beri nama "Kas Masuk" di view.
        // Idealnya: $jurnals = AccHdjurnal::where('tipe_jurnal', 'KM')->...

        $jurnals = AccHdjurnal::with('user')
                        ->where('tipe_jurnal', 'KM') // <-- FILTER DI SINI
                        ->orderBy('tanggal_buat', 'desc')
                        ->orderBy('no_jurnal', 'desc')
                        ->paginate(15);

        $warehouses = Warehouse::orderBy('WARE_Name')->get(['WARE_Name']); // Cukup ambil WARE_Name
        // Perkiraan untuk Rekening Tujuan (Kas/Bank) dan Diterima Dari
        $perkiraan = AccKira::orderBy('cls_kiraid')->get(['id', 'cls_kiraid', 'cls_ina']);

        return view('akunting.kasmasuk.index', compact('jurnals', 'perkiraan', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_buat' => 'required|date',
            'rekening_tujuan_id' => 'required|exists:acc_kira,id',
            'lokasi_nama' => 'nullable|string|max:255', // <-- VALIDASI LOKASI
            'referensi' => 'nullable|string|max:255',
            'catatan_header' => 'nullable|string',
            'details' => 'required|array|min:1', // Minimal 1 baris penerimaan (kredit)
            'details.*.acc_kira_id' => 'required|exists:acc_kira,id',
            'details.*.kredit' => 'required|numeric|min:0.01', // Kredit harus diisi dan > 0
            'details.*.catatan_detail' => 'nullable|string',
        ], [
            'rekening_tujuan_id.required' => 'Rekening Tujuan harus dipilih.',
            'details.required' => 'Minimal harus ada 1 baris detail penerimaan.',
            'details.min' => 'Minimal harus ada 1 baris detail penerimaan.',
            'details.*.acc_kira_id.required' => 'Kode Perkiraan pada baris :position harus diisi.',
            'details.*.kredit.required' => 'Kredit pada baris :position harus diisi.',
            'details.*.kredit.min' => 'Kredit pada baris :position harus lebih besar dari 0.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalKredit = 0;
        foreach ($request->details as $detail) {
            $totalKredit += (float)$detail['kredit'];
        }

        if ($totalKredit <= 0) {
            return response()->json(['errors' => ['balance' => ['Total kredit harus lebih besar dari 0.']]], 422);
        }

        DB::beginTransaction();
        try {
            $noBukti = $this->generateNoBuktiKasMasuk();
            $now = Carbon::now();

            $header = AccHdjurnal::create([
            'no_jurnal' => $noBukti,
            'tanggal_buat' => Carbon::parse($request->tanggal_buat),
            'tanggal_edit' => $now,
            'lokasi_nama' => $request->lokasi_nama, // Pastikan ini ada jika masih diperlukan
            'referensi' => $request->referensi,
            'catatan' => $request->catatan_header,
            'user_id' => Auth::id(),
            'nominal' => $totalKredit,
            'tipe_jurnal' => 'KM', // <-- SET TIPE JURNAL
            'created_at' => $now,
            'updated_at' => $now,
        ]);

            // 1. Simpan Detail DEBIT (Rekening Tujuan)
            AccDtjurnal::create([
                'acc_hd_jurnal_id' => $header->id,
                'acc_kira_id' => $request->rekening_tujuan_id,
                'debet' => $totalKredit,
                'kredit' => 0,
                'catatan' => 'Kas Masuk ke ' . AccKira::find($request->rekening_tujuan_id)->cls_ina, // Catatan otomatis
            ]);

            // 2. Simpan Detail KREDIT (Diterima Dari)
            foreach ($request->details as $detailData) {
                AccDtjurnal::create([
                    'acc_hd_jurnal_id' => $header->id,
                    'acc_kira_id' => $detailData['acc_kira_id'],
                    'debet' => 0,
                    'kredit' => (float)$detailData['kredit'],
                    'catatan' => $detailData['catatan_detail'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Kas Masuk berhasil disimpan dengan nomor: ' . $noBukti], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Kas Masuk: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['errors' => ['server' => ['Terjadi kesalahan saat menyimpan Kas Masuk. Detail: ' . $e->getMessage()]]], 500);
        }
    }

    public function show(string $id)
    {
        // Sama seperti JurnalUmumController, view modal akan menata debit dan kredit
        $jurnal = AccHdjurnal::with('details.perkiraan', 'user')->find($id);
         if (!$jurnal) {
            return response()->json(['message' => 'Data kas masuk tidak ditemukan'], 404);
        }
        return response()->json(['jurnal' => $jurnal]); // Kirim dalam wrapper 'jurnal'
    }

    public function edit(string $id)
    {
        $jurnal = AccHdjurnal::with('details.perkiraan')->find($id);
        if (!$jurnal) {
            return response()->json(['error' => 'Data kas masuk tidak ditemukan.'], 404);
        }

        // Pisahkan detail debet (rekening tujuan) dan kredit (diterima dari)
        $rekeningTujuanDetail = null;
        $kreditDetails = [];
        foreach ($jurnal->details as $detail) {
            if ($detail->debet > 0) {
                $rekeningTujuanDetail = $detail;
            } else {
                $kreditDetails[] = $detail;
            }
        }

        $perkiraan = AccKira::orderBy('cls_kiraid')->get(['id', 'cls_kiraid', 'cls_ina']);

        return response()->json([
            'jurnalHeader' => $jurnal, // header info
            'rekeningTujuanDetail' => $rekeningTujuanDetail,
            'kreditDetails' => $kreditDetails,
            'perkiraan' => $perkiraan,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_buat' => 'required|date',
            'rekening_tujuan_id' => 'required|exists:acc_kira,id',
            'lokasi_nama' => 'nullable|string|max:255', // <-- VALIDASI LOKASI
            'referensi' => 'nullable|string|max:255',
            'catatan_header' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.acc_kira_id' => 'required|exists:acc_kira,id',
            'details.*.kredit' => 'required|numeric|min:0.01',
            'details.*.catatan_detail' => 'nullable|string',
        ]); // Pesan error bisa disamakan

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalKredit = 0;
        foreach ($request->details as $detail) {
            $totalKredit += (float)$detail['kredit'];
        }

        if ($totalKredit <= 0) {
             return response()->json(['errors' => ['balance' => ['Total kredit harus lebih besar dari 0.']]], 422);
        }

        DB::beginTransaction();
        try {
            $header = AccHdjurnal::find($id);
            if (!$header) {
                DB::rollBack();
                return response()->json(['errors' => ['server' => ['Data kas masuk tidak ditemukan.']]], 404);
            }

            $now = Carbon::now();
            $header->update([
            'tanggal_buat' => $request->tanggal_buat,
            'tanggal_edit' => $now,
            'lokasi_nama' => $request->lokasi_nama, // Pastikan ini ada jika masih diperlukan
            'referensi' => $request->referensi,
            'catatan' => $request->catatan_header,
            'user_id' => Auth::id(),
            'nominal' => $totalKredit,
            'lokasi_nama' => $request->lokasi_nama, // <-- UPDATE LOKASI
            'tipe_jurnal' => 'KM', // <-- Pastikan tipe tetap KM saat update
            ]);

            $header->details()->delete(); // Hapus detail lama

            // Simpan Detail DEBIT (Rekening Tujuan)
            AccDtjurnal::create([
                'acc_hd_jurnal_id' => $header->id,
                'acc_kira_id' => $request->rekening_tujuan_id,
                'debet' => $totalKredit,
                'kredit' => 0,
                'catatan' => 'Kas Masuk ke ' . AccKira::find($request->rekening_tujuan_id)->cls_ina,
            ]);

            // Simpan Detail KREDIT (Diterima Dari)
            foreach ($request->details as $detailData) {
                AccDtjurnal::create([
                    'acc_hd_jurnal_id' => $header->id,
                    'acc_kira_id' => $detailData['acc_kira_id'],
                    'debet' => 0,
                    'kredit' => (float)$detailData['kredit'],
                    'catatan' => $detailData['catatan_detail'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Kas Masuk ' . $header->no_jurnal . ' berhasil diperbarui.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Kas Masuk ID ' . $id . ': ' . $e->getMessage()  . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['errors' => ['server' => ['Terjadi kesalahan saat memperbarui Kas Masuk. ' . $e->getMessage()]]], 500);
        }
    }

    public function destroy(string $id)
    {
        // Sama seperti JurnalUmumController
        DB::beginTransaction();
        try {
            $header = AccHdjurnal::find($id);
            if (!$header) {
                DB::rollBack();
                 return response()->json(['error' => 'Data kas masuk tidak ditemukan.'], 404);
            }
            $noBukti = $header->no_jurnal;
            $header->delete(); // Cascade delete akan menghapus details
            DB::commit();
             return response()->json(['success' => 'Kas Masuk ' . $noBukti . ' berhasil dihapus.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Kas Masuk ID ' . $id . ': ' . $e->getMessage());
             return response()->json(['error' => 'Gagal menghapus Kas Masuk.'], 500);
        }
    }
}
