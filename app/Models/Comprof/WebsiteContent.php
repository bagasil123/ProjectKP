<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteContent extends Model
{
    protected $table = 'website_contents';
    protected $fillable = [
        'submenu_id',
        'judul',
        'isi',
        'gambar',
        'kategori_berita_id',
        'kategori_album_id',
        'status',
        'halaman_depan'
    ];

    public function submenu(): BelongsTo
    {
        return $this->belongsTo(Submenu::class);
    }

    public function kategoriBerita(): BelongsTo
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_berita_id');
    }

    public function kategoriAlbum(): BelongsTo
    {
        return $this->belongsTo(KategoriAlbum::class, 'kategori_album_id');
    }
}
