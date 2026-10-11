<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Keranjang;
use App\Models\PasswordHapus;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\TemplateStruk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    // Halaman Input Penjualan: pilih barang + isi keranjang + checkout
    public function index()
    {
        // Daftar barang TIDAK dimuat di sini. Kotak pilih barang mencarinya ke server saat kasir mengetik
        // (lihat BarangController@cari). Yang diambil hanya barang yang terakhir dipilih, supaya pilihannya
        // tidak hilang kalau form dikembalikan karena ada isian yang salah.
        $barangLama = null;
        if (old('barang_id')) {
            $barangLama = Barang::find(old('barang_id'));
        }

        // Stok dan harga barang itu, untuk dibaca JavaScript di halaman (null = tidak ada)
        $dataBarangLama = null;
        if ($barangLama) {
            $dataBarangLama = [
                'qty' => $barangLama->qty,
                'harga_member' => $barangLama->harga_member,
                'harga_user' => $barangLama->harga_user,
                'harga_ongkir' => $barangLama->harga_ongkir,
            ];
        }

        // Keranjang milik user yang sedang login saja
        $keranjang = Keranjang::with('barang')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        $daftarSales = User::where('role', 'sales')->where('aktif', 1)->orderBy('name')->get();

        return view('penjualan.index', [
            'barangLama' => $barangLama,
            'dataBarangLama' => $dataBarangLama,
            'keranjang' => $keranjang,
            'total' => $keranjang->sum('subtotal'),
            'daftarSales' => $daftarSales,
        ]);
    }

    // Menambah barang ke keranjang
    public function tambah(Request $request)
    {
        $request->validate([
            // exists:barang,id,deleted_at,NULL = barangnya harus ada DAN belum dihapus
            'barang_id' => 'required|exists:barang,id,deleted_at,NULL',
            'qty' => 'required|integer|min:1|max:100000',
            // garansi harus salah satu dari pilihan yang tersedia (0, 1, 3, 7, ... hari)
            'garansi_hari' => 'nullable|in:' . implode(',', array_keys(PenjualanDetail::PILIHAN_GARANSI)),
        ]);

        $barang = Barang::findOrFail($request->barang_id);
        $qty = (int) $request->qty;

        // 1. Tentukan harga jual.
        //    Bos boleh mengetik harga custom. User lain wajib memilih salah satu harga dari database.
        $harga = 0;
        if (Auth::user()->isBos()) {
            // Harga custom: tolak minus, huruf, dan angka yang terlalu besar
            $this->periksaAngka($request, ['harga_custom' => 'Harga Custom']);

            $harga = $this->angka($request->harga_custom);
        }

        if ($harga == 0) {
            $harga = (int) $request->harga_pilihan;
            $hargaYangBoleh = [$barang->harga_member, $barang->harga_user, $barang->harga_ongkir];

            if (! in_array($harga, $hargaYangBoleh)) {
                return back()->with('error', 'Silakan pilih harga jual.')->withInput();
            }
        }

        // 2. Harga jual tidak boleh di bawah harga modal
        if ($harga < $barang->harga_distributor) {
            return back()->with('error', 'Harga Transaksi tidak boleh kurang dari Harga Distributor.')->withInput();
        }

        // 3. Qty tidak boleh melebihi stok (dihitung bersama yang sudah ada di keranjang)
        $sudahDiKeranjang = Keranjang::where('user_id', Auth::id())->where('barang_id', $barang->id)->sum('qty');
        if ($sudahDiKeranjang + $qty > $barang->qty) {
            return back()->with('error', 'Stok ' . $barang->nama . ' tidak cukup. Stok tersedia: ' . $barang->qty)->withInput();
        }

        // 4. Totalnya tidak boleh melebihi batas angka yang bisa disimpan database
        if ($harga * $qty > 2000000000) {
            return back()->with('error', 'Total harga terlalu besar. Kurangi qty atau periksa lagi harganya.')->withInput();
        }

        // 5. Simpan ke keranjang
        Keranjang::create([
            'user_id' => Auth::id(),
            'barang_id' => $barang->id,
            'qty' => $qty,
            'harga_satuan' => $harga,
            'subtotal' => $harga * $qty,
            'keuntungan' => ($harga - $barang->harga_distributor) * $qty,
            'garansi_hari' => (int) $request->garansi_hari,
        ]);

        return redirect()->route('penjualan.index');
    }

    // Menghapus 1 baris keranjang (wajib isi password hapus)
    public function hapusItem(Request $request, $id)
    {
        if (! PasswordHapus::cocok($request->password_hapus)) {
            return redirect()->route('penjualan.index')->with('error', 'Password Salah, silahkan mencoba kembali.');
        }

        // Hanya boleh menghapus item di keranjang milik sendiri
        $item = Keranjang::where('user_id', Auth::id())->where('id', $id)->first();

        if ($item == null) {
            return redirect()->route('penjualan.index')->with('error', 'Item tidak ditemukan di keranjang Anda.');
        }

        $item->delete();

        return redirect()->route('penjualan.index')->with('info', 'Item berhasil dihapus.');
    }

    // Membatalkan transaksi = mengosongkan keranjang (wajib isi password hapus)
    public function batal(Request $request)
    {
        if (! PasswordHapus::cocok($request->password_hapus)) {
            return redirect()->route('penjualan.index')->with('error', 'Password Salah, silahkan mencoba kembali.');
        }

        Keranjang::where('user_id', Auth::id())->delete();

        return redirect()->route('penjualan.index')->with('info', 'Transaksi Berhasil Dibatalkan.');
    }

    // Checkout: memindahkan isi keranjang menjadi transaksi penjualan + mengurangi stok
    public function checkout(Request $request)
    {
        $request->validate(
            // exists:users,id,role,sales,aktif,1 = harus pengguna level Sales yang masih aktif
            ['sales_id' => 'required|exists:users,id,role,sales,aktif,1'],
            ['sales_id.required' => 'Pilih Sales.', 'sales_id.exists' => 'Sales yang dipilih tidak tersedia.']
        );

        $keranjang = Keranjang::where('user_id', Auth::id())->get();

        if ($keranjang->count() == 0) {
            return redirect()->route('penjualan.index')->with('error', 'Lakukan Transaksi Terlebih dahulu.');
        }

        // Diskon (potongan rupiah untuk seluruh transaksi). Hanya Bos yang boleh memberi diskon,
        // sama seperti Harga Custom. Diskon dari staff diabaikan.
        $totalBarang = $keranjang->sum('subtotal');
        $totalUntung = $keranjang->sum('keuntungan');
        $diskon = 0;

        if (Auth::user()->isBos()) {
            $this->periksaAngka($request, ['diskon' => 'Diskon']);
            $diskon = $this->angka($request->diskon);
        }

        // Diskon tidak boleh melebihi keuntungan, supaya tidak menjual di bawah modal
        if ($diskon > $totalUntung) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Diskon terlalu besar. Maksimal Rp ' . number_format($totalUntung) . ' supaya tidak menjual di bawah modal.')
                ->withInput();
        }

        // PENTING: semua pemeriksaan yang bisa menolak transaksi (seperti diskon di atas) harus dilakukan
        // SEBELUM DB::beginTransaction. Kalau menolak di tengah transaksi tanpa DB::rollBack, pesan errornya ikut hilang.

        // DB::beginTransaction: semua perubahan di bawah ini dianggap satu paket.
        // Kalau ada yang gagal di tengah jalan, semuanya dibatalkan (rollBack),
        // jadi tidak ada penjualan yang tersimpan setengah-setengah.
        DB::beginTransaction();

        // Template struk yang berlaku saat ini. Disalin ke penjualan, supaya struk transaksi ini
        // tidak berubah walaupun Template Struk diedit di kemudian hari.
        $template = TemplateStruk::ambil();

        // 1. Simpan data penjualan
        $penjualan = Penjualan::create([
            'struk_header' => $template->header,
            'struk_catatan' => $template->catatan,
            'struk_penutup' => $template->penutup,
            'tanggal' => now(),
            'total' => $totalBarang - $diskon,          // uang yang dibayar pembeli
            'diskon' => $diskon,
            'keuntungan' => $totalUntung - $diskon,     // diskon mengurangi keuntungan
            'keterangan' => $request->keterangan,
            'sales_id' => $request->sales_id,
            'kasir_id' => Auth::id(),
        ]);

        // Kode transaksi dibuat dari ID supaya tidak pernah kembar. Contoh: V2S-2026/10-10557
        // Awalan "V2S" diambil dari TOKO_KODE di file .env
        $penjualan->kode_transaksi = config('toko.kode') . '-' . date('Y/m') . '-' . str_pad($penjualan->id, 5, '0', STR_PAD_LEFT);
        $penjualan->save();

        // 2. Pindahkan isi keranjang ke penjualan_detail dan kurangi stok
        foreach ($keranjang as $item) {
            // lockForUpdate: mengunci baris barang ini sampai transaksi selesai,
            // supaya 2 kasir tidak menjual stok yang sama pada saat bersamaan.
            $barang = Barang::withTrashed()->lockForUpdate()->find($item->barang_id);

            // Barangnya bisa saja dihapus Bos setelah masuk keranjang
            if ($barang->trashed()) {
                DB::rollBack();

                return redirect()->route('penjualan.index')
                    ->with('error', 'Barang ' . $barang->nama . ' sudah dihapus dari daftar barang. Hapus dulu dari keranjang.');
            }

            if ($barang->qty < $item->qty) {
                DB::rollBack();

                return redirect()->route('penjualan.index')
                    ->with('error', 'Stok ' . $barang->nama . ' tidak cukup. Stok tersedia: ' . $barang->qty);
            }

            PenjualanDetail::create([
                'penjualan_id' => $penjualan->id,
                'barang_id' => $barang->id,
                'nama_barang' => $barang->nama,
                'qty' => $item->qty,
                'harga_satuan' => $item->harga_satuan,
                'subtotal' => $item->subtotal,
                'keuntungan' => $item->keuntungan,
                'garansi_hari' => $item->garansi_hari,
            ]);

            // Kurangi stok + catat di kartu stok
            $barang->ubahStok(-$item->qty, 'jual', 'Penjualan ' . $penjualan->kode_transaksi);
        }

        // 3. Kosongkan keranjang
        Keranjang::where('user_id', Auth::id())->delete();

        DB::commit();

        // Setelah checkout langsung ke halaman struk (di dalam tampilan admin, ada tombol Print)
        return redirect()->route('penjualan.struk', $penjualan->id)
            ->with('info', 'Transaksi ' . $penjualan->kode_transaksi . ' berhasil disimpan.');
    }

    // Membatalkan (void) transaksi yang sudah di-checkout. Hanya Bos.
    // Transaksinya tidak dihapus, hanya ditandai "dibatalkan", dan stok barangnya dikembalikan.
    public function batalkan(Request $request, $id)
    {
        $request->validate(
            ['alasan_batal' => 'required|max:500'],
            ['alasan_batal.required' => 'Alasan pembatalan wajib diisi.']
        );

        DB::beginTransaction();

        $penjualan = Penjualan::with('detail')->lockForUpdate()->findOrFail($id);

        if ($penjualan->sudahDibatalkan()) {
            DB::rollBack();

            return redirect()->route('penjualan.struk', $penjualan->id)->with('error', 'Transaksi ini sudah pernah dibatalkan.');
        }

        // 1. Kembalikan stok tiap barang + catat di kartu stok
        foreach ($penjualan->detail as $item) {
            // Barang dari data lama bisa saja sudah tidak ada (barang_id kosong), yang itu dilewati
            if ($item->barang_id == null) {
                continue;
            }

            $barang = Barang::withTrashed()->lockForUpdate()->find($item->barang_id);
            $barang->ubahStok($item->qty, 'batal_jual', 'Batal penjualan ' . $penjualan->kode_transaksi);
        }

        // 2. Tandai transaksinya sebagai dibatalkan
        $penjualan->dibatalkan_at = now();
        $penjualan->dibatalkan_oleh = Auth::id();
        $penjualan->alasan_batal = $request->alasan_batal;
        $penjualan->save();

        DB::commit();

        $pesan = 'Transaksi ' . $penjualan->kode_transaksi . ' dibatalkan. Stok barang sudah dikembalikan.';

        // Kalau dibatalkan dari Laporan Penjualan, kembali ke report dengan periode yang sama
        if ($request->bulan != '') {
            return redirect()->route('laporan.penjualan', $request->only('bulan', 'tahun', 'tanggal', 'sales_id'))->with('info', $pesan);
        }

        return redirect()->route('penjualan.struk', $penjualan->id)->with('info', $pesan);
    }

    // Struk / nota untuk dicetak
    public function struk($id)
    {
        $penjualan = Penjualan::with('detail', 'sales', 'kasir', 'pembatal')->findOrFail($id);

        // Header dan footer diambil dari yang tersimpan di penjualan ini (template saat transaksi dibuat),
        // BUKAN dari Template Struk yang sekarang.
        $template = (object) [
            'header' => $penjualan->struk_header,
            'catatan' => $penjualan->struk_catatan,
            'penutup' => $penjualan->struk_penutup,
        ];

        return view('penjualan.struk', [
            'penjualan' => $penjualan,
            'template' => $template,
        ]);
    }
}
