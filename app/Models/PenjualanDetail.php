<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    protected $table = 'penjualan_detail';

    protected $guarded = [];

    // Tabel ini tidak punya kolom created_at / updated_at
    public $timestamps = false;

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }
}
