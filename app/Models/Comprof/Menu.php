<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class Menu extends Model
{
    protected $table = 'setmenu';

    protected $fillable = [
        'nama_menu',
        'route_name',
        'tautan',
        'urutan',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Relasi ke submenu
    public function submenus(): HasMany
    {
        return $this->hasMany(Submenu::class, 'menu_id');
    }

    // Accessor untuk status dalam format HTML
    public function getStatusHtmlAttribute()
    {
        return $this->status
            ? '<span class="badge-active"">Aktif</span>'
            : '<span class="badge-inactive">Tidak Aktif</span>';
    }

    public function getFinalLinkAttribute()
    {
        // Jika route_name ada dan route terdaftar
        if ($this->route_name && Route::has($this->route_name)) {
            return route($this->route_name);
        }
        
        // Jika ada tautan langsung
        if ($this->tautan) {
            // Jika tautan internal (mulai dengan /)
            if (str_starts_with($this->tautan, '/')) {
                return url($this->tautan);
            }
            return $this->tautan;
        }
        
        return '#';
    }

    public function getLinkTargetAttribute()
    {
        if (empty($this->tautan)) {
            return '_self';
        }
        return (str_starts_with($this->tautan, 'http') && !str_contains($this->tautan, request()->getHost()))
            ? '_blank'
            : '_self';
    }
}