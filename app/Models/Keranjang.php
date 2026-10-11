<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    protected $table = 'keranjang';

    protected $guarded = [];

    public function barang()
    {
        // withTrashed: barang tetap terbaca walau sudah dihapus setelah masuk keranjang
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }
}
