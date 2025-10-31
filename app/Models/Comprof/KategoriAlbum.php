<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriAlbum extends Model
{
    protected $table = 'kategori_album';
    
    protected $fillable = [
        'kategori_album',
        'tampil_gallery'
    ];
    
    public function albums(): HasMany
    {
        return $this->hasMany(Album::class, 'kategori_id');
    }

    // Tambahkan relasi ke website content
    public function websiteContents()
    {
        return $this->hasMany(WebsiteContent::class, 'kategori_album_id');
    }
    
    public function getTampilGalleryHtmlAttribute()
    {
        return $this->tampil_gallery 
            ? '<span class="badge">Tampil Gallery</span>' 
            : '<span class="badge">Tidak Tampil Gallery</span>';
    }
}