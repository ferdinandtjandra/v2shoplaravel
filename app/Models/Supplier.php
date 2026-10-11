<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';

    // Semua kolom boleh diisi lewat create() / update()
    protected $guarded = [];
}
