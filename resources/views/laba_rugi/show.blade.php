<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laba Rugi {{ $laporan->periode }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 500px; border-collapse: collapse; }
        td { padding: 4px 8px; }
        .kanan { text-align: right; }
        .garis { border-bottom: 1px solid #000; }
        @media print { .hidden-print { display: none !important; } }
    </style>
</head>
<body>
    <h3>{{ strtoupper(config('toko.nama')) }} - Laporan Laba / Rugi</h3>
    <p>Periode: <strong>{{ $laporan->periode }}</strong></p>
    <table>
        <tr><td>Total Penjualan</td><td class="kanan">{{ number_format($laporan->total_penjualan) }}</td></tr>
        <tr><td>Harga Pokok Penjualan</td><td class="kanan garis">{{ number_format($laporan->harga_pokok_penjualan) }}</td></tr>
        <tr><td><strong>Laba Kotor</strong></td><td class="kanan"><strong>{{ number_format($laporan->laba_kotor) }}</strong></td></tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr><td>Gaji</td><td class="kanan">{{ number_format($laporan->gaji) }}</td></tr>
        <tr><td>Listrik</td><td class="kanan">{{ number_format($laporan->listrik) }}</td></tr>
        <tr><td>Biaya Tak Terduga</td><td class="kanan garis">{{ number_format($laporan->operasional) }}</td></tr>
        <tr><td><strong>Total Biaya</strong></td><td class="kanan"><strong>{{ number_format($laporan->total_biaya) }}</strong></td></tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr><td><strong>Laba Bersih</strong></td><td class="kanan"><strong>{{ number_format($laporan->laba_bersih) }}</strong></td></tr>
    </table>
    <br>
    <button class="hidden-print" onclick="window.print()">Cetak</button>
</body>
</html>
