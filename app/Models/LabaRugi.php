<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabaRugi extends Model
{
    protected $table = 'laba_rugi';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
