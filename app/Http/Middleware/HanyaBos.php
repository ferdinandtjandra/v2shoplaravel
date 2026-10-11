<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Middleware ini dipasang di route yang hanya boleh dibuka oleh Bos.
// User lain (karyawan / sales) akan dikembalikan ke dashboard.
class HanyaBos
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::user()->isBos()) {
            return redirect()->route('dashboard')->with('error', 'Halaman tersebut hanya untuk Bos.');
        }

        return $next($request);
    }
}
