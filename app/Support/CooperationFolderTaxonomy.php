<?php

namespace App\Support;

use App\Models\DocumentFolder;

final class CooperationFolderTaxonomy
{
    /**
     * Canonical repository tree under every YearFolder.
     *
     * @return array<string, array{name: string, slug: string, sort_order: int, aliases: list<string>, children: list<array{name: string, slug: string, sort_order: int, aliases: list<string>}>}>
     */
    public static function tree(): array
    {
        return [
            'pendidikan' => [
                'name' => 'Pendidikan',
                'slug' => 'pendidikan',
                'sort_order' => 10,
                'aliases' => [
                    'Pendidikan',
                    'Pendidikan dan pembelajaran',
                ],
                'children' => [
                    [
                        'name' => 'Pemanfaatan sarana dan prasarana',
                        'slug' => 'pemanfaatan-sarana-dan-prasarana',
                        'sort_order' => 10,
                        'aliases' => ['Pemanfaatan sarana dan prasarana'],
                    ],
                    [
                        'name' => 'Kelas Internasional',
                        'slug' => 'kelas-internasional',
                        'sort_order' => 20,
                        'aliases' => ['Kelas Internasional', 'Kelas internasional'],
                    ],
                    [
                        'name' => 'Seminar dan Kegiatan',
                        'slug' => 'seminar-dan-kegiatan',
                        'sort_order' => 30,
                        'aliases' => ['Seminar dan Kegiatan', 'Seminar dan kegiatan ilmiah'],
                    ],
                    [
                        'name' => 'Pelatihan dan Sertifikasi',
                        'slug' => 'pelatihan-dan-sertifikasi',
                        'sort_order' => 40,
                        'aliases' => ['Pelatihan dan Sertifikasi', 'Pelatihan dan sertifikasi'],
                    ],
                ],
            ],
            'penelitian' => [
                'name' => 'Penelitian',
                'slug' => 'penelitian',
                'sort_order' => 20,
                'aliases' => ['Penelitian'],
                'children' => [
                    [
                        'name' => 'Teknologi dan Inovasi',
                        'slug' => 'teknologi-dan-inovasi',
                        'sort_order' => 10,
                        'aliases' => ['Teknologi dan Inovasi', 'Teknologi dan inovasi'],
                    ],
                ],
            ],
            'pengabdian' => [
                'name' => 'Pengabdian',
                'slug' => 'pengabdian',
                'sort_order' => 30,
                'aliases' => [
                    'Pengabdian',
                    'Pengabdian kepada masyarakat',
                ],
                'children' => [
                    [
                        'name' => 'Pertukaran dosen',
                        'slug' => 'pertukaran-dosen',
                        'sort_order' => 10,
                        'aliases' => ['Pertukaran dosen', 'Pertukaran dosen atau mahasiswa'],
                    ],
                    [
                        'name' => 'Rekrutmen dan penempatan lulusan',
                        'slug' => 'rekrutmen-dan-penempatan-lulusan',
                        'sort_order' => 20,
                        'aliases' => ['Rekrutmen dan penempatan lulusan'],
                    ],
                    [
                        'name' => 'Praktik Klinik atau Magang',
                        'slug' => 'praktik-klinik-atau-magang',
                        'sort_order' => 30,
                        'aliases' => ['Praktik Klinik atau Magang', 'Praktik klinik atau magang'],
                    ],
                ],
            ],
            'pengembangan-sdm' => [
                'name' => 'Pengembangan SDM',
                'slug' => 'pengembangan-sdm',
                'sort_order' => 40,
                'aliases' => ['Pengembangan SDM'],
                'children' => [
                    [
                        'name' => 'Tracer Study dan Pengembangan Alumni',
                        'slug' => 'tracer-study-dan-pengembangan-alumni',
                        'sort_order' => 10,
                        'aliases' => [
                            'Tracer Study dan Pengembangan Alumni',
                            'Tracer study dan pengembangan alumni',
                        ],
                    ],
                    [
                        'name' => 'Pengembangan kompetensi SDM',
                        'slug' => 'pengembangan-kompetensi-sdm',
                        'sort_order' => 20,
                        'aliases' => ['Pengembangan kompetensi SDM'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function rootNames(): array
    {
        return array_values(array_map(fn (array $root) => $root['name'], self::tree()));
    }

    /**
     * @return list<string>
     */
    public static function rootSlugs(): array
    {
        return array_keys(self::tree());
    }

    /**
     * @return list<string>
     */
    public static function rootAliases(): array
    {
        $aliases = [];
        foreach (self::tree() as $root) {
            foreach ($root['aliases'] as $alias) {
                $aliases[] = $alias;
            }
        }

        return array_values(array_unique($aliases));
    }

    public static function expectedRootCount(): int
    {
        return count(self::tree());
    }

    public static function expectedFolderCount(): int
    {
        $count = 0;
        foreach (self::tree() as $root) {
            $count += 1 + count($root['children']);
        }

        return $count;
    }

    /**
     * @return list<string>
     */
    public static function allRootNormalizedAliases(): array
    {
        return array_values(array_unique(array_map(
            fn (string $alias) => self::normalize($alias),
            self::rootAliases(),
        )));
    }

    public static function normalize(string $name): string
    {
        $name = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name) ?? $name));

        return $name;
    }

    /**
     * @param  list<string>  $aliases
     */
    public static function matchesAliases(string $name, array $aliases): bool
    {
        $normalized = self::normalize($name);

        foreach ($aliases as $alias) {
            if ($normalized === self::normalize($alias)) {
                return true;
            }
        }

        return false;
    }

    public static function isCanonicalRoot(DocumentFolder $folder): bool
    {
        if ($folder->parent_id !== null) {
            return false;
        }

        foreach (self::tree() as $key => $root) {
            if ($folder->slug === $key || self::matchesAliases($folder->name, $root['aliases'])) {
                return true;
            }
        }

        return false;
    }

    public static function rootKeyFor(DocumentFolder $folder): ?string
    {
        foreach (self::tree() as $key => $root) {
            if ($folder->slug === $key || self::matchesAliases($folder->name, $root['aliases'])) {
                return $key;
            }
        }

        return null;
    }
}
