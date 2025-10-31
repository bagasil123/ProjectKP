<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GambarAlbum extends Model
{
    protected $table = 'gambar_albums';
    
    protected $fillable = [
        'album_id',
        'path_gambar',
        'judul_gambar',
        'deskripsi'
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'album_id');
    }
}