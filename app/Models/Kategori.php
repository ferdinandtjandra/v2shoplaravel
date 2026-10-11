<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';

    // Semua kolom boleh diisi lewat create() / update()
    protected $guarded = [];
}
