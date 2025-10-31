<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    protected $table = 'sliders';
    protected $fillable = [
        'title',
        'link',
        'image',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'status_html',
        'clean_title'
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/default-slider.png');
    }

    public function getStatusHtmlAttribute()
    {
        return $this->status 
            ? '<span class="badge-active">Aktif</span>'
            : '<span class="badge-inactive">Tidak Aktif</span>';
    }

    public function getCleanTitleAttribute()
    {
        return strip_tags($this->title);
    }
}