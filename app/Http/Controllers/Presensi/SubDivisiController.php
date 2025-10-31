<?php

namespace App\Http\Controllers\Presensi;
use App\Http\Controllers\Controller;
use App\Models\Presensi\Divisi;
use App\Models\Presensi\SubDivisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // Untuk validasi
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;

class SubDivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $SubDivisis = SubDivisi::all();
        $Divisis = Divisi::all();
        return view('presensi.subdivisi.index', compact('SubDivisis', 'Divisis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'div_divcode'   => 'required|string|exists:ts_div,div_auto',
            'Div_Code'      => 'required|string|max:20|unique:ts_subdiv,Div_Code',
            'Div_Name'      => 'required|string|max:50',
            'DIV_NIK'       => 'nullable|string|max:20',
        ]);
        
        $validated['Div_EntryID'] = Auth::user()->id;
        $validated['Div_Entrydate'] = now();

        SubDivisi::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Subdivisi berhasil ditambahkan.',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubDivisi $SubDivisi)
    {
        $validated = $request->validate([
            'div_divcode'   => 'required|string|max:4|exists:ts_div,div_auto',
            'Div_Code'      => 'required|string|max:20',
            'Div_Name'      => 'required|string|max:50',
            'DIV_NIK'       => 'nullable|string|max:20',
        ]);
        
        $validated['Div_UserID'] = Auth::user()->id;
        $validated['Div_LastUpdate'] = now();

        $SubDivisi->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Subdivisi berhasil ditambahkan.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $SubDivisi = SubDivisi::findOrFail($id);
        $SubDivisi->delete();
        return response()->json(['message' => 'Data Sub-Divisi berhasil dihapus.']);

    }

    public function getByDivision($Divisi)
    {
        // Cari semua sub-divisi yang memiliki div_divcode sama dengan ID divisi yang diberikan
        $subDivisis = SubDivisi::where('div_divcode', $Divisi)->get();

        // Kembalikan hasilnya dalam format JSON
        return response()->json($subDivisis);
    }
}
