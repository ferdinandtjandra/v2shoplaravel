<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $table = 'satuan';

    // Semua kolom boleh diisi lewat create() / update()
    protected $guarded = [];
}
