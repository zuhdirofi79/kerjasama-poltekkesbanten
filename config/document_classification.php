<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cooperation area taxonomy (document metadata — NOT folder names)
    |--------------------------------------------------------------------------
    |
    | Repository folders remain free-form. Classification for search,
    | rekapitulasi, and expiry always reads these canonical document fields.
    |
    */

    'areas' => [
        'education' => 'Pendidikan',
        'research' => 'Penelitian',
        'community_service' => 'Pengabdian Masyarakat',
        'hr_development' => 'Pengembangan SDM',
        'other' => 'Lainnya',
    ],

    'tri_dharma_areas' => [
        'education',
        'research',
        'community_service',
    ],

    'subtypes' => [
        'education' => [
            'facilities' => 'Pemanfaatan sarana dan prasarana',
            'international_class' => 'Kelas Internasional',
            'seminar_activity' => 'Seminar dan Kegiatan',
            'training_certification' => 'Pelatihan dan Sertifikasi',
        ],
        'research' => [
            'technology_innovation' => 'Teknologi dan Inovasi',
        ],
        'community_service' => [
            'lecturer_exchange' => 'Pertukaran dosen',
            'graduate_recruitment' => 'Rekrutmen dan penempatan lulusan',
            'clinical_practice_internship' => 'Praktik Klinik atau Magang',
        ],
        'hr_development' => [
            'tracer_alumni' => 'Tracer Study dan Pengembangan Alumni',
            'hr_competency' => 'Pengembangan kompetensi SDM',
        ],
        'other' => [],
    ],

    /*
    | Longer phrases first. Matching maps to documents.cooperation_area.
    */
    'search_aliases' => [
        'community_service' => [
            'tri dharma bidang pengabdian masyarakat',
            'tri dharma pengabdian masyarakat',
            'tri dharma bidang pengabdian',
            'tri dharma pengabdian',
            'tri dharma bidang pengmas',
            'tri dharma pengmas',
            'pengabdian kepada masyarakat',
            'pengabdian masyarakat',
            'pengmas',
        ],
        'research' => [
            'tri dharma bidang penelitian',
            'tri dharma penelitian',
            'penelitian',
        ],
        'education' => [
            'tri dharma bidang pendidikan',
            'tri dharma pendidikan',
            'pendidikan',
        ],
    ],

    'sorts' => [
        'document_date_desc' => 'Tanggal Dokumen Terbaru',
        'document_date_asc' => 'Tanggal Dokumen Terlama',
        'uploaded_desc' => 'Terbaru Diupload',
        'uploaded_asc' => 'Terlama Diupload',
        'title_asc' => 'Judul A-Z',
        'title_desc' => 'Judul Z-A',
    ],

    'expiry_warning_days' => 30,

    'satisfaction_scale' => [
        1 => 'Sangat Tidak Puas',
        2 => 'Tidak Puas',
        3 => 'Cukup',
        4 => 'Puas',
        5 => 'Sangat Puas',
    ],

    'default_satisfaction_questions' => [
        'Kejelasan informasi dan koordinasi kerja sama',
        'Responsivitas pengelola kerja sama',
        'Kemudahan proses administrasi',
        'Kualitas pelaksanaan kegiatan kerja sama',
        'Manfaat kerja sama bagi institusi',
        'Kepuasan secara keseluruhan',
    ],
];
