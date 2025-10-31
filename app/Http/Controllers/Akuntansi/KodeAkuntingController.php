<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Models\Akuntansi\AccClass;
use App\Models\Akuntansi\AccSubclass;
use App\Models\Akuntansi\AccKira;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KodeAkuntingController extends Controller
{
    public function index()
    {
        $kodes = AccKira::with(['accClass', 'accSubclass'])
            ->orderBy('cls_kiraid')
            ->get();

        $classes = AccClass::all();
        $subclasses = AccSubclass::all();
        $kode = null;

        return view('akunting.kodeakunting.index', compact('kodes', 'classes', 'subclasses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cls_id' => 'required|exists:acc_class,cls_id',
            'cls_subid' => 'required|exists:acc_subclass,cls_subid',
            'cls_ina' => 'required|string|max:255',
            'status' => 'required|in:umum,cash/bank',
            'd_k' => 'required|in:debet,kredit',
            'tanggal' => 'nullable|date',
        ]);

        // Generate new cls_kiraid (subclass-number + incremental 10)
        $prefix = $request->cls_subid;
        $lastKode = AccKira::where('cls_kiraid', 'like', $prefix . '%')
            ->orderBy('cls_kiraid', 'desc')
            ->first();

        if ($lastKode) {
            $lastNumber = intval(substr($lastKode->cls_kiraid, -3));
            $newNumber = $lastNumber + 10;
            $cls_kiraid = $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $cls_kiraid = $prefix . '-010';
        }

        AccKira::create([
            'cls_kiraid' => $cls_kiraid,
            'cls_id' => $request->cls_id,
            'cls_subid' => $request->cls_subid,
            'cls_ina' => $request->cls_ina,
            'status' => $request->status,
            'd_k' => $request->d_k,
            'tanggal' => $request->tanggal ?? now(),
        ]);

        return redirect()->route('kodeakunting.index')
            ->with('success', 'Kode akuntansi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kode = AccKira::findOrFail($id);
        $classes = AccClass::all();
        $subclasses = AccSubclass::all();

        return view('akunting.kodeakunting.edit', compact('kode', 'classes', 'subclasses'));
    }


    public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'cls_ina'   => 'required|string|max:255',
        'status'    => 'required|in:umum,cash/bank',
        'd_k'       => 'required|in:debet,kredit',
        'cls_id'    => 'required|exists:acc_class,cls_id',
        'cls_subid' => 'required|exists:acc_subclass,cls_subid',
        'tanggal'   => 'nullable|date',
    ]);

    $kode = AccKira::findOrFail($id);

    // Simpan cls_id dan cls_subid lama sebelum diupdate (jika perlu untuk logika lain)
    $old_cls_id = $kode->cls_id;
    $old_cls_subid = $kode->cls_subid;

    // Cek apakah cls_id atau cls_subid berubah
    $classificationChanged = ($kode->cls_id != $validatedData['cls_id']) || ($kode->cls_subid != $validatedData['cls_subid']);
    $new_cls_kiraid = $kode->cls_kiraid; // Default ke cls_kiraid yang lama

    if ($classificationChanged) {
        // Logika untuk men-generate cls_kiraid baru, mirip dengan di store()
        // Anda mungkin perlu membuat fungsi helper untuk ini agar tidak duplikasi kode
        $prefix = $validatedData['cls_subid']; // Prefix baru berdasarkan cls_subid yang baru dipilih
        $lastKodeInNewCategory = AccKira::where('cls_kiraid', 'like', $prefix . '%')
            ->where('id', '!=', $kode->id) // Abaikan record saat ini saat mencari nomor terakhir
            ->orderBy('cls_kiraid', 'desc')
            ->first();

        if ($lastKodeInNewCategory) {
            // Ambil 3 digit terakhir setelah tanda hubung terakhir
            $parts = explode('-', $lastKodeInNewCategory->cls_kiraid);
            $lastNumber = intval(end($parts)); // Ambil bagian setelah tanda hubung terakhir
            $newNumber = $lastNumber + 10;
            $new_cls_kiraid = $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        } else {
            // Jika tidak ada kode lain di kategori/subkategori baru ini
            $new_cls_kiraid = $prefix . '-010';
        }

        // Tambahan: Periksa apakah cls_kiraid baru yang di-generate sudah ada (kecuali untuk ID saat ini)
        // Ini untuk kasus yang sangat jarang terjadi jika ada proses lain yang membuat ID bersamaan
        $existingKodeWithNewKiraId = AccKira::where('cls_kiraid', $new_cls_kiraid)
                                           ->where('id', '!=', $kode->id)
                                           ->first();
        if ($existingKodeWithNewKiraId) {
            // Handle kasus duplikasi jika diperlukan, misalnya dengan error atau mencoba nomor lain
            // Untuk sekarang, kita bisa abaikan atau log error
            // Log::warning("Potensi duplikasi cls_kiraid saat update: {$new_cls_kiraid} untuk AccKira ID: {$kode->id}");
        }
    }

    // Update model
    $kode->update([
        'cls_kiraid' => $new_cls_kiraid, // Gunakan cls_kiraid yang baru atau yang lama
        'cls_ina'   => $validatedData['cls_ina'],
        'status'    => $validatedData['status'],
        'd_k'       => $validatedData['d_k'],
        'cls_id'    => $validatedData['cls_id'],
        'cls_subid' => $validatedData['cls_subid'],
        'tanggal'   => $request->input('tanggal', now()->toDateString()),
    ]);

    return redirect()->route('kodeakunting.index')
        ->with('success', 'Kode akuntansi berhasil diperbarui.');
}
    public function destroy($id)
{
    try {
        $kode = AccKira::findOrFail($id);
        $kode->delete();
        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Gagal menghapus data.'], 500);
    }
}

    public function getSubclassesByClass($classId)
    {
        $subclasses = AccSubclass::where('cls_id', $classId)
                                 ->orderBy('cls_subid') // atau order by nama jika perlu
                                 ->get(['cls_subid', 'cls_ina']); // Hanya ambil kolom yang dibutuhkan
        return response()->json($subclasses);
    }
}
