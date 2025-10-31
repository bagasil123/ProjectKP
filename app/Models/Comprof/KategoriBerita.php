<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBerita extends Model
{
    protected $table = 'kategori_berita';

    protected $fillable = [
        'kategori_berita',
    ];

    public function beritas(): HasMany
    {
        return $this->hasMany(Berita::class, 'kategori_id');
    }

    // Tambahkan relasi ke website content
    public function websiteContents()
    {
        return $this->hasMany(WebsiteContent::class, 'kategori_berita_id');
    }
}