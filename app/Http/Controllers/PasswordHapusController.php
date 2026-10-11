<?php

namespace App\Http\Controllers;

use App\Models\PasswordHapus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordHapusController extends Controller
{
    public function index()
    {
        $daftar = PasswordHapus::orderBy('id', 'desc')->get();

        return view('password_hapus.index', ['daftar' => $daftar]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'keterangan' => 'nullable|max:100',
            'password' => 'required|min:4',
        ]);

        PasswordHapus::create([
            'keterangan' => $request->keterangan,
            'password' => Hash::make($request->password),   // untuk pengecekan
            'password_terlihat' => $request->password,      // otomatis dienkripsi (untuk ditampilkan ke Bos)
        ]);

        return redirect()->route('password_hapus.index')->with('info', 'Password berhasil disimpan.');
    }

    public function destroy($id)
    {
        // findOrFail: kalau datanya sudah tidak ada, tampil halaman 404 (bukan pesan "berhasil")
        PasswordHapus::findOrFail($id)->delete();

        return redirect()->route('password_hapus.index')->with('info', 'Password berhasil dihapus.');
    }
}
