<?php

// Identitas toko. Program ini dipakai oleh beberapa toko (V2 Shop, V2 Com, V88, ...).
// Tiap toko punya folder program dan database sendiri, dan cukup mengganti isi file .env:
//   TOKO_NAMA, TOKO_ALAMAT, TOKO_HP, TOKO_KODE
//
// Cara memakai di program: config('toko.nama')
return [
    'nama' => env('TOKO_NAMA', 'V2 Shop'),
    'alamat' => env('TOKO_ALAMAT', ''),
    'hp' => env('TOKO_HP', ''),

    // Awalan kode transaksi penjualan. Contoh: V2S -> V2S-2026/10-10557
    'kode' => env('TOKO_KODE', 'V2S'),
];
