<?php

namespace App\Models\keamanan;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id'];

    /**
     * Relasi ke role (banyak ke banyak)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu');
    }

    /**
     * Relasi ke submenu (anak dari menu)
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    /**
     * Relasi ke parent menu (jika ini adalah submenu)
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function roleMenus()
    {
        return $this->hasMany(RoleMenu::class, 'menu_id', 'id');
    }
}
