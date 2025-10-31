<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SetPerusahaan extends Model
{
    protected $table = 'set_perusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'company_name',
        'address',
        'phone',
        'whatsapp',
        'email',
        'website',
        'email_account',
        'email_password',
        'email_host',
        'smtp_port',
        'tagline',
        'map_location',
        'logo',
        'icon',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['logo_url', 'icon_url'];

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return Storage::disk('public')->exists($this->logo) 
                ? asset('storage/' . $this->logo)
                : asset('images/default-logo.png');
        }
        return asset('images/default-logo.png');
    }

    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return Storage::disk('public')->exists($this->icon) 
                ? asset('storage/' . $this->icon)
                : asset('images/default-icon.png');
        }
        return asset('images/default-icon.png');
    }
}