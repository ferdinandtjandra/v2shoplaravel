<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StokPenyesuaian;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdjustStokController extends Controller
{
    // Halaman Penyesuaian Stok: form di kiri, riwayat di kanan.
    // Form bisa dipakai semua user. Riwayat hanya bisa dilihat oleh Bos.
    public function index()
    {
        // Barang yang stoknya 0 juga ditampilkan, karena bisa saja mau ditambah
        $barang = Barang::orderBy('nama')->get();

        // 500 riwayat terakhir (hanya diambil kalau yang login adalah Bos)
        $riwayat = [];
        if (Auth::user()->isBos()) {
            $riwayat = StokPenyesuaian::with('barang', 'user', 'toko')->orderBy('id', 'desc')->limit(500)->get();
        }

        return view('adjust_stok.index', [
            'barang' => $barang,
            'riwayat' => $riwayat,
            'daftarToko' => Toko::orderBy('nama')->get(),
        ]);
    }

    // Menyimpan penambahan / pengurangan stok
    public function store(Request $request)
    {
        $request->validate([
            // exists:barang,id,deleted_at,NULL = barangnya harus ada DAN belum dihapus
            'barang_id' => 'required|exists:barang,id,deleted_at,NULL',
            'jenis' => 'required|in:tambah,kurang',
            'qty' => 'required|integer|min:1|max:1000000',
            'alasan' => 'required|in:' . implode(',', StokPenyesuaian::DAFTAR_ALASAN),
            'keterangan' => 'required|max:500',
            'toko_id' => 'nullable|exists:toko,id',
        ]);

        // Kalau alasannya pindah ke / terima dari toko lain, tokonya wajib dipilih.
        // Untuk alasan lain, toko dikosongkan.
        $tokoId = null;
        if (in_array($request->alasan, StokPenyesuaian::ALASAN_PERLU_TOKO)) {
            if ($request->toko_id == '') {
                return back()->with('error', 'Pilih tokonya dulu.')->withInput();
            }

            $tokoId = $request->toko_id;
        }

        $qty = (int) $request->qty;

        DB::beginTransaction();

        // lockForUpdate: mengunci baris barang ini sampai selesai,
        // supaya tidak bentrok dengan penjualan yang terjadi di saat yang sama.
        $barang = Barang::lockForUpdate()->findOrFail($request->barang_id);

        $stokSebelum = $barang->qty;

        if ($request->jenis == 'tambah') {
            $stokSesudah = $stokSebelum + $qty;
        } else {
            $stokSesudah = $stokSebelum - $qty;
        }

        // Stok tidak boleh menjadi minus
        if ($stokSesudah < 0) {
            DB::rollBack();

            return back()->with('error', 'Stok ' . $barang->nama . ' hanya ' . $stokSebelum . ', tidak bisa dikurangi ' . $qty . '.')->withInput();
        }

        // 1. Ubah stok barang + catat di kartu stok
        $perubahan = $request->jenis == 'tambah' ? $qty : -$qty;
        $barang->ubahStok($perubahan, 'adjust', $request->alasan . ': ' . $request->keterangan);

        // 2. Catat riwayatnya
        StokPenyesuaian::create([
            'barang_id' => $barang->id,
            'user_id' => Auth::id(),
            'jenis' => $request->jenis,
            'qty' => $qty,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'alasan' => $request->alasan,
            'toko_id' => $tokoId,
            'keterangan' => $request->keterangan,
        ]);

        DB::commit();

        return redirect()->route('adjust_stok.index')
            ->with('info', 'Stok ' . $barang->nama . ' berubah dari ' . $stokSebelum . ' menjadi ' . $stokSesudah . '.');
    }
}
