<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokPenyesuaian extends Model
{
    protected $table = 'stok_penyesuaian';

    protected $guarded = [];

    // Pilihan alasan di form Penyesuaian Stok
    public const DAFTAR_ALASAN = [
        'Pindah ke toko lain',
        'Terima dari toko lain',
        'Rusak',
        'Hilang',
        'Koreksi hitung stok',
        'Lainnya',
    ];

    // Alasan yang wajib memilih toko (toko tujuan / toko asal)
    public const ALASAN_PERLU_TOKO = [
        'Pindah ke toko lain',
        'Terima dari toko lain',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
