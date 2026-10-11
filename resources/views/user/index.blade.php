@extends('layouts.app')

@section('judul', 'Master Pengguna')

@section('isi')
<div class="row">

    {{-- KIRI: form tambah --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Tambah Pengguna</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('user.store') }}" autocomplete="off">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-user"></i></span>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-at"></i></span>
                            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                        </div>
                        <div class="form-text">Dipakai untuk login. Tidak boleh sama dengan user lain.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-lock-alt"></i></span>
                            <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="form-text">Minimal 4 karakter.</div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="role" class="form-select">
                            @foreach (\App\Models\User::DAFTAR_ROLE as $kode => $label)
                                <option value="{{ $kode }}" {{ old('role', 'karyawan') == $kode ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Bos: semua menu. Karyawan: penjualan dan stok. Sales: muncul di pilihan sales.</div>
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
                <h5 class="mb-0">Daftar Pengguna</h5>
                <span class="badge bg-label-primary">{{ $daftar->count() }} data</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>Password</th><th>Status</th><th>Aktif</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $baris)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-medium">{{ $baris->name }}</div>
                                    <small class="text-muted">{{ '@' . $baris->username }}</small>
                                </td>
                                <td>
                                    @if ($baris->password_terlihat)
                                        <code>{{ $baris->password_terlihat }}</code>
                                    @else
                                        <small class="text-muted">belum tersimpan</small>
                                    @endif
                                </td>
                                <td>{{ \App\Models\User::DAFTAR_ROLE[$baris->role] ?? $baris->role }}</td>
                                <td>
                                    @if ($baris->aktif)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('user.edit', $baris->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="icon-base bx bx-edit"></i></a>
                                    @if ($baris->id != Auth::id())
                                        <form method="POST" action="{{ route('user.ubah_aktif', $baris->id) }}" class="d-inline konfirmasi"
                                            data-pesan="{{ $baris->aktif ? 'Nonaktifkan' : 'Aktifkan' }} user {{ $baris->name }}?">
                                            @csrf
                                            @if ($baris->aktif)
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Nonaktifkan</button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success">Aktifkan</button>
                                            @endif
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
