<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierRiwayat extends Model
{
    protected $table = 'supplier_riwayat';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
