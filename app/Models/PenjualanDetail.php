<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    protected $table = 'penjualan_detail';

    protected $guarded = [];

    // Tabel ini tidak punya kolom created_at / updated_at
    public $timestamps = false;

    // Pilihan garansi: jumlah hari => tulisan yang ditampilkan. 0 = tanpa garansi.
    public const PILIHAN_GARANSI = [
        0 => 'Tanpa garansi',
        1 => '1 hari',
        3 => '3 hari',
        7 => '7 hari',
        14 => '14 hari',
        30 => '30 hari',
        90 => '90 hari',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }
}
