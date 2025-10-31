<?php

namespace App\Models\keamanan;

use App\Models\keamanan\RoleMenu;
use Illuminate\Database\Eloquent\Model;

// app/Models/keamanan/RightAccess.php
// ...
class RightAccess extends Model
{
    protected $table = 'mn_rightacces';
    public $timestamps = false;
    protected $primaryKey = ['AC_USER', 'AC_MAINMENU'];
    public $incrementing = false;
    protected $keyType = 'array'; // Karena AC_USER (string) dan AC_MAINMENU (int)
    protected $fillable = ['AC_USER', 'AC_MAINMENU', 'AC_AD', 'AC_ED', 'AC_DE', 'AC_USERID', 'AC_LASTUPDATE'];

    // Relasi ke kombinasi Role-Menu
    public function roleMenuCombination()
    {
        return $this->belongsTo(RoleMenu::class, 'AC_MAINMENU', 'id');
    }
}