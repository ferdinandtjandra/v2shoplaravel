@extends('layouts.app')

@section('judul', $tampilDihapus ? 'Laporan Stok (Barang Dihapus)' : ($tampilHabis ? 'Laporan Stok (Stok Habis)' : 'Laporan Stok'))

@section('isi')
<div class="card mb-6">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        {{-- 3 pilihan tampilan (hanya Bos). Tombol yang sedang aktif berwarna biru. --}}
        <div class="d-flex gap-2">
            @if (Auth::user()->isBos())
                <a href="{{ route('barang.index') }}" class="btn btn-sm {{ ! $tampilHabis && ! $tampilDihapus ? 'btn-primary' : 'btn-outline-secondary' }}">Stok tersedia</a>
                <a href="{{ route('barang.index', ['tampil' => 'habis']) }}" class="btn btn-sm {{ $tampilHabis ? 'btn-primary' : 'btn-outline-secondary' }}">Stok habis</a>
                <a href="{{ route('barang.index', ['tampil' => 'dihapus']) }}" class="btn btn-sm {{ $tampilDihapus ? 'btn-primary' : 'btn-outline-secondary' }}">Barang dihapus</a>
            @endif
        </div>

        {{-- Ringkasan: dihitung dari semua barang yang cocok dengan pencarian, bukan hanya halaman ini --}}
        <div class="d-flex gap-2">
            <span class="badge bg-label-primary">{{ number_format($daftar->total()) }} barang</span>
            <span class="badge bg-label-primary">{{ number_format($jumlahPcs) }} pcs</span>
            @if (Auth::user()->isBos() && ! $tampilDihapus)
                <span class="badge bg-label-success">Nilai modal Rp {{ number_format($nilaiModal) }}</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        {{-- Pencarian dan filter. method GET: pilihannya ikut di alamat URL, lalu diproses di server. --}}
        <form method="GET" action="{{ route('barang.index') }}" class="row g-2 align-items-end mb-4">
            <input type="hidden" name="tampil" value="{{ request('tampil') }}">
            <div class="col-md-4">
                <label class="form-label">Cari</label>
                <input type="text" name="cari" class="form-control" value="{{ $cari }}" placeholder="Nama atau kode barang" autofocus>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select name="kategori_id" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Urutkan</label>
                <select name="urut" class="form-select">
                    <option value="">Terbaru</option>
                    <option value="nama" {{ request('urut') == 'nama' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="qty_kecil" {{ request('urut') == 'qty_kecil' ? 'selected' : '' }}>Stok paling sedikit</option>
                    <option value="qty_besar" {{ request('urut') == 'qty_besar' ? 'selected' : '' }}>Stok paling banyak</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="icon-base bx bx-search me-1"></i> Cari</button>
                <a href="{{ route('barang.index', ['tampil' => request('tampil')]) }}" class="btn btn-outline-secondary" title="Hapus pencarian">Reset</a>
                @if (Auth::user()->isBos())
                    {{-- Export mengikuti pencarian dan filter yang sedang dipakai --}}
                    <a href="{{ route('barang.export', request()->query()) }}" class="btn btn-outline-success" title="Export ke Excel"><i class="icon-base bx bx-download"></i> Excel</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover tabel-padat">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        @if (Auth::user()->isBos()) <th>Supplier</th> @endif
                        <th class="text-end">Qty</th>
                        @if (Auth::user()->isBos()) <th class="text-end">Modal</th> @endif
                        <th class="text-end">Member</th>
                        <th class="text-end">User</th>
                        <th class="text-end">+ Ongkir</th>
                        @if (Auth::user()->isBos()) <th></th> @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $barang)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $barang->nama }}</div>
                                <small class="text-muted">{{ $barang->kode }}</small>
                            </td>
                            <td>{{ $barang->kategori->nama ?? '-' }}</td>
                            @if (Auth::user()->isBos()) <td>{{ $barang->supplier->perusahaan ?? '-' }}</td> @endif

                            <td class="text-end text-nowrap">
                                {{-- Stok 2 ke bawah diberi warna supaya gampang terlihat --}}
                                @if ($barang->qty <= 0)
                                    <span class="badge bg-label-danger">{{ $barang->qty }}</span>
                                @elseif ($barang->qty <= 2)
                                    <span class="badge bg-label-warning">{{ $barang->qty }}</span>
                                @else
                                    {{ $barang->qty }}
                                @endif
                                <small class="text-muted">{{ $barang->satuan->nama ?? '' }}</small>
                            </td>
                            @if (Auth::user()->isBos()) <td class="text-end text-muted">{{ number_format($barang->harga_distributor) }}</td> @endif
                            <td class="text-end">{{ number_format($barang->harga_member) }}</td>
                            <td class="text-end">{{ number_format($barang->harga_user) }}</td>
                            <td class="text-end">{{ number_format($barang->harga_ongkir) }}</td>

                            @if (Auth::user()->isBos())
                                <td class="text-end text-nowrap">
                                    @if ($tampilDihapus)
                                        {{-- Barang yang sudah dihapus: lihat riwayat, atau kembalikan --}}
                                        <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-sm btn-outline-primary"><i class="icon-base bx bx-history me-1"></i> Riwayat</a>
                                        <form method="POST" action="{{ route('barang.kembalikan', $barang->id) }}" class="d-inline konfirmasi" data-pesan="Kembalikan {{ $barang->nama }} ke daftar barang?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="icon-base bx bx-undo me-1"></i> Kembalikan</button>
                                        </form>
                                    @else
                                        <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit / Riwayat / Kartu Stok"><i class="icon-base bx bx-edit"></i></a>
                                        <form method="POST" action="{{ route('barang.destroy', $barang->id) }}" class="d-inline konfirmasi" data-pesan="Anda yakin ingin menghapus {{ $barang->nama }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-6">Tidak ada barang yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tombol halaman: 1, 2, 3, ... --}}
        <div class="mt-4">
            {{ $daftar->links() }}
        </div>
    </div>
</div>
@endsection
