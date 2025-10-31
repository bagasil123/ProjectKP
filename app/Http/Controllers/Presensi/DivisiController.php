<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use App\Models\Presensi\Divisi;
use App\Models\Presensi\SubDivisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Support\Facades\Validator; // Untuk validasi
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;

class DivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Divisis = Divisi::all();
        $SubDivisis = SubDivisi::all(); 

        return view('presensi.divisi.index', compact('Divisis', 'SubDivisis'));
    }

    /**
     * Show the form for creating a new resource.
     */
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Div_Code'      => 'required|string|max:20|unique:ts_div,Div_Code',
            'Div_Name'      => 'required|string|max:50',
            'DIV_NIK'       => 'nullable|string|max:20',
            'DIV_SHIFTYN'   => 'required|in:Y,T',
            'DIV_BIAYA'     => 'nullable|in:Y,T',
        ]);
        
        $validated['Div_EntryID'] = Auth::user()->id;
        $validated['Div_Entrydate'] = now();


        Divisi::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data divisi berhasil ditambahkan.',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Muat Divisi beserta relasi SubDivisi
        $SubDivisis = SubDivisi::all();
        $divisi = Divisi::with('SubDivisi')
            ->findOrFail($id);

        return response()->json($divisi);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Divisi $Divisi)
    // {
    //     return view('presensi.divisi', compact('Divisi'));
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Divisi $Divisi)
    {
        $validated = $request->validate([
            'Div_Code'    => 'required|string|max:20',
            'Div_Name'    => 'required|string|max:50',
            'DIV_NIK'     => 'nullable|string|max:20',
            'DIV_SHIFTYN' => 'required|in:Y,T',
            'DIV_BIAYA'   => 'nullable|in:Y,T',
        ]);

        $validated['Div_UserID']     = Auth::id();
        $validated['Div_LastUpdate'] = now();
        $Divisi->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message'=> 'Data Divisi berhasil diperbarui.'
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Divisi = Divisi::findOrFail($id);
        $Divisi->delete();
    
        return response()->json(['message' => 'Data Divisi berhasil dihapus.']);
    }
}
