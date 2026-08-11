<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default root folders provisioned under every YearFolder
    |--------------------------------------------------------------------------
    |
    | These are templates only. After creation they become independent
    | DocumentFolder rows that admins may rename, reorder, nest, or delete.
    |
    */
    'default_year_folders' => [
        'Pendidikan dan pembelajaran',
        'Penelitian',
        'Pengabdian kepada masyarakat',
        'Praktik klinik atau magang',
        'Pengembangan kompetensi SDM',
        'Pelatihan dan sertifikasi',
        'Rekrutmen dan penempatan lulusan',
        'Tracer study dan pengembangan alumni',
        'Pertukaran dosen atau mahasiswa',
        'Seminar dan kegiatan ilmiah',
        'Kelas internasional',
        'Pemanfaatan sarana dan prasarana',
        'Teknologi dan inovasi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum DocumentFolder nesting depth (root = depth 1)
    |--------------------------------------------------------------------------
    */
    'max_folder_depth' => 10,

];
