<?php

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use App\Models\Presensi\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ShiftController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_code' => 'required|string|max:10|unique:m_shift,shift_code,' . $request->id,
            'shift_name' => 'required|string|max:50',
            'jam_in' => 'required|date_format:H:i',
            'jam_out' => 'required|date_format:H:i',
        ]);

        Shift::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Shift berhasil ditambahkan.',
            ]);
        }
    }

    public function update(Request $request, Shift $Shift)
    {
        $validated = $request->validate([
            'shift_code' => 'required|string|max:10|unique:m_shift,shift_code,' . $request->id,
            'shift_name' => 'required|string|max:50',
            'jam_in' => 'required|',
            'jam_out' => 'required|',
        ]);

        $Shift->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Shift berhasil ditambahkan.',
            ]);
        }
    }

    public function edit($id)
    {
        $Shift = Shift::findOrFail($id);
        return response()->json($Shift);
    }
    public function destroy(Shift $shift) // Gunakan Route-Model Binding
    {
        $shift->delete();
        return response()->json(['success' => 'Data Shift berhasil dihapus.']);
    }
}
