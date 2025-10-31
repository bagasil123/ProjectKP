<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comprof\Menu;
use App\Models\Comprof\Submenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SubMenuController extends Controller
{
    public function index()
    {
        $submenus = Submenu::with('menu')->orderBy('urut')->get();
        $menus = Menu::where('status', 1)->orderBy('urutan')->get();

        return view('comprof.settingsubmenu.index', compact('submenus', 'menus'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:setmenu,id',
            'nama_submenu' => [
                'required',
                'string',
                'max:100',
                Rule::unique('submenu_tabel', 'nama_submenu')
            ],
            'urut' => 'required|integer|min:0',
            'tautan' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        Submenu::create($validated);

        return response()->json([
            'message' => 'Sub Menu berhasil ditambahkan',
            'data' => $validated
        ]);
    }

    public function update(Request $request, Submenu $submenu): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:setmenu,id',
            'nama_submenu' => [
                'required',
                'string',
                'max:100',
                Rule::unique('submenu_tabel', 'nama_submenu')->ignore($submenu->id)
            ],
            'urut' => 'required|integer|min:0',
            'tautan' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $submenu->update($validated);

        return response()->json([
            'message' => 'Sub Menu berhasil diperbarui',
            'data' => $submenu
        ]);
    }

    public function destroy(Submenu $submenu): JsonResponse
    {
        $submenu->delete();

        return response()->json([
            'message' => 'Sub Menu berhasil dihapus',
            'data' => $submenu
        ]);
    }
}