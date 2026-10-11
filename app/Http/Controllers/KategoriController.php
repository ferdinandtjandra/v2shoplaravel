<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    // Halaman master kategori: form tambah + daftar
    public function index()
    {
        $daftar = Kategori::orderBy('id', 'desc')->get();

        return view('kategori.index', ['daftar' => $daftar]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|max:20',
            'nama' => 'required|max:100',
        ]);

        Kategori::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
        ]);

        return redirect()->route('kategori.index')->with('info', 'Data berhasil disimpan.');
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);

        return view('kategori.edit', ['kategori' => $kategori]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => 'required|max:20',
            'nama' => 'required|max:100',
        ]);

        $kategori = Kategori::findOrFail($id);
        $kategori->kode = $request->kode;
        $kategori->nama = $request->nama;
        $kategori->save();

        return redirect()->route('kategori.index')->with('info', 'Data berhasil diubah.');
    }

    public function destroy($id)
    {
        // Tidak boleh dihapus kalau masih dipakai oleh barang
        $dipakai = Barang::withTrashed()->where('kategori_id', $id)->count();
        if ($dipakai > 0) {
            return redirect()->route('kategori.index')->with('error', 'Tidak bisa dihapus, masih dipakai oleh ' . $dipakai . ' barang.');
        }

        // findOrFail: kalau datanya sudah tidak ada, tampil halaman 404 (bukan pesan "berhasil")
        Kategori::findOrFail($id)->delete();

        return redirect()->route('kategori.index')->with('info', 'Data berhasil dihapus.');
    }
}
