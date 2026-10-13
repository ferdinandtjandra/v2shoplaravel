<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    protected $table = 'service_item';

    protected $guarded = [];

    // Jenis => tulisan yang ditampilkan
    public const DAFTAR_JENIS = [
        'stok' => 'Sparepart (stok toko)',
        'luar' => 'Sparepart (dari luar)',
        'jasa' => 'Jasa',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
