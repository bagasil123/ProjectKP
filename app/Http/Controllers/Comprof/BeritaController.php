<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use App\Models\Comprof\Berita;
use App\Models\Comprof\KategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    public function index()
    {
        $beritas = Berita::with('kategori')->latest()->paginate(10);
        $kategoris = KategoriBerita::all();
        return view('comprof.berita.index', compact('beritas', 'kategoris'));
    }

    public function create()
    {
        $kategoris = KategoriBerita::all();
        return view('comprof.berita.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_berita' => 'required|string|max:255',
            'isi_berita' => 'required|string',
            'gambar_berita' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori_id' => 'required|exists:kategori_berita,id',
            'penulis' => 'nullable|string|max:100',
            'status' => 'required|boolean',
        ]);

        if ($request->hasFile('gambar_berita')) {
            $validated['gambar_berita'] = $request->file('gambar_berita')->store('berita', 'public');
        }

        // Generate slug
        $validated['slug'] = Str::slug($validated['judul_berita']);

        Berita::create($validated);

        return redirect()->route('comprof.berita.index')
                         ->with('success', 'Berita berhasil ditambahkan');
    }

    public function edit(Berita $berita)
    {
        $kategoris = KategoriBerita::all();
        return view('comprof.berita.edit', compact('berita', 'kategoris'));
    }

    public function update(Request $request, Berita $berita)
    {
        $validated = $request->validate([
            'judul_berita' => 'required|string|max:255',
            'isi_berita' => 'required|string',
            'gambar_berita' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori_id' => 'required|exists:kategori_berita,id',
            'penulis' => 'nullable|string|max:100',
            'status' => 'required|boolean',
        ]);

        if ($request->hasFile('gambar_berita')) {
            // Hapus gambar lama
            if ($berita->gambar_berita) {
                Storage::disk('public')->delete($berita->gambar_berita);
            }
            $validated['gambar_berita'] = $request->file('gambar_berita')->store('berita', 'public');
        }

        // Update slug jika judul berubah
        if ($berita->judul_berita !== $validated['judul_berita']) {
            $validated['slug'] = Str::slug($validated['judul_berita']);
        }

        $berita->update($validated);

        return redirect()->route('comprof.berita.index')
                         ->with('success', 'Berita berhasil diperbarui');
    }

    public function destroy(Berita $berita)
    {
        if ($berita->gambar_berita) {
            Storage::disk('public')->delete($berita->gambar_berita);
        }
        $berita->delete();

        return redirect()->route('comprof.berita.index')
                         ->with('success', 'Berita berhasil dihapus');
    }
}