<?php

namespace App\Models\Akuntansi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccDtjurnal extends Model
{
    use HasFactory;

    protected $table = 'acc_dt_jurnal';
    protected $fillable = [
        'acc_hd_jurnal_id',
        'acc_kira_id',
        'debet',
        'kredit',
        'catatan',
    ];

    protected $casts = [
        'debet' => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    // Relasi ke Header Jurnal
    public function header(): BelongsTo
    {
        return $this->belongsTo(AccHdjurnal::class, 'acc_hd_jurnal_id');
    }

    // Relasi ke Akun Perkiraan
    public function perkiraan(): BelongsTo
    {
        return $this->belongsTo(AccKira::class, 'acc_kira_id');
    }
}
