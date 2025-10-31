<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comprof\KategoriBerita;
use App\Models\Comprof\Berita;
use App\Models\Comprof\SetPerusahaan;
use App\Models\Comprof\Menu;
use App\Models\Comprof\WebsiteContent;

class NewsController extends Controller
{
    protected $sharedData;

    public function __construct()
    {
        $this->sharedData = [
            'company' => SetPerusahaan::first(),
            'menus' => Menu::with('submenus')
                ->where('status', 1)
                ->orderBy('urutan')
                ->get()
        ];
    }

    public function index()
    {
        $data = array_merge($this->sharedData, [
            'kategoriBeritas' => KategoriBerita::withCount(['beritas' => function($query) {
                $query->where('status', 1);
            }])->get(),
            'latestNews' => Berita::where('status', 1)
                ->latest()
                ->limit(3)
                ->get(),
            'beritas' => Berita::where('status', 1)
                ->latest()
                ->paginate(6)
        ]);

        return view('frontend.news.index', $data);
    }

    public function category($id)
    {
        $kategori = KategoriBerita::findOrFail($id);
        $beritas = $kategori->beritas()
            ->where('status', 1)
            ->latest()
            ->paginate(6);

        // Cari konten website yang terkait dengan kategori ini
        $content = WebsiteContent::where('kategori_berita_id', $id)->first();

        $data = array_merge($this->sharedData, [
            'kategori' => $kategori,
            'beritas' => $beritas,
            'latestNews' => Berita::where('status', 1)
                ->latest()
                ->limit(3)
                ->get(),
            'content' => $content // Tambahkan ini
        ]);

        return view('frontend.news.category', $data);
    }

    public function show($slug)
    {
        $berita = Berita::where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();
        
        // Increment view count
        $berita->increment('views');
        
        $data = array_merge($this->sharedData, [
            'berita' => $berita,
            'relatedNews' => Berita::where('kategori_id', $berita->kategori_id)
                ->where('id', '!=', $berita->id)
                ->where('status', 1)
                ->limit(3)
                ->get()
        ]);

        return view('frontend.news.show', $data);
    }
}