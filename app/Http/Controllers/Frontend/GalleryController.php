<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Comprof\KategoriAlbum;
use App\Models\Comprof\Album; // Added missing Album import
use App\Models\Comprof\SetPerusahaan;
use App\Models\Comprof\Menu;

class GalleryController extends Controller
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
        $kategoriAlbums = KategoriAlbum::withCount('albums')
            ->with(['albums' => function ($query) {
                $query->orderBy('created_at', 'desc')->take(1);
            }, 'albums.gambarAlbums'])
            ->where('tampil_gallery', true)
            ->orderBy('kategori_album', 'asc')
            ->get();

        $data = array_merge($this->sharedData, [
            'kategoriAlbums' => $kategoriAlbums
        ]);

        return view('frontend.gallery.index', $data);
    }

    public function show($id, Request $request)
    {
        try {
            $kategori = KategoriAlbum::with(['albums' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->with(['gambarAlbums' => function ($q) {
                        $q->orderBy('created_at', 'desc');
                    }]);
            }])
                ->where('tampil_gallery', true)
                ->findOrFail($id);

            // Handle album parameter
            if ($request->has('album')) {
                $albumId = $request->query('album');
                $album = $kategori->albums->where('id', $albumId)->first();
                
                if (!$album) {
                    abort(404, 'Album not found in this category');
                }
                
                return $this->showAlbum($album);
            }

            $coverImage = null;
            foreach ($kategori->albums as $album) {
                if ($album->gambarAlbums->isNotEmpty()) {
                    $coverImage = $album->gambarAlbums->first()->path_gambar;
                    break;
                }
            }

            $data = array_merge($this->sharedData, [
                'kategori' => $kategori,
                'albums' => $kategori->albums,
                'coverImage' => $coverImage
            ]);

            return view('frontend.gallery.show', $data);

        } catch (\Exception $e) {
            abort(404, 'Gallery category not found');
        }
    }

    private function showAlbum(Album $album)
    {
        $data = array_merge($this->sharedData, [
            'album' => $album,
            'images' => $album->gambarAlbums,
            'kategori' => $album->kategori
        ]);

        return view('frontend.gallery.album', $data);
    }
}