<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Berita extends Model
{
    protected $table = 'berita'; // Pastikan sesuai dengan nama tabel di database
    
    protected $fillable = [
        'judul_berita',
        'slug',
        'isi_berita',
        'gambar_berita',
        'kategori_id',
        'penulis',
        'status'
    ];

    protected $appends = ['gambar_url'];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_id');
    }

    public function getGambarUrlAttribute()
    {
        return $this->gambar_berita ? asset('storage/' . $this->gambar_berita) : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($berita) {
            $berita->slug = Str::slug($berita->judul_berita);
        });

        static::updating(function ($berita) {
            $berita->slug = Str::slug($berita->judul_berita);
        });
    }
}