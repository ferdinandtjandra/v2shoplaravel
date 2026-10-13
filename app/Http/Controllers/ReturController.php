<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Retur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    // Laporan Retur
    public function index()
    {
        $daftar = Retur::with('barang.supplier')->orderBy('id', 'desc')->get();

        return view('retur.index', ['daftar' => $daftar]);
    }

    // Langkah 1: pilih barang yang mau diretur
    public function pilih()
    {
        $daftar = Barang::with('kategori', 'supplier')->where('qty', '>', 0)->orderBy('id', 'desc')->get();

        return view('retur.pilih', ['daftar' => $daftar]);
    }

    // Langkah 2: form retur untuk barang yang dipilih
    public function create($barangId)
    {
        $barang = Barang::with('supplier')->findOrFail($barangId);

        // Riwayat retur barang ini (siapa yang input, kapan, sudah kembali atau belum)
        $riwayat = Retur::with('user')->where('barang_id', $barang->id)->orderBy('id', 'desc')->get();

        return view('retur.create', [
            'barang' => $barang,
            'riwayat' => $riwayat,
        ]);
    }

    // Menyimpan retur dan mengurangi stok
    public function store(Request $request, $barangId)
    {
        $request->validate([
            'qty' => 'required|integer|min:1|max:1000000',
            'keterangan' => 'required|max:1000',
            'disetujui_oleh' => 'required|max:100',   // nama yang menyetujui retur, diketik bebas
        ]);

        DB::beginTransaction();

        $barang = Barang::lockForUpdate()->findOrFail($barangId);

        if ($request->qty > $barang->qty) {
            DB::rollBack();

            return back()->with('error', 'Quantity retur tidak boleh lebih dari stok (' . $barang->qty . ').')->withInput();
        }

        $retur = Retur::create([
            'barang_id' => $barang->id,
            'qty' => $request->qty,
            'keterangan' => $request->keterangan,
            'disetujui_oleh' => $request->disetujui_oleh,
            'tanggal_retur' => now(),
            'user_id' => Auth::id(),
        ]);

        // Kode retur dibuat dari ID, contoh: RTN001
        $retur->kode = 'RTN' . str_pad($retur->id, 3, '0', STR_PAD_LEFT);
        $retur->save();

        // Barang yang diretur keluar dari stok + dicatat di kartu stok
        $barang->ubahStok(-$request->qty, 'retur', 'Retur ' . $retur->kode . ': ' . $request->keterangan);

        DB::commit();

        return redirect()->route('retur.pilih')->with('info', 'Retur ' . $retur->kode . ' berhasil disimpan.');
    }

    // Barang retur sudah kembali dari supplier: stok ditambah lagi
    public function kembali($id)
    {
        DB::beginTransaction();

        $retur = Retur::lockForUpdate()->findOrFail($id);

        if ($retur->sudah_kembali) {
            DB::rollBack();

            return redirect()->route('retur.index')->with('error', 'Retur ini sudah pernah dikembalikan.');
        }

        $retur->sudah_kembali = true;
        $retur->tanggal_kembali = now();
        $retur->save();

        $barang = Barang::withTrashed()->lockForUpdate()->find($retur->barang_id);
        $barang->ubahStok($retur->qty, 'retur_kembali', 'Retur ' . $retur->kode . ' kembali dari supplier');

        DB::commit();

        return redirect()->route('retur.index')->with('info', 'Barang sudah dikembalikan ke stok.');
    }
}
