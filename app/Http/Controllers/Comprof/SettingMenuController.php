<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comprof\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SettingMenuController extends Controller
{
    public function index()
    {
        $setmenus = Menu::orderBy('urutan')->get();
        return view('comprof.settingmenu.index', compact('setmenus'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_menu' => [
                'required',
                'string',
                'max:100',
                Rule::unique('setmenu', 'nama_menu')
            ],
            'urutan' => 'required|integer|min:0',
            'status' => 'required|boolean',
        ]);

        Menu::create($validated);

        return response()->json([
            'message' => 'Menu berhasil ditambahkan',
            'data' => $validated
        ]);
    }

    public function update(Request $request, Menu $settingmenu): JsonResponse
    {
        $validated = $request->validate([
            'nama_menu' => [
                'required',
                'string',
                'max:100',
                Rule::unique('setmenu', 'nama_menu')->ignore($settingmenu->id)
            ],
            'urutan' => 'required|integer|min:0',
            'status' => 'required|boolean',
        ]);

        $settingmenu->update($validated);

        return response()->json([
            'message' => 'Menu berhasil diperbarui',
            'data' => $settingmenu
        ]);
    }

    public function destroy(Menu $settingmenu): JsonResponse
    {
        $settingmenu->delete();

        return response()->json([
            'message' => 'Menu berhasil dihapus',
            'data' => $settingmenu
        ]);
    }
}
