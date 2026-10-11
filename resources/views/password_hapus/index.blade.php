@extends('layouts.app')

@section('judul', 'Master Password Hapus')

@section('isi')
<div class="row">

    {{-- KIRI: form tambah --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Tambah Password Hapus</h5>
            <div class="card-body">
                <div class="alert alert-primary small" role="alert">
                    Password ini diminta saat menghapus item di keranjang penjualan atau membatalkan transaksi.
                </div>
                <form method="POST" action="{{ route('password_hapus.store') }}" autocomplete="off">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="Contoh: password milik Bos">
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-key"></i></span>
                            <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="form-text">Minimal 4 karakter.</div>
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
                <h5 class="mb-0">Daftar Password Hapus</h5>
                <span class="badge bg-label-primary">{{ $daftar->count() }} data</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>No</th><th>Keterangan</th><th>Password</th><th>Dibuat</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $baris)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $baris->keterangan ?? '-' }}</td>
                                <td>
                                    @if ($baris->password_terlihat)
                                        <code>{{ $baris->password_terlihat }}</code>
                                    @else
                                        <small class="text-muted">belum tersimpan</small>
                                    @endif
                                </td>
                                <td>{{ $baris->created_at->format('j M Y') }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('password_hapus.destroy', $baris->id) }}" class="konfirmasi" data-pesan="Hapus password ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-6">Belum ada password hapus.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
