<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PenjualanDetail;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\TemplateStruk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// SERVICE = perbaikan HP pelanggan.
//
// Alurnya:
//   1. Terima HP        -> status "diterima"   -> cetak SURAT TANDA TERIMA
//   2. Kerjakan         -> status "dikerjakan" -> tambah sparepart (dari stok / dari luar) dan jasa
//   3. Tandai selesai   -> status "selesai"    -> pelanggan dihubungi (di luar program)
//   4. HP diambil+bayar -> status "diambil"    -> cetak NOTA SERVICE
//   Kapan saja sebelum diambil bisa dibatalkan -> status "batal" (sparepart dari stok kembali ke stok)
//
// Semua level (Bos dan staff) bisa memakai menu ini. Angka modal dan keuntungan hanya tampil untuk Bos.
class ServiceController extends Controller
{
    // Daftar service. ?status=... untuk memilih kelompok, ?cari=... untuk mencari nomor / nama / no HP.
    public function index(Request $request)
    {
        // Kelompok yang bisa dipilih di halaman
        $kelompok = is_string($request->status) ? $request->status : 'aktif';

        $query = Service::query();

        if ($kelompok == 'selesai') {
            $query->where('status', 'selesai');
        } elseif ($kelompok == 'diambil') {
            $query->where('status', 'diambil');
        } elseif ($kelompok == 'batal') {
            $query->where('status', 'batal');
        } else {
            $kelompok = 'aktif';
            $query->whereIn('status', ['diterima', 'dikerjakan']); // yang sedang dikerjakan
        }

        // Pencarian: nomor service, nama pelanggan, atau nomor HP pelanggan
        $cari = is_string($request->cari) ? trim($request->cari) : '';
        if ($cari != '') {
            $query->where(function ($q) use ($cari) {
                $q->where('kode', 'like', '%' . $cari . '%')
                    ->orWhere('nama_pelanggan', 'like', '%' . $cari . '%')
                    ->orWhere('hp_pelanggan', 'like', '%' . $cari . '%');
            });
        }

        return view('service.index', [
            'daftar' => $query->orderBy('id', 'desc')->paginate(25)->withQueryString(),
            'kelompok' => $kelompok,
            'cari' => $cari,
            // Jumlah tiap kelompok, untuk ditampilkan di tombolnya
            'jumlahAktif' => Service::whereIn('status', ['diterima', 'dikerjakan'])->count(),
            'jumlahSelesai' => Service::where('status', 'selesai')->count(),
        ]);
    }

    // Form terima service baru
    public function create()
    {
        return view('service.form', [
            'service' => new Service(),
        ]);
    }

    // Menyimpan service baru
    public function store(Request $request)
    {
        $this->validasiDataService($request);

        // Header dan catatan yang berlaku saat ini disalin ke service,
        // supaya tanda terima dan notanya tidak berubah kalau Template Struk diedit di kemudian hari.
        $template = TemplateStruk::ambil();

        $service = new Service();
        $this->isiDariForm($service, $request);
        $service->status = 'diterima';
        $service->tanggal_terima = now();
        $service->diterima_oleh = Auth::id();
        $service->struk_header = $template->header;
        $service->struk_catatan_tt = $template->catatan_tanda_terima;
        $service->struk_catatan_nota = $template->catatan_nota_service;
        $service->struk_penutup = $template->penutup;
        $service->save();

        // Nomor service dibuat dari ID supaya tidak pernah kembar. Contoh: SRV-2026/10-00001
        $service->kode = 'SRV-' . date('Y/m') . '-' . str_pad($service->id, 5, '0', STR_PAD_LEFT);
        $service->save();

        return redirect()->route('service.show', $service->id)
            ->with('info', 'Service ' . $service->kode . ' berhasil disimpan. Silakan cetak tanda terimanya.');
    }

    // Halaman kerja 1 service: data HP, sparepart, jasa, dan tombol-tombol aksinya
    public function show($id)
    {
        $service = Service::with('item.user', 'penerima', 'penyerah', 'pembatal')->findOrFail($id);

        return view('service.show', ['service' => $service]);
    }

    // Form ubah data penerimaan (misalnya mengisi kerusakan setelah dicek)
    public function edit($id)
    {
        $service = Service::findOrFail($id);

        if (! $service->masihBisaDiubah()) {
            return redirect()->route('service.show', $service->id)->with('error', 'Service ini sudah ' . strtolower(Service::DAFTAR_STATUS[$service->status]) . ', datanya tidak bisa diubah.');
        }

        return view('service.form', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        if (! $service->masihBisaDiubah()) {
            return redirect()->route('service.show', $service->id)->with('error', 'Service ini sudah tidak bisa diubah.');
        }

        $this->validasiDataService($request);

        $this->isiDariForm($service, $request);
        $service->save();

        return redirect()->route('service.show', $service->id)->with('info', 'Data service berhasil diubah.');
    }

    // ------------------------------------------------------------------
    // SPAREPART DAN JASA
    // ------------------------------------------------------------------

    // Sparepart dari STOK TOKO. Harganya selalu "Harga Jual + Ongkir", dan stok langsung berkurang.
    public function tambahStok(Request $request, $id)
    {
        $request->validate([
            // exists:barang,id,deleted_at,NULL = barangnya harus ada DAN belum dihapus
            'barang_id' => 'required|exists:barang,id,deleted_at,NULL',
            'qty' => 'required|integer|min:1|max:100000',
        ]);

        $qty = (int) $request->qty;

        DB::beginTransaction();

        $service = Service::lockForUpdate()->findOrFail($id);

        if (! $service->masihBisaDiubah()) {
            DB::rollBack();

            return redirect()->route('service.show', $id)->with('error', 'Service ini sudah tidak bisa diubah.');
        }

        // lockForUpdate: mengunci baris barang sampai selesai, supaya tidak bentrok dengan penjualan
        $barang = Barang::lockForUpdate()->findOrFail($request->barang_id);

        if ($qty > $barang->qty) {
            DB::rollBack();

            return redirect()->route('service.show', $id)->with('error', 'Stok ' . $barang->nama . ' tidak cukup. Stok tersedia: ' . $barang->qty);
        }

        ServiceItem::create([
            'service_id' => $service->id,
            'jenis' => 'stok',
            'barang_id' => $barang->id,
            'nama' => $barang->nama,
            'qty' => $qty,
            'harga_satuan' => $barang->harga_ongkir,          // selalu Harga Jual + Ongkir
            'subtotal' => $barang->harga_ongkir * $qty,
            'modal_satuan' => $barang->harga_distributor,
            'user_id' => Auth::id(),
        ]);

        // Sparepartnya sudah terpakai, jadi stok langsung dikurangi + dicatat di kartu stok
        $barang->ubahStok(-$qty, 'service', 'Dipakai service ' . $service->kode);

        $this->setelahItemBerubah($service);

        DB::commit();

        return redirect()->route('service.show', $id)->with('info', 'Sparepart ' . $barang->nama . ' ditambahkan. Stok berkurang ' . $qty . '.');
    }

    // Sparepart dari LUAR (dibeli di tempat lain): nama dan harga diketik sendiri. Tidak mengubah stok.
    public function tambahLuar(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:150',
            'qty' => 'required|integer|min:1|max:100000',
            'harga' => 'required',
        ]);

        // harga = harga ke pelanggan, harga_beli = harga saat membelinya (boleh kosong)
        $this->periksaAngka($request, ['harga' => 'Harga', 'harga_beli' => 'Harga beli']);

        $service = Service::findOrFail($id);

        if (! $service->masihBisaDiubah()) {
            return redirect()->route('service.show', $id)->with('error', 'Service ini sudah tidak bisa diubah.');
        }

        $qty = (int) $request->qty;
        $harga = $this->angka($request->harga);

        if ($harga * $qty > 2000000000) {
            return back()->with('error', 'Total harga terlalu besar. Periksa lagi qty dan harganya.')->withInput();
        }

        ServiceItem::create([
            'service_id' => $service->id,
            'jenis' => 'luar',
            'nama' => $request->nama,
            'qty' => $qty,
            'harga_satuan' => $harga,
            'subtotal' => $harga * $qty,
            'modal_satuan' => $this->angka($request->harga_beli),
            'user_id' => Auth::id(),
        ]);

        $this->setelahItemBerubah($service);

        return redirect()->route('service.show', $id)->with('info', 'Sparepart ' . $request->nama . ' ditambahkan.');
    }

    // Jasa pengerjaan: keterangan dan nominal bebas
    public function tambahJasa(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:150',
            'harga' => 'required',
        ]);

        $this->periksaAngka($request, ['harga' => 'Nominal jasa']);

        $service = Service::findOrFail($id);

        if (! $service->masihBisaDiubah()) {
            return redirect()->route('service.show', $id)->with('error', 'Service ini sudah tidak bisa diubah.');
        }

        $harga = $this->angka($request->harga);

        if ($harga < 1) {
            return back()->with('error', 'Nominal jasa harus lebih dari 0.')->withInput();
        }

        ServiceItem::create([
            'service_id' => $service->id,
            'jenis' => 'jasa',
            'nama' => $request->nama,
            'qty' => 1,
            'harga_satuan' => $harga,
            'subtotal' => $harga,
            'modal_satuan' => 0,
            'user_id' => Auth::id(),
        ]);

        $this->setelahItemBerubah($service);

        return redirect()->route('service.show', $id)->with('info', 'Jasa ' . $request->nama . ' ditambahkan.');
    }

    // Menghapus 1 sparepart / jasa. Kalau sparepartnya dari stok toko, stoknya dikembalikan.
    public function hapusItem($id, $itemId)
    {
        DB::beginTransaction();

        $service = Service::lockForUpdate()->findOrFail($id);
        $item = ServiceItem::where('service_id', $service->id)->where('id', $itemId)->first();

        if ($item == null || ! $service->masihBisaDiubah()) {
            DB::rollBack();

            return redirect()->route('service.show', $id)->with('error', 'Item tidak ditemukan, atau service ini sudah tidak bisa diubah.');
        }

        if ($item->jenis == 'stok') {
            $this->kembalikanStok($item, $service);
        }

        $item->delete();
        $service->hitungUlangTotal();

        DB::commit();

        return redirect()->route('service.show', $id)->with('info', $item->nama . ' dihapus dari service.');
    }

    // ------------------------------------------------------------------
    // PERUBAHAN STATUS
    // ------------------------------------------------------------------

    // Pengerjaan selesai, HP siap diambil
    public function selesai($id)
    {
        $service = Service::findOrFail($id);

        if (! in_array($service->status, ['diterima', 'dikerjakan'])) {
            return redirect()->route('service.show', $id)->with('error', 'Service ini tidak sedang dikerjakan.');
        }

        // Harus ada isinya. Kalau memang tidak jadi diperbaiki, pakai tombol Batalkan.
        if ($service->item()->count() == 0) {
            return redirect()->route('service.show', $id)
                ->with('error', 'Isi dulu sparepart atau jasanya. Kalau tidak jadi diperbaiki, pakai tombol Batalkan.');
        }

        $service->status = 'selesai';
        $service->tanggal_selesai = now();
        $service->save();

        return redirect()->route('service.show', $id)->with('info', 'Service ditandai selesai. Silakan hubungi pelanggan untuk mengambil HP-nya.');
    }

    // HP diambil pelanggan dan dibayar. Di sini diskon dan garansi diisi, lalu nota dicetak.
    public function ambil(Request $request, $id)
    {
        $request->validate([
            // garansi harus salah satu dari pilihan yang tersedia (0, 1, 3, 7, ... hari)
            'garansi_hari' => 'required|in:' . implode(',', array_keys(PenjualanDetail::PILIHAN_GARANSI)),
        ]);

        // Diskon boleh diisi semua level. Tolak minus, huruf, dan angka yang terlalu besar.
        $this->periksaAngka($request, ['diskon' => 'Diskon']);
        $diskon = $this->angka($request->diskon);

        $service = Service::findOrFail($id);

        if ($service->status != 'selesai') {
            return redirect()->route('service.show', $id)->with('error', 'Service harus ditandai selesai dulu sebelum diserahkan.');
        }

        // Diskon tidak boleh membuat harga jatuh di bawah modal sparepart.
        // Batasnya = (sparepart + jasa) - modal. Angka batasnya hanya disebut ke Bos, karena itu membuka harga modal.
        $batasDiskon = $service->total_sparepart + $service->total_jasa - $service->modal;

        if ($diskon > $batasDiskon) {
            $pesan = 'Diskon terlalu besar.';
            if (Auth::user()->isBos()) {
                $pesan .= ' Maksimal Rp ' . number_format(max(0, $batasDiskon)) . ' supaya tidak di bawah modal.';
            }

            return redirect()->route('service.show', $id)->with('error', $pesan)->withInput();
        }

        $service->diskon = $diskon;
        $service->garansi_hari = (int) $request->garansi_hari;
        $service->status = 'diambil';
        $service->tanggal_diambil = now();
        $service->diserahkan_oleh = Auth::id();
        $service->save();
        $service->hitungUlangTotal();

        return redirect()->route('service.nota', $id)->with('info', 'Service ' . $service->kode . ' sudah diserahkan dan dibayar. Silakan cetak notanya.');
    }

    // Membatalkan service. Sparepart dari stok toko dikembalikan ke stok.
    //   - Belum diambil : semua level boleh (pelanggan batal, atau HP tidak bisa diperbaiki).
    //   - Sudah diambil : hanya Bos, karena uangnya sudah dicatat.
    public function batal(Request $request, $id)
    {
        $request->validate(
            ['alasan_batal' => 'required|max:500'],
            ['alasan_batal.required' => 'Alasan pembatalan wajib diisi.']
        );

        DB::beginTransaction();

        $service = Service::with('item')->lockForUpdate()->findOrFail($id);

        if ($service->status == 'batal') {
            DB::rollBack();

            return redirect()->route('service.show', $id)->with('error', 'Service ini sudah pernah dibatalkan.');
        }

        if ($service->status == 'diambil' && ! Auth::user()->isBos()) {
            DB::rollBack();

            return redirect()->route('service.show', $id)->with('error', 'Service yang sudah diambil hanya bisa dibatalkan oleh Bos.');
        }

        // Kembalikan semua sparepart yang diambil dari stok toko
        foreach ($service->item as $item) {
            if ($item->jenis == 'stok') {
                $this->kembalikanStok($item, $service);
            }
        }

        $service->status = 'batal';
        $service->batal_at = now();
        $service->batal_oleh = Auth::id();
        $service->alasan_batal = $request->alasan_batal;
        $service->save();

        DB::commit();

        return redirect()->route('service.show', $id)->with('info', 'Service ' . $service->kode . ' dibatalkan. Sparepart dari stok toko sudah dikembalikan.');
    }

    // ------------------------------------------------------------------
    // CETAK
    // ------------------------------------------------------------------

    // Surat tanda terima (diberikan ke pelanggan saat HP ditinggal)
    public function tandaTerima($id)
    {
        $service = Service::with('penerima')->findOrFail($id);

        return view('service.cetak', ['service' => $service, 'jenis' => 'tanda_terima']);
    }

    // Nota service (diberikan saat HP diambil). Baru ada setelah service ditandai selesai.
    public function nota($id)
    {
        $service = Service::with('item')->findOrFail($id);

        if (! in_array($service->status, ['selesai', 'diambil'])) {
            return redirect()->route('service.show', $id)->with('error', 'Nota baru bisa dicetak setelah service ditandai selesai.');
        }

        return view('service.cetak', ['service' => $service, 'jenis' => 'nota']);
    }

    // ------------------------------------------------------------------
    // Fungsi bantuan
    // ------------------------------------------------------------------

    // Dipanggil setiap ada sparepart / jasa ditambahkan
    private function setelahItemBerubah(Service $service)
    {
        // Begitu mulai diisi, statusnya otomatis menjadi "dikerjakan"
        if ($service->status == 'diterima') {
            $service->status = 'dikerjakan';
        }

        $service->hitungUlangTotal();
    }

    // Mengembalikan stok 1 sparepart yang diambil dari stok toko + mencatatnya di kartu stok
    private function kembalikanStok(ServiceItem $item, Service $service)
    {
        // withTrashed: barangnya bisa saja sudah dihapus dari daftar barang, stoknya tetap dikembalikan
        $barang = Barang::withTrashed()->lockForUpdate()->find($item->barang_id);

        if ($barang) {
            $barang->ubahStok($item->qty, 'batal_service', 'Batal dipakai service ' . $service->kode);
        }
    }

    // Aturan validasi form penerimaan. Dipakai oleh store() dan update().
    private function validasiDataService(Request $request)
    {
        $request->validate(
            [
                'nama_pelanggan' => 'required|max:100',
                'hp_pelanggan' => ['required', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
                'merk_tipe' => 'required|max:150',
                'imei' => 'nullable|max:50',
                'keluhan' => 'required|max:1000',
                'kerusakan' => 'nullable|max:1000',
                'kelengkapan' => 'nullable|max:255',
                'kondisi_fisik' => 'nullable|max:255',
                'perkiraan_selesai' => 'nullable|date',
                'nama_teknisi' => 'required|max:100',   // diketik bebas, wajib diisi
                'catatan' => 'nullable|max:1000',
            ],
            ['hp_pelanggan.regex' => 'No. HP pelanggan hanya boleh berisi angka.']
        );

        $this->periksaAngka($request, ['perkiraan_biaya' => 'Perkiraan biaya']);
    }

    // Memindahkan isi form ke object service. Dipakai oleh store() dan update().
    private function isiDariForm(Service $service, Request $request)
    {
        $service->nama_pelanggan = $request->nama_pelanggan;
        $service->hp_pelanggan = $request->hp_pelanggan;
        $service->merk_tipe = $request->merk_tipe;
        $service->imei = $request->imei;
        $service->keluhan = $request->keluhan;
        $service->kerusakan = $request->kerusakan;
        $service->kelengkapan = $request->kelengkapan;
        $service->kondisi_fisik = $request->kondisi_fisik;
        $service->perkiraan_biaya = $this->angka($request->perkiraan_biaya);
        $service->perkiraan_selesai = $request->perkiraan_selesai;
        $service->nama_teknisi = $request->nama_teknisi;
        $service->catatan = $request->catatan;
    }
}
