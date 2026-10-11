@extends('layouts.app')

@section('judul', 'Pengingat')

@section('isi')
{{-- translatedFormat('j F Y'): tanggal dengan nama bulan Bahasa Indonesia, contoh: 11 Oktober 2026 --}}
<div class="row">

    {{-- KIRI: form tambah --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Tambah Pengingat</h5>
            <div class="card-body">
                <div class="alert alert-primary small" role="alert">
                    Pengingat muncul mencolok di dashboard <strong>Bos dan staff</strong>, hanya <strong>pada tanggal yang dipilih</strong>. Besoknya otomatis hilang dari dashboard.
                </div>
                <form method="POST" action="{{ route('pengingat.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Pesan <span class="text-danger">*</span></label>
                        <textarea name="pesan" class="form-control" rows="3" maxlength="500" placeholder="Contoh: Toko tutup jam 3 sore, ada stok opname" required>{{ old('pesan') }}</textarea>
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
                <h5 class="mb-0">Akan Datang</h5>
                <span class="badge bg-label-primary">{{ $akanDatang->count() }} pengingat</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover tabel-padat mb-0">
                    <thead>
                        <tr><th>Tanggal</th><th>Pesan</th><th>Dibuat oleh</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($akanDatang as $p)
                            <tr>
                                <td class="text-nowrap">
                                    {{ $p->tanggal->translatedFormat('j F Y') }}
                                    @if ($p->sisaHari() == 0)
                                        <span class="badge bg-danger">Hari ini, sedang tampil</span>
                                    @elseif ($p->sisaHari() == 1)
                                        <small class="text-muted">besok</small>
                                    @else
                                        <small class="text-muted">{{ $p->sisaHari() }} hari lagi</small>
                                    @endif
                                </td>
                                <td>{{ $p->pesan }}</td>
                                <td>{{ $p->user->name ?? '-' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('pengingat.destroy', $p->id) }}" class="konfirmasi" data-pesan="Hapus pengingat ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-6">Belum ada pengingat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($sudahLewat->count() > 0)
            <div class="card mb-6">
                <h5 class="card-header">Sudah Lewat</h5>
                <div class="table-responsive">
                    <table class="table table-hover tabel-padat mb-0">
                        <tbody>
                            @foreach ($sudahLewat as $p)
                                <tr class="text-muted">
                                    <td class="text-nowrap">{{ $p->tanggal->translatedFormat('j F Y') }}</td>
                                    <td>{{ $p->pesan }}</td>
                                    <td>{{ $p->user->name ?? '-' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('pengingat.destroy', $p->id) }}" class="konfirmasi" data-pesan="Hapus pengingat ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
