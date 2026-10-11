<?php

namespace App\Http\Controllers;

use App\Models\Pengingat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengingatController extends Controller
{
    // Form tambah + daftar pengingat
    public function index()
    {
        // Yang akan datang di atas (urut tanggal terdekat), yang sudah lewat di bawahnya
        $akanDatang = Pengingat::with('user')->where('tanggal', '>=', date('Y-m-d'))->orderBy('tanggal')->get();
        $sudahLewat = Pengingat::with('user')->where('tanggal', '<', date('Y-m-d'))->orderBy('tanggal', 'desc')->limit(50)->get();

        return view('pengingat.index', [
            'akanDatang' => $akanDatang,
            'sudahLewat' => $sudahLewat,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                // after_or_equal:today = tanggal hari ini atau sesudahnya (pengingat untuk tanggal yang sudah lewat tidak ada gunanya)
                'tanggal' => 'required|date|after_or_equal:today',
                'pesan' => 'required|max:500',
            ],
            ['tanggal.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.']
        );

        Pengingat::create([
            'tanggal' => $request->tanggal,
            'pesan' => $request->pesan,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('pengingat.index')->with('info', 'Pengingat berhasil disimpan.');
    }

    public function destroy($id)
    {
        // findOrFail: kalau datanya sudah tidak ada, tampil halaman 404 (bukan pesan "berhasil")
        Pengingat::findOrFail($id)->delete();

        return redirect()->route('pengingat.index')->with('info', 'Pengingat berhasil dihapus.');
    }
}
