<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comprof\KategoriAlbum;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class KategoriAlbumController extends Controller
{
    public function index()
    {
        $kategoris = KategoriAlbum::orderBy('kategori_album')->get();
        return view('comprof.kategorialbum.index', compact('kategoris'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_album' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_album', 'kategori_album')
            ],
            'tampil_gallery' => 'required|boolean',
        ]);

        KategoriAlbum::create($validated);

        return response()->json([
            'message' => 'Kategori album berhasil ditambahkan',
            'data' => $validated
        ]);
    }

    public function update(Request $request, KategoriAlbum $kategorialbum): JsonResponse
    {
        $validated = $request->validate([
            'kategori_album' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_album', 'kategori_album')->ignore($kategorialbum->id)
            ],
            'tampil_gallery' => 'required|boolean',
        ]);

        $kategorialbum->update($validated);

        return response()->json([
            'message' => 'Kategori album berhasil diperbarui',
            'data' => $kategorialbum
        ]);
    }

    public function destroy(KategoriAlbum $kategorialbum): JsonResponse
    {
        $kategorialbum->delete();

        return response()->json([
            'message' => 'Kategori album berhasil dihapus',
            'data' => null
        ]);
    }
}