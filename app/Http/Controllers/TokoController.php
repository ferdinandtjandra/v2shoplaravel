<?php

namespace App\Http\Controllers;

use App\Models\StokPenyesuaian;
use App\Models\Toko;
use Illuminate\Http\Request;

class TokoController extends Controller
{
    // Halaman master toko: form tambah + daftar
    public function index()
    {
        $daftar = Toko::orderBy('nama')->get();

        return view('toko.index', ['daftar' => $daftar]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100|unique:toko,nama',
        ]);

        Toko::create(['nama' => $request->nama]);

        return redirect()->route('toko.index')->with('info', 'Toko berhasil disimpan.');
    }

    public function edit($id)
    {
        $toko = Toko::findOrFail($id);

        return view('toko.edit', ['toko' => $toko]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:100|unique:toko,nama,' . $id,
        ]);

        $toko = Toko::findOrFail($id);
        $toko->nama = $request->nama;
        $toko->save();

        return redirect()->route('toko.index')->with('info', 'Toko berhasil diubah.');
    }

    public function destroy($id)
    {
        // Tidak boleh dihapus kalau sudah dipakai di riwayat Penyesuaian Stok
        $dipakai = StokPenyesuaian::where('toko_id', $id)->count();
        if ($dipakai > 0) {
            return redirect()->route('toko.index')->with('error', 'Tidak bisa dihapus, sudah dipakai di ' . $dipakai . ' riwayat Penyesuaian Stok.');
        }

        // findOrFail: kalau datanya sudah tidak ada, tampil halaman 404 (bukan pesan "berhasil")
        Toko::findOrFail($id)->delete();

        return redirect()->route('toko.index')->with('info', 'Toko berhasil dihapus.');
    }
}
