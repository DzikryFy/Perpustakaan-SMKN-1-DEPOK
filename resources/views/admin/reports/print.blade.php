<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sirkulasi Perpustakaan - SMKN 1 Depok</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #000; padding: 20mm; background: #fff; }
        
        .header { display: flex; align-items: center; border-bottom: 2.5px solid #000; padding-bottom: 8px; margin-bottom: 16px; }
        .header img { height: 75px; width: auto; margin-right: 18px; }
        .header-text { text-align: center; flex: 1; }
        .header-text h3 { font-size: 12pt; text-transform: uppercase; font-weight: normal; margin-bottom: 2px; }
        .header-text h2 { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header-text h1 { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; }
        .header-text p { font-size: 9.5pt; font-style: italic; }

        .title-block { text-align: center; margin: 18px 0; }
        .title-block h3 { font-size: 13pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .title-block p { font-size: 11pt; margin-top: 3px; }

        .summary-box { display: flex; justify-content: space-between; border: 1px solid #000; padding: 8px 12px; margin-bottom: 16px; font-size: 10pt; }
        .summary-box div { flex: 1; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 9.5pt; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }

        .signatures { margin-top: 35px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sign-box { width: 220px; text-align: center; }
        .sign-space { height: 65px; }

        .no-print { position: fixed; top: 15px; right: 15px; background: #2563eb; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-family: sans-serif; font-size: 12px; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
        .no-print:hover { background: #1d4ed8; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print">🖨️ Cetak Dokumen</button>

    <!-- Official Letterhead (Kop Surat) -->
    <div class="header">
        <img src="{{ asset('images/logo-onedek.png') }}" alt="Logo SMKN 1 Depok">
        <div class="header-text">
            <h3>Pemerintah Daerah Provinsi Jawa Barat</h3>
            <h2>Dinas Pendidikan</h2>
            <h1>SMK Negeri 1 Depok</h1>
            <p>Unit Pelaksana Teknis Perpustakaan Digital &bull; Jl. Tapos Raya No. 1, Kec. Tapos, Kota Depok, Jawa Barat</p>
        </div>
    </div>

    <!-- Title -->
    <div class="title-block">
        <h3>Laporan Sirkulasi & Peminjaman Bahan Pustaka</h3>
        <p>Periode: <strong>{{ $periodeLabel }}</strong></p>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <div>Total Transaksi: <strong>{{ $totalPinjam }}</strong></div>
        <div>Telah Dikembalikan: <strong>{{ $totalKembali }}</strong></div>
        <div>Sedang Dipinjam: <strong>{{ $totalAktif }}</strong></div>
        <div>Total Kas Denda: <strong>Rp {{ number_format($totalDenda, 0, ',', '.') }}</strong></div>
    </div>

    <!-- Detail Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Anggota</th>
                <th>Role</th>
                <th>Judul Buku</th>
                <th>Tgl Pinjam</th>
                <th>Batas Kembali</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $index => $l)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $l->user->name }}</td>
                <td style="text-align: center; text-transform: capitalize;">{{ $l->user->role }}</td>
                <td>{{ $l->book->judul }}</td>
                <td style="text-align: center;">{{ $l->tanggal_pinjam->format('d/m/Y') }}</td>
                <td style="text-align: center;">{{ $l->batas_kembali->format('d/m/Y') }}</td>
                <td style="text-align: center;">
                    {{ $l->status === 'dikembalikan' ? 'Kembali' : 'Dipinjam' }}
                </td>
                <td style="text-align: right;">
                    @if($l->bookReturn && $l->bookReturn->total_denda > 0)
                        Rp {{ number_format($l->bookReturn->total_denda, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 15px;">Tidak ada transaksi sirkulasi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sign-box">
            <p>Mengetahui,</p>
            <p><strong>Kepala Perpustakaan</strong></p>
            <div class="sign-space"></div>
            <p><strong><u>{{ $kepala->name ?? 'Kepala Perpustakaan' }}</u></strong></p>
            <p>NIP. 19780512 200801 1 004</p>
        </div>

        <div class="sign-box">
            <p>Kota Depok, {{ date('d F Y') }}</p>
            <p><strong>Pustakawan Pelaksana</strong></p>
            <div class="sign-space"></div>
            <p><strong><u>{{ Auth::user()->name }}</u></strong></p>
            <p>NIP/NUPTK. 19850914 201101 2 008</p>
        </div>
    </div>

</body>
</html>
