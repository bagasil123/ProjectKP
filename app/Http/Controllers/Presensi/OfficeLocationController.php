<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use App\Models\Presensi\OfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfficeLocationController extends Controller
{
    /**
     * Menyimpan data lokasi kantor baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:m_officeloc,name',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buat record baru
        OfficeLocation::create($validator->validated());

        return response()->json(['success' => 'Lokasi kantor berhasil ditambahkan.']);
    }

    /**
     * Memperbarui data lokasi kantor yang ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OfficeLocation  $officeLocation
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, OfficeLocation $officeLocation)
    {
        // Validasi input dari form
        $validator = Validator::make($request->all(), [
            // Pastikan nama unik, kecuali untuk data yang sedang diedit
            'name' => 'required|string|max:255|unique:m_officeloc,name,' . $officeLocation->id,
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update record yang ada
        $officeLocation->update($validator->validated());

        return response()->json(['success' => 'Lokasi kantor berhasil diperbarui.']);
    }

    /**
     * Menghapus data lokasi kantor dari database.
     *
     * @param  \App\Models\OfficeLocation  $officeLocation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(OfficeLocation $officeLocation)
    {
        try {
            $officeLocation->delete();
            return response()->json(['success' => 'Lokasi kantor berhasil dihapus.']);
        } catch (\Exception $e) {
            // Tangani jika ada error, misalnya karena relasi foreign key
            return response()->json(['message' => 'Gagal menghapus data. Kemungkinan lokasi ini masih digunakan.'], 500);
        }
    }
}
