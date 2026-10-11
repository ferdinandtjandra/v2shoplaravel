<?php

namespace App\Http\Controllers;

use App\Models\CatatanLogin;

class CatatanLoginController extends Controller
{
    // Daftar 1000 catatan login terakhir (hanya Bos)
    public function index()
    {
        $daftar = CatatanLogin::with('user')->orderBy('id', 'desc')->limit(1000)->get();

        return view('catatan_login.index', ['daftar' => $daftar]);
    }
}
