<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use App\Models\Presensi\LiburNasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LiburNasionalController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        LiburNasional::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Libur Nasional berhasil ditambahkan.',
            ]);
        }
    }

    public function update(Request $request, LiburNasional $LiburNasional)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        $LiburNasional->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Libur Nasional berhasil ditambahkan.',
            ]);
        }
    }

    public function edit($id)
    {
        $holiday = LiburNasional::findOrFail($id);
        return response()->json($holiday);
    }

 

    public function destroy(LiburNasional $liburNasional) // Gunakan Route-Model Binding
    {
        // Hapus typo "Holiday", gunakan variabel yang benar
        $liburNasional->delete(); 
        return response()->json(['success' => 'Data Libur Nasional berhasil dihapus.']);
    }
}
