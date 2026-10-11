<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangRiwayat extends Model
{
    protected $table = 'barang_riwayat';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
