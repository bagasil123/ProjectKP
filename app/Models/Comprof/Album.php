<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    protected $table = 'albums';
    
    protected $fillable = [
        'kategori_id',
        'nama_album',
        'deskripsi'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAlbum::class, 'kategori_id');
    }

    public function gambarAlbums(): HasMany
    {
        return $this->hasMany(GambarAlbum::class, 'album_id');
    }

    public function getCoverImageUrlAttribute()
    {
        if ($this->gambarAlbums->isNotEmpty()) {
            return asset('storage/' . $this->gambarAlbums->first()->path_gambar);
        }
        return asset('images/default-album.jpg');
    }
}