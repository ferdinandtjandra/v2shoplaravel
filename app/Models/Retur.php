<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $table = 'retur';

    protected $guarded = [];

    protected $casts = [
        'tanggal_retur' => 'datetime',
        'tanggal_kembali' => 'datetime',
        'sudah_kembali' => 'boolean',
    ];

    // User yang menginput retur
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }
}
