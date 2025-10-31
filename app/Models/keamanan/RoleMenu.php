<?php

namespace App\Models\keamanan; // Sesuaikan namespace jika di App/Models/keamanan

use Illuminate\Database\Eloquent\Relations\Pivot; // Penting: extend Pivot
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleMenu extends Pivot // extend Pivot
{
    use HasFactory;

    protected $table = 'role_menu';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = ['role_id', 'menu_id'];

    // Relasi ke Role
    public function role()
    {
        return $this->belongsTo(Role::class); // Asumsi role_id ada di sini dan primary key Role adalah 'id'
    }

    // Relasi ke Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class); // Asumsi menu_id ada di sini dan primary key Menu adalah 'id'
    }

    // Relasi ke RightAccess (opsional, jika perlu melihat siapa saja yang punya hak akses ke kombinasi ini)
    public function rightAccesses()
    {
        return $this->hasMany(RightAccess::class, 'AC_MAINMENU', 'id');
    }
}