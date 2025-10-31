<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comprof\Submenu;
use App\Models\Comprof\SetPerusahaan;
use App\Models\Comprof\Menu;
use App\Models\Comprof\WebsiteContent;
use App\Models\Comprof\KategoriAlbum;
use App\Models\Comprof\KategoriBerita;
use App\Models\Comprof\Berita;

class PageController extends Controller
{
    protected $sharedData;

    public function __construct()
    {
        $company = SetPerusahaan::first();
        
        $this->sharedData = [
            'company' => $company,
            'menus' => Menu::with(['submenus' => function($query) {
                $query->where('status', true)
                      ->orderBy('urut');
            }])
            ->where('status', true)
            ->orderBy('urutan')
            ->get(),
            'default_banner' => $company->banner_url ?? asset('images/default-banner.jpg')
        ];
    }

    public function show($id)
    {
        $content = WebsiteContent::findOrFail($id);
        
        // Jika konten ini terkait dengan kategori berita
        if ($content->kategori_berita_id) {
            return $this->showNewsCategory($content);
        }
        
        // Jika konten ini terkait dengan kategori album
        if ($content->kategori_album_id) {
            return $this->showAlbumCategory($content);
        }
        
        // Default: tampilkan konten biasa
        $relatedAlbums = null;
        $relatedNews = null;
        
        if ($content->kategori_album_id) {
            $relatedAlbums = KategoriAlbum::with(['albums' => function($query) {
                $query->with('gambarAlbums')->take(3);
            }])->find($content->kategori_album_id);
        }
        
        if ($content->kategori_berita_id) {
            $relatedNews = KategoriBerita::with(['beritas' => function($query) {
                $query->where('status', true)
                      ->latest()
                      ->take(3);
            }])->find($content->kategori_berita_id);
        }

        return view('frontend.page.show', array_merge($this->sharedData, [
            'content' => $content,
            'relatedAlbums' => $relatedAlbums,
            'relatedNews' => $relatedNews,
            'title' => $content->judul
        ]));
    }

    public function showBySubmenu($submenuId)
    {
        $submenu = Submenu::findOrFail($submenuId);
        
        if ($submenu->websiteContent) {
            return $this->show($submenu->websiteContent->id);
        }
        
        abort(404, 'Content not found for this submenu');
    }

    private function showNewsCategory(WebsiteContent $content)
    {
        $kategori = $content->kategoriBerita;
        $beritas = $kategori->beritas()
                            ->where('status', true)
                            ->latest()
                            ->paginate(6);

        return view('frontend.news.category', array_merge($this->sharedData, [
            'kategori' => $kategori,
            'beritas' => $beritas,
            'latestNews' => Berita::where('status', true)
                                 ->latest()
                                 ->limit(3)
                                 ->get(),
            'content' => $content
        ]));
    }

    private function showAlbumCategory(WebsiteContent $content)
    {
        $kategori = $content->kategoriAlbum;
        $albums = $kategori->albums()
                           ->with('gambarAlbums')
                           ->latest()
                           ->paginate(6);

        return view('frontend.gallery.category', array_merge($this->sharedData, [
            'kategori' => $kategori,
            'albums' => $albums,
            'content' => $content
        ]));
    }
}