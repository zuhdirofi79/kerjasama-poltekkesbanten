<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Pengajuan Kerjasama — Poltekkes Kemenkes Banten</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #134e4a; }
        .header { text-align: center; margin-bottom: 12px; }
        .header img { max-width: 100%; height: auto; width: auto; max-height: 72px; }
        h1 { font-size: 16px; text-align: center; margin: 8px 0 4px; }
        h2 { font-size: 12px; margin: 14px 0 6px; color: #0f766e; }
        p { margin: 4px 0; line-height: 1.45; }
        .box { border: 1px solid #99f6e4; border-radius: 6px; padding: 8px 10px; margin-bottom: 8px; }
        .line { border-bottom: 1px dotted #94a3b8; min-height: 18px; margin: 6px 0; }
        .label { font-weight: bold; }
        .sign { margin-top: 28px; width: 45%; }
    </style>
</head>
<body>
    <div class="header">
        @if($headerPath)
            <img src="{{ $headerPath }}" alt="Header resmi Poltekkes Kemenkes Banten">
        @endif
        <p>POLTEKKES KEMENKES BANTEN</p>
    </div>
    <h1>FORM PENGAJUAN KERJASAMA<br>POLTEKKES KEMENKES BANTEN</h1>

    <h2>A. IDENTITAS PENGUSUL</h2>
    <div class="box">
        <p class="label">Nama resmi instansi / lembaga</p><div class="line"></div>
        <p class="label">Alamat lengkap</p><div class="line"></div>
        <p class="label">Nomor telepon</p><div class="line"></div>
        <p class="label">Alamat website</p><div class="line"></div>
        <p class="label">Email resmi</p><div class="line"></div>
        <p class="label">Jenis instansi</p><div class="line"></div>
    </div>

    <h2>B. PENANGGUNG JAWAB</h2>
    <div class="box">
        <p class="label">Nama pimpinan / pejabat berwenang</p><div class="line"></div>
        <p class="label">Jabatan</p><div class="line"></div>
        <p class="label">Nama narahubung</p><div class="line"></div>
        <p class="label">Jabatan narahubung</p><div class="line"></div>
        <p class="label">WhatsApp / email narahubung</p><div class="line"></div>
    </div>

    <h2>C. INFORMASI USULAN KERJASAMA</h2>
    <div class="box">
        <p class="label">Jenis pengajuan (Internal / Eksternal)</p><div class="line"></div>
        <p class="label">Judul / topik kerja sama</p><div class="line"></div>
        <p class="label">Tujuan</p><div class="line"></div>
        <p class="label">Manfaat yang diharapkan</p><div class="line"></div>
        <p class="label">Unit / program studi tujuan</p><div class="line"></div>
    </div>

    <h2>D. RUANG LINGKUP</h2>
    <div class="box">
        <p>Pendidikan / Penelitian / Pengabdian Masyarakat / Pengembangan SDM / Lainnya</p>
        <p class="label">Uraian kegiatan</p><div class="line"></div>
        <p class="label">Sasaran</p><div class="line"></div>
        <p class="label">Bentuk dokumen (MoU / PKS / IA)</p><div class="line"></div>
    </div>

    <h2>E. RENCANA PELAKSANAAN</h2>
    <div class="box">
        <p class="label">Tanggal mulai</p><div class="line"></div>
        <p class="label">Tanggal selesai</p><div class="line"></div>
        <p class="label">Lokasi kegiatan</p><div class="line"></div>
        <p class="label">Kebutuhan pembiayaan</p><div class="line"></div>
    </div>

    <h2>F. DOKUMEN PENDUKUNG</h2>
    <div class="box">
        <p>1. Surat resmi pengajuan</p>
        <p>2. Profil instansi</p>
        <p>3. Dokumen legalitas / RAB / draft kerja sama (jika ada)</p>
    </div>

    <h2>G. PERNYATAAN</h2>
    <div class="box">
        <p>Saya menyatakan bahwa data dan dokumen yang disampaikan benar serta dapat dipertanggungjawabkan, bersedia mengikuti proses verifikasi, dan menyetujui penggunaan data untuk keperluan administrasi kerja sama.</p>
        <div class="sign">
            <p>Tanggal: ....................................</p>
            <p>Nama Pengusul: ....................................</p>
            <p>Jabatan: ....................................</p>
            <p style="margin-top:48px;">Tanda Tangan: ....................................</p>
        </div>
    </div>
</body>
</html>
