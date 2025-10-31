<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comprof\Menu;
use App\Models\Comprof\Slider;
use App\Models\Comprof\SetPerusahaan;
use App\Models\Comprof\Datastaf;
use App\Models\Comprof\KategoriAlbum;
use App\Models\Comprof\KategoriBerita;
use App\Models\Comprof\Submenu;

class HomeController extends Controller
{
    protected $sharedData;

    public function __construct()
{
    $this->sharedData = [
        'company' => SetPerusahaan::first(),
        'menus' => Menu::with(['submenus' => function($query) {
            $query->where('status', true)
                  ->orderBy('urut'); // Ubah 'urutan' menjadi 'urut'
        }])
        ->where('status', true)
        ->orderBy('urutan')
        ->get()
    ];
}

    public function index()
    {
        $data = array_merge($this->sharedData, [
            'mainSlider' => Slider::where('status', 1)->latest()->first(),
            'sliders' => Slider::where('status', 1)->limit(5)->get(),
            'staffs' => Datastaf::where('status', 1)->limit(3)->get(),
            'kategoriAlbums' => KategoriAlbum::where('tampil_gallery', true)->get(),
            'kategoriBeritas' => KategoriBerita::all()
        ]);
        
        return view('frontend.home', $data);
    }

    public function about()
    {
        return view('frontend.about', $this->sharedData);
    }

    public function team()
    {
        $data = array_merge($this->sharedData, [
            'staffs' => Datastaf::where('status', 1)->get()
        ]);
        
        return view('frontend.team', $data);
    }
}