<?php

namespace App\Support;

class CategoryIcon
{
    /**
     * Map category slug to public icon asset path (relative to /images/).
     */
    private const MAP = [
        'rumah-sakit' => 'icons/icon-rumah-sakit.svg',
        'dinkes-puskesmas' => 'icons/icon-dinkes.svg',
        'dinas-kesehatan' => 'icons/icon-dinkes.svg',
        'dinkes' => 'icons/icon-dinkes.svg',
        'klinik-praktik-mandiri' => 'icons/icon-klinik.svg',
        'klinik' => 'icons/icon-klinik.svg',
        'perguruan-tinggi' => 'icons/icon-perguruan.svg',
        'pendidikan-tinggi' => 'icons/icon-perguruan.svg',
        'institusi-lainnya' => 'icons/icon-institusi.svg',
        'pemerintah-daerah' => 'icons/icon-institusi.svg',
        'industri-dunia-usaha' => 'icons/icon-institusi.svg',
        'student-exchange' => 'icons/icon-student-exchange.svg',
        'pertukaran-pendidikan' => 'icons/icon-student-exchange.svg',
        'internship' => 'icons/icon-internship.svg',
        'kolaborasi-penelitian' => 'icons/icon-internship.svg',
        'international-class' => 'icons/icon-international-class.svg',
        'pengembangan-kapasitas' => 'icons/icon-international-class.svg',
    ];

    public static function path(?string $slug): string
    {
        $key = strtolower(trim((string) $slug));
        $relative = self::MAP[$key] ?? 'icons/icon-institusi.svg';

        return asset('images/'.$relative);
    }

    public static function forSlug(?string $slug): string
    {
        return self::path($slug);
    }
}
