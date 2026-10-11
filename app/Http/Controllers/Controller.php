<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    // Mengubah tulisan angka dari form menjadi angka biasa.
    // Contoh: "1,500,000" menjadi 1500000. Kalau kosong, hasilnya 0.
    protected function angka($nilai)
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $nilai);
    }

    // Memeriksa kotak-kotak angka di form (qty, harga, nominal) SEBELUM disimpan.
    // Kalau ada yang salah, form langsung dikembalikan dengan pesan error (sama seperti validasi biasa).
    //
    // Yang ditolak:
    //   - berisi selain angka, koma, dan titik. Contoh: "-5", "abc", "12abc"
    //     (tanpa pemeriksaan ini, "-5" diam-diam menjadi 5 dan "abc" menjadi 0)
    //   - angkanya lebih besar dari $maksimal (database tidak sanggup menyimpannya)
    //
    // Cara pakai: $this->periksaAngka($request, ['qty' => 'Quantity', 'harga_user' => 'Harga Jual User']);
    protected function periksaAngka(Request $request, array $daftarKotak, $maksimal = 2000000000)
    {
        foreach ($daftarKotak as $namaKotak => $label) {
            $teks = (string) $request->input($namaKotak);

            // Kotak kosong tidak diperiksa di sini (itu urusan aturan "required")
            if ($teks === '') {
                continue;
            }

            if (! preg_match('/^[0-9][0-9.,]*$/', $teks)) {
                throw ValidationException::withMessages([$namaKotak => $label . ' hanya boleh berisi angka.']);
            }

            if ($this->angka($teks) > $maksimal) {
                throw ValidationException::withMessages([$namaKotak => $label . ' terlalu besar (maksimal ' . number_format($maksimal) . ').']);
            }
        }
    }
}
