<?php

namespace App\Http\Controllers;

use App\Models\TemplateStruk;
use Illuminate\Http\Request;

class TemplateStrukController extends Controller
{
    // Halaman edit template struk: form di kiri, contoh struk di kanan
    public function edit()
    {
        // Data penjualan contoh, hanya untuk mengisi preview struk
        $contoh = (object) [
            'tanggal' => now(),
            'kode_transaksi' => config('toko.kode') . '-' . date('Y/m') . '-00001',
            'total' => 450000,
            'detail' => [
                (object) ['nama_barang' => 'Contoh Barang A', 'qty' => 1, 'subtotal' => 300000],
                (object) ['nama_barang' => 'Contoh Barang B', 'qty' => 2, 'subtotal' => 150000],
            ],
        ];

        return view('template_struk.edit', [
            'template' => TemplateStruk::ambil(),
            'contoh' => $contoh,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'header' => 'required|max:500',
            'catatan' => 'nullable|max:1000',
            'penutup' => 'nullable|max:100',
        ]);

        $template = TemplateStruk::ambil();
        $template->header = $request->header;
        $template->catatan = $request->catatan;
        $template->penutup = $request->penutup;
        $template->save();

        return redirect()->route('template_struk.edit')->with('info', 'Template struk berhasil disimpan.');
    }
}
