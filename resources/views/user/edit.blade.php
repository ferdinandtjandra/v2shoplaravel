@extends('layouts.app')

@section('judul', 'Edit Pengguna')

@section('isi')
<div class="row">
    <div class="col-lg-5">
        <div class="card mb-6">
            <h5 class="card-header">{{ $user->name }}</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('user.update', $user->id) }}" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-user"></i></span>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-at"></i></span>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-lock-alt"></i></span>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="form-text">Kosongkan kalau password tidak diganti.</div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="role" class="form-select">
                            @foreach (\App\Models\User::DAFTAR_ROLE as $kode => $label)
                                <option value="{{ $kode }}" {{ old('role', $user->role) == $kode ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
