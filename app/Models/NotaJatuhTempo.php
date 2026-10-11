<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotaJatuhTempo extends Model
{
    // SoftDeletes: saat dihapus, data tidak hilang. Hanya kolom deleted_at yang diisi.
    use SoftDeletes;

    protected $table = 'nota_jatuh_tempo';

    protected $guarded = [];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
    ];

    // User yang menginput nota
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // User yang menghapus nota
    public function penghapus()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
