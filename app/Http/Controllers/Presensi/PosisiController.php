<?php

namespace App\Http\Controllers\Presensi;
use App\Http\Controllers\Controller;
use App\Models\Presensi\Posisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Support\Facades\Validator; // Untuk validasi

use Illuminate\Http\Request;

class PosisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Posisis = Posisi::all();

        return view('presensi.posisi.index', compact('Posisis'));
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
            'Pos_Code'   => 'required|string|max:60|unique:ts_position,Pos_Code',
            'Pos_Name'   => 'required|string|max:150',
        ]);

        Posisi::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Posisi berhasil ditambahkan.',
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Posisi $Posisi)
    {
        // hanya nama saja
        $validated = $request->validate([
            'Pos_Code'   => 'required|string|max:60',
            'Pos_Name' => 'required|string|max:150',
        ]);

        $validated['Pos_UserID']     = Auth::user()->id;
        $validated['Pos_LastUpdate'] = now();

        // hanya update nama dan user/lastupdate
        $Posisi->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message'=> 'Data Posisi berhasil diperbarui.']);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Posisi = Posisi::findOrFail($id);
        $Posisi->delete();
        return response()->json(['message' => 'Data Posisi berhasil dihapus.']);
    }
}
