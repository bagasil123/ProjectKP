<?php

// app/Models/Comprof/Submenu.php
namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submenu extends Model
{
    protected $table = 'submenu_tabel';

    protected $fillable = [
        'menu_id',
        'nama_submenu',
        'tautan',
        'urut',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // Perbaikan relasi ke website content
    public function websiteContent(): HasOne
    {
        return $this->hasOne(WebsiteContent::class, 'submenu_id');
    }

    public function getStatusHtmlAttribute()
    {
        return $this->status
            ? '<span class="badge-active">Aktif</span>'
            : '<span class="badge-inactive">Tidak Aktif</span>';
    }

    public function getSafeLinkAttribute(): string
    {
        // Jika ada website content, gunakan route ke halaman konten
        if ($this->websiteContent) {
            return route('page.show', $this->websiteContent->id);
        }

        if (empty($this->tautan)) {
            return '#';
        }

        // Jika tautan internal (dimulai dengan /)
        if (str_starts_with($this->tautan, '/')) {
            return url($this->tautan);
        }

        // Untuk tautan eksternal, gunakan tautan langsung
        return $this->tautan;
    }

    public function getLinkTargetAttribute(): string
    {
        if (empty($this->tautan)) {
            return '_self';
        }

        // Jika tautan internal (termasuk route page.show) atau anchor, buka di tab yang sama
        if (str_starts_with($this->tautan, '/') || str_starts_with($this->tautan, '#')) {
            return '_self';
        }

        // Untuk tautan eksternal, buka di tab baru
        return '_blank';
    }
}