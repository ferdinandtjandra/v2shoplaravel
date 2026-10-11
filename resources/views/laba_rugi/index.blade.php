@extends('layouts.app')

@section('judul', 'Laba / Rugi ' . $tahun)

@php
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // Jumlah setahun (untuk kotak ringkasan dan baris Total)
    $totalPenjualan = array_sum(array_column($perBulan, 'penjualan'));
    $totalLabaKotor = array_sum(array_column($perBulan, 'laba_kotor'));
    $totalBiaya = array_sum(array_column($perBulan, 'biaya'));
    $totalLabaBersih = $totalLabaKotor - $totalBiaya;

    // Laba kotor tertinggi, dipakai untuk tinggi batang grafik (yang tertinggi = 100%)
    $tertinggi = max(array_column($perBulan, 'laba_kotor'));
@endphp

@section('isi')

{{-- ================= PILIH TAHUN ================= --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    @for ($t = date('Y'); $t >= $tahunPertama; $t--)
        <a href="{{ route('laba_rugi.index', ['tahun' => $t]) }}" class="btn btn-sm {{ $t == $tahun ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $t }}</a>
    @endfor
</div>

{{-- ================= RINGKASAN SETAHUN ================= --}}
<div class="row">
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted d-block">Penjualan {{ $tahun }}</small>
            <span class="fs-4 fw-bold">Rp {{ number_format($totalPenjualan) }}</span>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted d-block">Laba Kotor</small>
            <span class="fs-4 fw-bold">Rp {{ number_format($totalLabaKotor) }}</span>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted d-block">Biaya (yang sudah diisi)</small>
            <span class="fs-4 fw-bold">Rp {{ number_format($totalBiaya) }}</span>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted d-block">Laba Bersih</small>
            <span class="fs-4 fw-bold">Rp {{ number_format($totalLabaBersih) }}</span>
            @if ($totalLabaBersih < 0)
                <span class="badge bg-label-danger">Rugi</span>
            @endif
        </div></div>
    </div>
</div>

{{-- ================= GRAFIK LABA KOTOR PER BULAN ================= --}}
<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Laba Kotor per Bulan &middot; {{ $tahun }}</h5>
        <small class="text-muted">Arahkan kursor ke batang untuk melihat angkanya</small>
    </div>
    <div class="card-body">
        @if ($tertinggi <= 0)
            <p class="text-center text-muted py-6 mb-0">Belum ada penjualan di tahun {{ $tahun }}.</p>
        @else
            <small class="text-muted">Tertinggi: Rp {{ number_format($tertinggi) }}</small>

            {{-- Tiap bulan 1 batang. Tinggi batang = persen dari laba kotor tertinggi. (CSS grafik ada di layouts/app.blade.php) --}}
            <div class="grafik mt-2">
                @foreach ($perBulan as $bulan => $data)
                    <div class="kolom" data-bs-toggle="tooltip"
                        title="{{ $namaBulan[$bulan] }}: laba kotor Rp {{ number_format($data['laba_kotor']) }} dari penjualan Rp {{ number_format($data['penjualan']) }} ({{ $data['transaksi'] }} transaksi)">
                        @if ($data['laba_kotor'] > 0)
                            <div class="batang" style="height: {{ max(2, round($data['laba_kotor'] / $tertinggi * 100)) }}%"></div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="grafik-tanggal">
                @foreach ($perBulan as $bulan => $data)
                    <span class="{{ $tahun == date('Y') && $bulan == date('n') ? 'hari-ini' : '' }}">{{ substr($namaBulan[$bulan], 0, 3) }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ================= TABEL 12 BULAN ================= --}}
<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Rincian per Bulan</h5>
            <small class="text-muted">Penjualan dan laba dihitung otomatis. Yang perlu diisi hanya biaya.</small>
        </div>
        {{-- Export berisi tabel di bawah ini untuk tahun yang sedang dibuka --}}
        <a href="{{ route('laba_rugi.export', ['tahun' => $tahun]) }}" class="btn btn-sm btn-outline-success text-nowrap"><i class="icon-base bx bx-download me-1"></i> Export Excel</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover tabel-padat mb-0">
            <thead>
                <tr>
                    <th>Bulan</th><th class="text-end">Transaksi</th><th class="text-end">Penjualan</th><th class="text-end">Harga Pokok</th>
                    <th class="text-end">Laba Kotor</th><th class="text-end">Biaya</th><th class="text-end">Laba Bersih</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perBulan as $bulan => $data)
                    <tr>
                        <td class="fw-medium">{{ $namaBulan[$bulan] }}</td>
                        <td class="text-end">{{ number_format($data['transaksi']) }}</td>
                        <td class="text-end">{{ number_format($data['penjualan']) }}</td>
                        <td class="text-end text-muted">{{ number_format($data['hpp']) }}</td>
                        <td class="text-end">{{ number_format($data['laba_kotor']) }}</td>
                        <td class="text-end">
                            @if ($data['biaya_diisi'])
                                {{ number_format($data['biaya']) }}
                            @else
                                <small class="text-muted">belum diisi</small>
                            @endif
                        </td>
                        <td class="text-end fw-bold">
                            {{ number_format($data['laba_bersih']) }}
                            @if ($data['laba_bersih'] < 0)
                                <span class="badge bg-label-danger">Rugi</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            {{-- Isi tombol dititipkan di data-..., lalu dibaca JavaScript untuk mengisi kotak biaya --}}
                            <button type="button" class="btn btn-sm btn-outline-primary tombol-biaya"
                                data-bulan="{{ $bulan }}" data-nama="{{ $namaBulan[$bulan] }}"
                                data-gaji="{{ $data['biaya_diisi'] ? $data['gaji'] : '' }}"
                                data-listrik="{{ $data['biaya_diisi'] ? $data['listrik'] : '' }}"
                                data-operasional="{{ $data['biaya_diisi'] ? $data['operasional'] : '' }}">
                                {{ $data['biaya_diisi'] ? 'Ubah biaya' : 'Isi biaya' }}
                            </button>
                            <a href="{{ route('laba_rugi.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Cetak"><i class="icon-base bx bx-printer"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td>Total {{ $tahun }}</td>
                    <td class="text-end">{{ number_format(array_sum(array_column($perBulan, 'transaksi'))) }}</td>
                    <td class="text-end">{{ number_format($totalPenjualan) }}</td>
                    <td class="text-end">{{ number_format($totalPenjualan - $totalLabaKotor) }}</td>
                    <td class="text-end">{{ number_format($totalLabaKotor) }}</td>
                    <td class="text-end">{{ number_format($totalBiaya) }}</td>
                    <td class="text-end">{{ number_format($totalLabaBersih) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ================= KOTAK ISI BIAYA ================= --}}
<div class="modal fade" id="modalBiaya">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('laba_rugi.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="bulan" id="biayaBulan" value="{{ old('bulan') }}">
            <div class="modal-header">
                <h5 class="modal-title">Biaya <span id="biayaNama"></span> {{ $tahun }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label">Gaji <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="gaji" id="biayaGaji" class="form-control rupiah" inputmode="numeric" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Listrik <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="listrik" id="biayaListrik" class="form-control rupiah" inputmode="numeric" required>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Biaya Tak Terduga <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="operasional" id="biayaOperasional" class="form-control rupiah" inputmode="numeric" required>
                    </div>
                    <div class="form-text">Isi 0 kalau tidak ada.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Biaya</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    // Mengaktifkan kotak keterangan (tooltip) di batang grafik
    $('[data-bs-toggle="tooltip"]').each(function () {
        bootstrap.Tooltip.getOrCreateInstance(this);
    });

    // Tombol "Isi biaya" / "Ubah biaya": buka kotak biaya, isi dengan data bulan yang diklik
    $('.tombol-biaya').on('click', function () {
        $('#biayaBulan').val($(this).data('bulan'));
        $('#biayaNama').text($(this).data('nama'));
        $('#biayaGaji').val(formatRibuan(String($(this).data('gaji'))));
        $('#biayaListrik').val(formatRibuan(String($(this).data('listrik'))));
        $('#biayaOperasional').val(formatRibuan(String($(this).data('operasional'))));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBiaya')).show();
    });
</script>
@endsection
