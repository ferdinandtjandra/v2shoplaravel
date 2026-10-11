{{-- Isi form supplier. Dipakai oleh halaman tambah (index) dan halaman edit. --}}
{{-- $supplier berisi data lama saat edit, dan null saat tambah baru. --}}

<h6 class="text-muted text-uppercase small mb-3">Data Perusahaan</h6>
<div class="row">
    <div class="col-md-4 mb-4">
        <label class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base bx bx-store"></i></span>
            <input type="text" name="perusahaan" class="form-control" value="{{ old('perusahaan', $supplier->perusahaan ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">PIC (nama kontak) <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base bx bx-user"></i></span>
            <input type="text" name="pic" class="form-control" value="{{ old('pic', $supplier->pic ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" class="form-control" rows="1">{{ old('alamat', $supplier->alamat ?? '') }}</textarea>
    </div>
</div>

<h6 class="text-muted text-uppercase small mb-3 mt-2">Kontak</h6>
<div class="row">
    <div class="col-md-4 mb-4">
        <label class="form-label">No. HP <span class="text-danger">*</span></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base bx bx-mobile"></i></span>
            <input type="text" name="hp" class="form-control" value="{{ old('hp', $supplier->hp ?? '') }}" placeholder="08xxxxxxxxxx" required>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">Telp</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base bx bx-phone"></i></span>
            <input type="text" name="telp" class="form-control" value="{{ old('telp', $supplier->telp ?? '') }}">
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">Email</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base bx bx-envelope"></i></span>
            <input type="text" name="email" class="form-control" value="{{ old('email', $supplier->email ?? '') }}">
        </div>
    </div>
</div>

<h6 class="text-muted text-uppercase small mb-3 mt-2">Rekening Bank</h6>
<div class="row">
    <div class="col-md-4 mb-4">
        <label class="form-label">Bank</label>
        <input type="text" name="bank" class="form-control" value="{{ old('bank', $supplier->bank ?? '') }}" placeholder="Contoh: BCA">
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">No. Rekening</label>
        <input type="text" name="rek" class="form-control" value="{{ old('rek', $supplier->rek ?? '') }}">
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">Atas Nama</label>
        <input type="text" name="atasnama" class="form-control" value="{{ old('atasnama', $supplier->atasnama ?? '') }}">
    </div>
</div>
