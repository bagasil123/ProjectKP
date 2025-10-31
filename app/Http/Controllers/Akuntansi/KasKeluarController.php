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

class KasKeluarController extends Controller
{
    private function generateNoBuktiKasKeluar(): string // Ganti nama fungsi
    {
        $currentYearMonth = date('dm');
        $prefix = date('d') . date('m') . '-';
        $suffix = '-KK'; // KK untuk Kas Keluar

        $lastBukti = AccHdjurnal::where('no_jurnal', 'like', $prefix . '%')
                                  ->where('tipe_jurnal', 'KK') // Pastikan filter tipe_jurnal KK
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
        $jurnals = AccHdjurnal::with('user')
                        ->where('tipe_jurnal', 'KK') // Filter hanya Kas Keluar
                        ->orderBy('tanggal_buat', 'desc')
                        ->orderBy('no_jurnal', 'desc')
                        ->paginate(15);
        $warehouses = Warehouse::orderBy('WARE_Name')->get(['WARE_Name']); // Cukup ambil WARE_Name

        // Perkiraan untuk Rekening Tujuan (Kas/Bank) dan Diterima Dari
        $perkiraan = AccKira::orderBy('cls_kiraid')->get(['id', 'cls_kiraid', 'cls_ina']);

        return view('akunting.kaskeluar.index', compact('jurnals', 'perkiraan', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_buat' => 'required|date',
            'rekening_asal_id' => 'required|exists:acc_kira,id', // Diubah dari rekening_tujuan_id
            'lokasi_nama' => 'nullable|string|max:255',
            'referensi' => 'nullable|string|max:255',
            'catatan_header' => 'nullable|string',
            'details' => 'required|array|min:1', // Minimal 1 baris pengeluaran (debet)
            'details.*.acc_kira_id' => 'required|exists:acc_kira,id',
            'details.*.debet' => 'required|numeric|min:0.01', // Diubah ke debet
            'details.*.catatan_detail' => 'nullable|string',
        ], [
            'rekening_asal_id.required' => 'Rekening Asal harus dipilih.', // Diubah
            'details.required' => 'Minimal harus ada 1 baris detail pengeluaran.', // Diubah
            'details.min' => 'Minimal harus ada 1 baris detail pengeluaran.', // Diubah
            'details.*.acc_kira_id.required' => 'Kode Perkiraan pada baris :position harus diisi.',
            'details.*.debet.required' => 'Debet pada baris :position harus diisi.', // Diubah
            'details.*.debet.min' => 'Debet pada baris :position harus lebih besar dari 0.', // Diubah
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalDebet = 0; // Diubah dari totalKredit
        foreach ($request->details as $detail) {
            $totalDebet += (float)$detail['debet']; // Diubah
        }

        if ($totalDebet <= 0) {
            return response()->json(['errors' => ['balance' => ['Total pengeluaran (debet) harus lebih besar dari 0.']]], 422); // Diubah
        }

        DB::beginTransaction();
        try {
            $noBukti = $this->generateNoBuktiKasKeluar();
            $now = Carbon::now();

            $header = AccHdjurnal::create([
                'no_jurnal' => $noBukti,
                'tanggal_buat' => Carbon::parse($request->tanggal_buat),
                'tanggal_edit' => $now,
                'lokasi_nama' => $request->lokasi_nama,
                'referensi' => $request->referensi,
                'catatan' => $request->catatan_header,
                'user_id' => Auth::id(),
                'nominal' => $totalDebet, // Nominal adalah total kas keluar (total debet)
                'tipe_jurnal' => 'KK',    // Set tipe jurnal ke Kas Keluar
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 1. Simpan Detail DEBET (Dibayarkan Untuk)
            foreach ($request->details as $detailData) {
                AccDtjurnal::create([
                    'acc_hd_jurnal_id' => $header->id,
                    'acc_kira_id' => $detailData['acc_kira_id'],
                    'debet' => (float)$detailData['debet'], // Debet diisi
                    'kredit' => 0,
                    'catatan' => $detailData['catatan_detail'],
                ]);
            }

            // 2. Simpan Detail KREDIT (Rekening Asal)
            AccDtjurnal::create([
                'acc_hd_jurnal_id' => $header->id,
                'acc_kira_id' => $request->rekening_asal_id,
                'debet' => 0,
                'kredit' => $totalDebet, // Kredit diisi sejumlah total debet
                'catatan' => 'Kas Keluar dari ' . AccKira::find($request->rekening_asal_id)->cls_ina,
            ]);

            DB::commit();
            return response()->json(['success' => 'Kas Keluar berhasil disimpan dengan nomor: ' . $noBukti], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Kas Keluar: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['errors' => ['server' => ['Terjadi kesalahan saat menyimpan Kas Keluar. Detail: ' . $e->getMessage()]]], 500);
        }
    }

    public function show(string $id)
    {
        $jurnal = AccHdjurnal::with('details.perkiraan', 'user')->find($id);
         if (!$jurnal || $jurnal->tipe_jurnal !== 'KK') { // Pastikan ini KK
            return response()->json(['message' => 'Data kas keluar tidak ditemukan'], 404);
        }
        return response()->json(['jurnal' => $jurnal]);
    }

    public function edit(string $id)
    {
        $jurnal = AccHdjurnal::with('details.perkiraan')->find($id);
        if (!$jurnal || $jurnal->tipe_jurnal !== 'KK') {
            return response()->json(['error' => 'Data kas keluar tidak ditemukan.'], 404);
        }

        $rekeningAsalDetail = null;
        $debetDetails = []; // Diubah dari kreditDetails
        foreach ($jurnal->details as $detail) {
            if ($detail->kredit > 0) { // Rekening asal adalah yang kredit
                $rekeningAsalDetail = $detail;
            } else if ($detail->debet > 0) { // Hanya ambil yang benar-benar debet untuk detail pengeluaran
                $debetDetails[] = $detail;
            }
        }

        $perkiraan = AccKira::orderBy('cls_kiraid')->get(['id', 'cls_kiraid', 'cls_ina']);

        return response()->json([
            'jurnalHeader' => $jurnal,
            'rekeningAsalDetail' => $rekeningAsalDetail,
            'debetDetails' => $debetDetails, // Diubah
            'perkiraan' => $perkiraan,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_buat' => 'required|date',
            'rekening_asal_id' => 'required|exists:acc_kira,id',
            'lokasi_nama' => 'nullable|string|max:255',
            'referensi' => 'nullable|string|max:255',
            'catatan_header' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.acc_kira_id' => 'required|exists:acc_kira,id',
            'details.*.debet' => 'required|numeric|min:0.01', // Diubah ke debet
            'details.*.catatan_detail' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalDebet = 0; // Diubah
        foreach ($request->details as $detail) {
            $totalDebet += (float)$detail['debet']; // Diubah
        }

        if ($totalDebet <= 0) {
             return response()->json(['errors' => ['balance' => ['Total pengeluaran (debet) harus lebih besar dari 0.']]], 422); // Diubah
        }

        DB::beginTransaction();
        try {
            $header = AccHdjurnal::find($id);
            if (!$header || $header->tipe_jurnal !== 'KK') {
                DB::rollBack();
                return response()->json(['errors' => ['server' => ['Data kas keluar tidak ditemukan.']]], 404);
            }

            $now = Carbon::now();
            $header->update([
                'tanggal_buat' => Carbon::parse($request->tanggal_buat),
                'tanggal_edit' => $now,
                'lokasi_nama' => $request->lokasi_nama,
                'referensi' => $request->referensi,
                'catatan' => $request->catatan_header,
                'user_id' => Auth::id(),
                'nominal' => $totalDebet, // Diubah
                'tipe_jurnal' => 'KK',    // Pastikan tetap KK
            ]);

            $header->details()->delete(); // Hapus detail lama

            // Simpan Detail DEBET (Dibayarkan Untuk)
            foreach ($request->details as $detailData) {
                AccDtjurnal::create([
                    'acc_hd_jurnal_id' => $header->id,
                    'acc_kira_id' => $detailData['acc_kira_id'],
                    'debet' => (float)$detailData['debet'], // Debet diisi
                    'kredit' => 0,
                    'catatan' => $detailData['catatan_detail'],
                ]);
            }

            // Simpan Detail KREDIT (Rekening Asal)
            AccDtjurnal::create([
                'acc_hd_jurnal_id' => $header->id,
                'acc_kira_id' => $request->rekening_asal_id,
                'debet' => 0,
                'kredit' => $totalDebet, // Kredit diisi sejumlah total debet
                'catatan' => 'Kas Keluar dari ' . AccKira::find($request->rekening_asal_id)->cls_ina,
            ]);

            DB::commit();
            return response()->json(['success' => 'Kas Keluar ' . $header->no_jurnal . ' berhasil diperbarui.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Kas Keluar ID ' . $id . ': ' . $e->getMessage()  . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['errors' => ['server' => ['Terjadi kesalahan saat memperbarui Kas Keluar. ' . $e->getMessage()]]], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $header = AccHdjurnal::find($id);
            if (!$header || $header->tipe_jurnal !== 'KK') {
                DB::rollBack();
                 return response()->json(['error' => 'Data kas keluar tidak ditemukan.'], 404);
            }
            $noBukti = $header->no_jurnal;
            $header->delete();
            DB::commit();
             return response()->json(['success' => 'Kas Keluar ' . $noBukti . ' berhasil dihapus.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Kas Keluar ID ' . $id . ': ' . $e->getMessage());
             return response()->json(['error' => 'Gagal menghapus Kas Keluar.'], 500);
        }
    }
}
