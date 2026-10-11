<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $table = 'toko';

    // Semua kolom boleh diisi lewat create() / update()
    protected $guarded = [];
}
