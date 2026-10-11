<?php

namespace App\Http\Controllers;

use App\Models\NotaJatuhTempo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaController extends Controller
{
    // Form input + daftar nota jatuh tempo
    // ?tampil=dihapus -> menampilkan nota yang sudah dihapus, beserta siapa yang menghapus
    public function index(Request $request)
    {
        $tampilDihapus = $request->tampil == 'dihapus';

        if ($tampilDihapus) {
            $daftar = NotaJatuhTempo::onlyTrashed()->with('user', 'penghapus')->orderBy('deleted_at', 'desc')->get();
        } else {
            $daftar = NotaJatuhTempo::with('user')->orderBy('tanggal_jatuh_tempo')->get();
        }

        return view('nota.index', [
            'daftar' => $daftar,
            'tampilDihapus' => $tampilDihapus,
            'jumlahDihapus' => NotaJatuhTempo::onlyTrashed()->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_nota' => 'required|max:100',
            'tanggal_jatuh_tempo' => 'required|date',
            'nominal' => 'required',
            'informasi' => 'nullable|max:1000',
        ]);

        // Nominal: tolak minus, huruf, dan angka yang terlalu besar
        $this->periksaAngka($request, ['nominal' => 'Nominal'], 999999999999);

        NotaJatuhTempo::create([
            'nomor_nota' => $request->nomor_nota,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'informasi' => $request->informasi,
            'nominal' => $this->angka($request->nominal),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('nota.index')->with('info', 'Nota berhasil disimpan.');
    }

    // Mengembalikan nota yang sudah dihapus
    public function kembalikan($id)
    {
        $nota = NotaJatuhTempo::onlyTrashed()->findOrFail($id);

        $nota->restore();           // mengosongkan deleted_at
        $nota->deleted_by = null;
        $nota->save();

        return redirect()->route('nota.index')->with('info', 'Nota ' . $nota->nomor_nota . ' berhasil dikembalikan.');
    }

    public function destroy($id)
    {
        $nota = NotaJatuhTempo::findOrFail($id);

        // Catat siapa yang menghapus, lalu hapus (soft delete: hanya mengisi deleted_at)
        $nota->deleted_by = Auth::id();
        $nota->save();
        $nota->delete();

        return redirect()->route('nota.index')->with('info', 'Nota ' . $nota->nomor_nota . ' berhasil dihapus.');
    }
}
