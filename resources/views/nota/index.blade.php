@extends('layouts.app')

@section('judul', 'Nota Jatuh Tempo')

@section('isi')
<div class="row">

    {{-- KIRI: form tambah --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Tambah Nota</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('nota.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Nomor Nota <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_nota" class="form-control" value="{{ old('nomor_nota') }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="{{ old('tanggal_jatuh_tempo') }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Nominal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="nominal" class="form-control rupiah" value="{{ old('nominal') }}" inputmode="numeric" required>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Informasi Nota</label>
                        <textarea name="informasi" class="form-control" rows="3" placeholder="Contoh: tagihan LCD dari GMT">{{ old('informasi') }}</textarea>
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
                {{-- 2 pilihan tampilan. Tombol yang sedang aktif berwarna biru. --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('nota.index') }}" class="btn btn-sm {{ $tampilDihapus ? 'btn-outline-secondary' : 'btn-primary' }}">Nota aktif</a>
                    <a href="{{ route('nota.index', ['tampil' => 'dihapus']) }}" class="btn btn-sm {{ $tampilDihapus ? 'btn-primary' : 'btn-outline-secondary' }}">Nota dihapus ({{ $jumlahDihapus }})</a>
                </div>
                <span class="badge bg-label-primary">Total Rp {{ number_format($daftar->sum('nominal')) }}</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Nomor Nota</th><th>Jatuh Tempo</th><th class="text-end">Nominal</th><th>Informasi</th><th>Input</th>
                            @if ($tampilDihapus)
                                <th>Dihapus oleh</th><th></th>
                            @else
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $nota)
                            <tr>
                                <td class="fw-medium">{{ $nota->nomor_nota }}</td>
                                <td data-order="{{ $nota->tanggal_jatuh_tempo->format('Y-m-d') }}" class="text-nowrap">
                                    {{ $nota->tanggal_jatuh_tempo->format('j M Y') }}
                                    {{-- Tanda untuk nota aktif yang sudah lewat / hampir jatuh tempo --}}
                                    @if (! $tampilDihapus)
                                        @if ($nota->tanggal_jatuh_tempo->format('Y-m-d') < date('Y-m-d'))
                                            <span class="badge bg-label-danger">Lewat</span>
                                        @elseif ($nota->tanggal_jatuh_tempo->format('Y-m-d') <= date('Y-m-d', strtotime('+7 day')))
                                            <span class="badge bg-label-warning">Segera</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end text-nowrap" data-order="{{ $nota->nominal }}">Rp {{ number_format($nota->nominal) }}</td>
                                <td>{{ $nota->informasi }}</td>
                                <td>
                                    <div>{{ $nota->user->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $nota->created_at ? $nota->created_at->format('j M Y') : '' }}</small>
                                </td>
                                @if ($tampilDihapus)
                                    {{-- Riwayat hapus: siapa dan kapan --}}
                                    <td data-order="{{ $nota->deleted_at->format('Y-m-d H:i:s') }}">
                                        <div class="fw-medium text-danger">{{ $nota->penghapus->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $nota->deleted_at->format('j M Y, H:i') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('nota.kembalikan', $nota->id) }}" class="konfirmasi" data-pesan="Kembalikan nota {{ $nota->nomor_nota }} ke daftar nota aktif?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success text-nowrap"><i class="icon-base bx bx-undo me-1"></i> Kembalikan</button>
                                        </form>
                                    </td>
                                @else
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('nota.destroy', $nota->id) }}" class="konfirmasi" data-pesan="Hapus nota {{ $nota->nomor_nota }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
