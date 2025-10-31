<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use App\Models\Comprof\WebsiteContent;
use App\Models\Comprof\Submenu;
use App\Models\Comprof\KategoriBerita;
use App\Models\Comprof\KategoriAlbum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteContentController extends Controller
{
    public function index()
    {
        $contents = WebsiteContent::with(['submenu', 'kategoriBerita', 'kategoriAlbum'])->get();
        $submenus = Submenu::where('status', 1)->orderBy('urut')->get();
        $kategoriBeritas = KategoriBerita::orderBy('kategori_berita')->get();
        $kategoriAlbums = KategoriAlbum::orderBy('kategori_album')->get();
        
        return view('comprof.websitecontent.index', compact('contents', 'submenus', 'kategoriBeritas', 'kategoriAlbums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'submenu_id' => 'nullable|exists:submenu_tabel,id',
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori_berita_id' => 'nullable|exists:kategori_berita,id',
            'kategori_album_id' => 'nullable|exists:kategori_album,id',
            'status' => 'required|boolean',
            'halaman_depan' => 'required|boolean',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('websitecontent', 'public');
        }

        WebsiteContent::create($validated);

        return response()->json([
            'message' => 'Konten berhasil ditambahkan!',
            'data' => $validated
        ]);
    }

    public function update(Request $request, $id)
    {
        $websitecontent = WebsiteContent::findOrFail($id);
        
        $validated = $request->validate([
            'submenu_id' => 'nullable|exists:submenu_tabel,id',
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori_berita_id' => 'nullable|exists:kategori_berita,id',
            'kategori_album_id' => 'nullable|exists:kategori_album,id',
            'status' => 'required|boolean',
            'halaman_depan' => 'required|boolean',
        ]);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($websitecontent->gambar) {
                Storage::disk('public')->delete($websitecontent->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('websitecontent', 'public');
        } elseif ($request->has('remove_image')) {
            // Hapus gambar jika checkbox dicentang
            if ($websitecontent->gambar) {
                Storage::disk('public')->delete($websitecontent->gambar);
                $validated['gambar'] = null;
            }
        }

        $websitecontent->update($validated);

        return response()->json([
            'message' => 'Konten berhasil diperbarui!',
            'data' => $websitecontent
        ]);
    }

    public function destroy($id)
    {
        $websitecontent = WebsiteContent::findOrFail($id);
        
        if ($websitecontent->gambar) {
            Storage::disk('public')->delete($websitecontent->gambar);
        }
        $websitecontent->delete();
        
        return response()->json([
            'message' => 'Konten berhasil dihapus!',
            'data' => null
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $path = $request->file('image')->store('websitecontent/summernote', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah gambar'
            ], 500);
        }
    }
}