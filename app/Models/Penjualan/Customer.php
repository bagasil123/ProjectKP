<?php

namespace App\Models\Penjualan;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'm_customer';
    protected $fillable = ['kode_customer', 'nama_customer'];
}
