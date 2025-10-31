<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comprof\KategoriBerita;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class KategoriBeritaController extends Controller
{
    public function index()
    {
        $kategoris = KategoriBerita::withCount('beritas')->orderBy('kategori_berita')->get();
        return view('comprof.kategoriberita.index', compact('kategoris'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_berita' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_berita', 'kategori_berita')
            ],
        ]);

        KategoriBerita::create($validated);

        return response()->json([
            'message' => 'Kategori berita berhasil ditambahkan',
            'data' => $validated
        ]);
    }

    public function update(Request $request, KategoriBerita $kategoriberita): JsonResponse
    {
        $validated = $request->validate([
            'kategori_berita' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_berita', 'kategori_berita')->ignore($kategoriberita->id)
            ],
        ]);

        $kategoriberita->update($validated);

        return response()->json([
            'message' => 'Kategori berita berhasil diperbarui',
            'data' => $kategoriberita
        ]);
    }

    public function destroy(KategoriBerita $kategoriberita): JsonResponse
    {
        $kategoriberita->delete();

        return response()->json([
            'message' => 'Kategori berita berhasil dihapus',
            'data' => $kategoriberita
        ]);
    }
}