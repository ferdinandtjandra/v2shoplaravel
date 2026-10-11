@extends('layouts.app')

@section('judul', 'Master Toko')

@section('isi')
<div class="row">

    {{-- KIRI: form tambah --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Tambah Toko</h5>
            <div class="card-body">
                <div class="alert alert-primary small" role="alert">
                    Daftar toko ini muncul di menu Penyesuaian Stok saat barang dipindah ke toko lain atau diterima dari toko lain.
                </div>
                <form method="POST" action="{{ route('toko.store') }}">
                    @csrf
                    <div class="mb-6">
                        <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-store"></i></span>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" maxlength="100" placeholder="Contoh: V2 Com" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- KANAN: daftar --}}
    <div class="col-lg-8">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Toko</h5>
                <span class="badge bg-label-primary">{{ $daftar->count() }} data</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>No</th><th>Nama Toko</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $baris)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-medium">{{ $baris->nama }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('toko.edit', $baris->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="icon-base bx bx-edit"></i></a>
                                    <form method="POST" action="{{ route('toko.destroy', $baris->id) }}" class="d-inline konfirmasi" data-pesan="Hapus toko {{ $baris->nama }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-6">Belum ada toko.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
