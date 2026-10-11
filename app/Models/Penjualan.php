<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'datetime',
        'dibatalkan_at' => 'datetime',
    ];

    // Daftar barang di dalam transaksi ini
    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    // User yang membatalkan transaksi ini
    public function pembatal()
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    // true kalau transaksi ini sudah dibatalkan
    public function sudahDibatalkan()
    {
        return $this->dibatalkan_at != null;
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }
}
