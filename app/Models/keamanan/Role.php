<?php

namespace App\Models\keamanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $fillable = ['name'];

    // Relasi: Role hasMany Members
    public function members()
    {
        return $this->hasMany(Member::class, 'role_id', 'id');
    }

    // Relasi: Role belongsToMany Menu (many-to-many dengan tabel pivot role_menu)
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menu');
    }

    public function roleMenus()
    {
        return $this->hasMany(RoleMenu::class, 'role_id', 'id');
    }
}
