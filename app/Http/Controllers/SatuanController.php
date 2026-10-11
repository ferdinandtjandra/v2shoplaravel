<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    // Halaman master satuan: form tambah + daftar
    public function index()
    {
        $daftar = Satuan::orderBy('id', 'desc')->get();

        return view('satuan.index', ['daftar' => $daftar]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|max:20',
            'nama' => 'required|max:100',
        ]);

        Satuan::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
        ]);

        return redirect()->route('satuan.index')->with('info', 'Data berhasil disimpan.');
    }

    public function edit($id)
    {
        $satuan = Satuan::findOrFail($id);

        return view('satuan.edit', ['satuan' => $satuan]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => 'required|max:20',
            'nama' => 'required|max:100',
        ]);

        $satuan = Satuan::findOrFail($id);
        $satuan->kode = $request->kode;
        $satuan->nama = $request->nama;
        $satuan->save();

        return redirect()->route('satuan.index')->with('info', 'Data berhasil diubah.');
    }

    public function destroy($id)
    {
        // Tidak boleh dihapus kalau masih dipakai oleh barang
        $dipakai = Barang::withTrashed()->where('satuan_id', $id)->count();
        if ($dipakai > 0) {
            return redirect()->route('satuan.index')->with('error', 'Tidak bisa dihapus, masih dipakai oleh ' . $dipakai . ' barang.');
        }

        // findOrFail: kalau datanya sudah tidak ada, tampil halaman 404 (bukan pesan "berhasil")
        Satuan::findOrFail($id)->delete();

        return redirect()->route('satuan.index')->with('info', 'Data berhasil dihapus.');
    }
}
