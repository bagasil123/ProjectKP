<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use App\Models\Presensi\RealAbsensi;
use App\Models\Presensi\Employee;
use App\Models\Presensi\Jadwal;
use App\Models\Presensi\Divisi; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Menampilkan halaman utama data absensi.
     */
    public function index()
    {
        $Absensis = RealAbsensi::with('employee')->latest('TS_TANGGAL')->get();
        $employees = Employee::orderBy('emp_Name')->get();
        
        // PERBAIKAN: Ambil data divisi untuk dikirim ke view
        $divisi = Divisi::orderBy('Div_Name')->get(); 
        
        // PERBAIKAN: Kirim variabel $divisi ke view
        return view('presensi.absensi.index', compact('Absensis', 'employees', 'divisi'));
    }

    /**
     * Mengambil data absensi spesifik untuk ditampilkan di modal.
     */
    public function show($id)
    {
        $absensi = RealAbsensi::with('employee')->findOrFail($id);
        return response()->json($absensi);
    }

    /**
     * Menyimpan data absensi baru dari input manual admin.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'TS_EMP' => 'required|exists:m_employee,emp_Auto',
            'TS_TANGGAL' => 'required|date',
            'TS_JAMIN' => 'nullable|date_format:H:i',
            'TS_JAMOUT' => 'nullable|date_format:H:i|after_or_equal:TS_JAMIN',
            'TS_FOTO' => 'nullable|image|max:5048',
            'TS_FILE_PENDUKUNG' => 'nullable|file|mimes:pdf,jpg,png,doc,docx|max:2048',
            'TS_LATITUDE' => 'nullable|numeric',
            'TS_LONGITUDE' => 'nullable|numeric',
            'TS_STATUS' => 'nullable|string',
            'TS_NOTE' => 'nullable|string',
        ]);

        $employee = Employee::find($request->TS_EMP);
        if ($employee) {
            $validatedData['TS_NAME'] = $employee->emp_Name;
            $validatedData['TS_CODE'] = $employee->emp_Code;
        }

        if ($request->hasFile('TS_FOTO')) {
            $validatedData['TS_FOTO'] = basename($request->file('TS_FOTO')->store('public/absensi_fotos'));
        }
        if ($request->hasFile('TS_FILE_PENDUKUNG')) {
            $validatedData['TS_FILE_PENDUKUNG'] = basename($request->file('TS_FILE_PENDUKUNG')->store('public/dokumen_izin'));
        }

        RealAbsensi::create($validatedData);
        return response()->json(['success' => 'Data absensi berhasil ditambahkan.']);
    }

    /**
     * Mengambil data absensi untuk form edit.
     */
    public function edit($id)
    {
        $absensi = RealAbsensi::findOrFail($id);
        return response()->json($absensi);
    }

    /**
     * Memperbarui data absensi yang ada.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'TS_EMP' => 'required|exists:m_employee,emp_Auto',
            'TS_TANGGAL' => 'required|date',
            'TS_JAMIN' => 'nullable|date_format:H:i',
            'TS_JAMOUT' => 'nullable|date_format:H:i|after_or_equal:TS_JAMIN',
            'TS_FOTO' => 'nullable|image|max:5048',
            'TS_FILE_PENDUKUNG' => 'nullable|file|mimes:pdf,jpg,png,doc,docx|max:2048',
            'TS_LATITUDE' => 'nullable|numeric',
            'TS_LONGITUDE' => 'nullable|numeric',
            'TS_STATUS' => 'nullable|string',
            'TS_NOTE' => 'nullable|string',
        ]);

        $absensi = RealAbsensi::findOrFail($id);
        $data = $request->except(['TS_FOTO', 'TS_FILE_PENDUKUNG', 'delete_foto', 'delete_file_pendukung']);

        $employee = Employee::find($request->TS_EMP);
        if ($employee) {
            $data['TS_NAME'] = $employee->emp_Name;
            $data['TS_CODE'] = $employee->emp_Code;
        }

        if ($request->delete_foto == '1' && $absensi->TS_FOTO) {
            Storage::disk('public')->delete('absensi_fotos/' . $absensi->TS_FOTO);
            $data['TS_FOTO'] = null;
        }

        if ($request->hasFile('TS_FOTO')) {
            if ($absensi->TS_FOTO) Storage::disk('public')->delete('absensi_fotos/' . $absensi->TS_FOTO);
            $data['TS_FOTO'] = basename($request->file('TS_FOTO')->store('public/absensi_fotos'));
        }

        if ($request->hasFile('TS_FILE_PENDUKUNG')) {
            if ($absensi->TS_FILE_PENDUKUNG) Storage::disk('public')->delete('dokumen_izin/' . $absensi->TS_FILE_PENDUKUNG);
            $data['TS_FILE_PENDUKUNG'] = basename($request->file('TS_FILE_PENDUKUNG')->store('public/dokumen_izin'));
        }

        $absensi->update($data);
        return response()->json(['success' => 'Data absensi berhasil diperbarui.']);
    }

    /**
     * Menghapus data absensi.
     */
    public function destroy($id)
    {
        $absensi = RealAbsensi::findOrFail($id);
        
        if ($absensi->TS_FOTO) {
            Storage::disk('public')->delete('absensi_fotos/' . $absensi->TS_FOTO);
        }
        if ($absensi->TS_FILE_PENDUKUNG) {
            Storage::disk('public')->delete('dokumen_izin/' . $absensi->TS_FILE_PENDUKUNG);
        }
        
        $absensi->delete();
        return response()->json(['success' => 'Data absensi berhasil dihapus.']);
    }
}
