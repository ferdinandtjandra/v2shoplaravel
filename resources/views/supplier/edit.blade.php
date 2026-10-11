@extends('layouts.app')

@section('judul', 'Edit Supplier')

@section('isi')
<div class="card mb-6">
    <h5 class="card-header">{{ $supplier->perusahaan }}</h5>
    <div class="card-body">
        <form method="POST" action="{{ route('supplier.update', $supplier->id) }}">
            @csrf
            @method('PUT')
            @include('supplier._form', ['supplier' => $supplier])
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">Kembali ke Daftar Supplier</a>
                <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= RIWAYAT SUPPLIER ================= --}}
<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Riwayat Supplier</h5>
        <small class="text-muted">Siapa yang membuat dan mengubah</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Waktu</th><th>Aksi</th><th>Keterangan</th><th>Oleh</th></tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                    <tr>
                        <td class="text-nowrap">
                            <div>{{ $r->created_at->format('j M Y') }}</div>
                            <small class="text-muted">{{ $r->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @if ($r->aksi == 'dibuat')
                                <span class="badge bg-label-success">Dibuat</span>
                            @else
                                <span class="badge bg-label-warning">Diubah</span>
                            @endif
                        </td>
                        {{-- nl2br: tiap perubahan tampil di baris sendiri --}}
                        <td>{!! nl2br(e($r->keterangan)) !!}</td>
                        <td>{{ $r->user->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-6">Belum ada riwayat. Perubahan berikutnya akan tercatat di sini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
