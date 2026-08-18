<?php

return [

    'max_upload_mb' => (int) env('PARTNERSHIP_APPLICATION_MAX_UPLOAD_MB', 10),

    'private_disk' => env('PARTNERSHIP_APPLICATION_DISK', 'private_docs'),

    'storage_root' => 'partnership-applications',

    'submission_code_prefix' => 'KS',

    'rate_limit_per_hour' => (int) env('PARTNERSHIP_APPLICATION_RATE_LIMIT', 5),

    'allowed_extensions' => [
        'official_request_letter' => ['pdf', 'doc', 'docx'],
        'filled_template' => ['pdf', 'doc', 'docx'],
        'institution_profile' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
        'supporting_document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
    ],

    'allowed_mimes' => [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
    ],

    'blocked_extensions' => [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'sh', 'bash', 'exe', 'bat', 'cmd', 'com', 'msi',
        'js', 'mjs', 'cjs', 'vbs', 'ps1', 'cgi', 'pl', 'py', 'rb',
        'htaccess', 'html', 'htm', 'shtml',
    ],

    'applicant_types' => [
        'internal' => 'Internal',
        'external' => 'Eksternal',
    ],

    'internal_institution' => [
        'name' => 'Poltekkes Kemenkes Banten',
        'address' => 'Jl. Syekh Moh. Nawawi Albantani No.12, Banjaragung, Kec. Cipocok Jaya, Kota Serang, Banten 42122',
        'phone' => '+62 851-8777-8838',
        'website' => 'https://poltekkesbanten.ac.id/',
    ],

    'institution_types' => [
        'instansi_pemerintah' => 'Instansi pemerintah',
        'perguruan_tinggi' => 'Perguruan tinggi',
        'rumah_sakit_fasyankes' => 'Rumah sakit atau fasilitas pelayanan kesehatan',
        'organisasi_profesi' => 'Organisasi profesi',
        'perusahaan_industri' => 'Perusahaan atau dunia industri',
        'yayasan_lembaga_sosial' => 'Yayasan atau lembaga sosial',
        'lembaga_pendidikan_pelatihan' => 'Lembaga pendidikan atau pelatihan',
        'lembaga_internasional' => 'Lembaga internasional',
        'lainnya' => 'Lainnya',
    ],

    'partner_statuses' => [
        'dalam_negeri' => 'Dalam negeri',
        'luar_negeri' => 'Luar negeri',
    ],

    'proposal_types' => [
        'kerja_sama_baru' => 'Kerja sama baru',
        'perpanjangan' => 'Perpanjangan kerja sama',
        'perubahan_adendum' => 'Perubahan atau adendum',
        'pengembangan_ruang_lingkup' => 'Pengembangan ruang lingkup',
        'lainnya' => 'Lainnya',
    ],

    'target_units' => [
        'tingkat_direktorat' => 'Tingkat Direktorat',
        'd3_keperawatan' => 'D-III Keperawatan',
        'd3_kebidanan' => 'D-III Kebidanan',
        'd3_tlm' => 'D-III Teknologi Laboratorium Medis',
        'sarjana_terapan_keperawatan' => 'Sarjana Terapan Keperawatan',
        'profesi_ners' => 'Pendidikan Profesi Ners',
        'unit_lainnya' => 'Unit lainnya',
    ],

    'proposed_scopes' => [
        'pendidikan_pembelajaran' => 'Pendidikan dan pembelajaran',
        'penelitian' => 'Penelitian',
        'pengabdian_masyarakat' => 'Pengabdian kepada masyarakat',
        'praktik_klinik_magang' => 'Praktik klinik atau magang',
        'pengembangan_kompetensi_sdm' => 'Pengembangan kompetensi SDM',
        'pelatihan_sertifikasi' => 'Pelatihan dan sertifikasi',
        'rekrutmen_penempatan_lulusan' => 'Rekrutmen dan penempatan lulusan',
        'tracer_study_alumni' => 'Tracer study dan pengembangan alumni',
        'pertukaran_dosen_mahasiswa' => 'Pertukaran dosen atau mahasiswa',
        'seminar_kegiatan_ilmiah' => 'Seminar dan kegiatan ilmiah',
        'kelas_internasional' => 'Kelas internasional',
        'pemanfaatan_sarana_prasarana' => 'Pemanfaatan sarana dan prasarana',
        'teknologi_inovasi' => 'Teknologi dan inovasi',
        'lainnya' => 'Lainnya',
    ],

    'document_forms' => [
        'mou' => 'Nota Kesepahaman (MoU)',
        'pks' => 'Perjanjian Kerja Sama (PKS)',
        'ia' => 'Implementation Arrangement (IA)',
        'belum_ditentukan' => 'Belum ditentukan',
    ],

    'proposed_durations' => [
        '1_tahun' => '1 tahun',
        '2_tahun' => '2 tahun',
        '3_tahun' => '3 tahun',
        '4_tahun' => '4 tahun',
        '5_tahun' => '5 tahun',
        'lainnya' => 'Lainnya',
    ],

    'requires_funding_options' => [
        'ya' => 'Ya',
        'tidak' => 'Tidak',
        'belum_ditentukan' => 'Belum dapat ditentukan',
    ],

    'funding_sources' => [
        'calon_mitra' => 'Calon mitra',
        'poltekkes' => 'Poltekkes Kemenkes Banten',
        'bersama' => 'Pembiayaan bersama',
        'hibah_sponsor' => 'Hibah atau sponsor',
        'tidak_memerlukan_biaya' => 'Tidak memerlukan biaya',
        'belum_ditentukan' => 'Belum ditentukan',
    ],

];
