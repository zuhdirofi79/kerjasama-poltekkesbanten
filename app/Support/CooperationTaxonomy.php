<?php

namespace App\Support;

final class CooperationTaxonomy
{
    public const EDUCATION = 'education';

    public const RESEARCH = 'research';

    public const COMMUNITY_SERVICE = 'community_service';

    public const HR_DEVELOPMENT = 'hr_development';

    public const OTHER = 'other';

    /**
     * @return array<string, string>
     */
    public static function areas(): array
    {
        return config('document_classification.areas', []);
    }

    /**
     * @return list<string>
     */
    public static function triDharmaAreas(): array
    {
        return array_values(config('document_classification.tri_dharma_areas', []));
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function subtypes(): array
    {
        return config('document_classification.subtypes', []);
    }

    /**
     * @return array<string, string>
     */
    public static function subtypesFor(?string $area): array
    {
        if ($area === null || $area === '') {
            return [];
        }

        return self::subtypes()[$area] ?? [];
    }

    public static function areaLabel(?string $area): ?string
    {
        if ($area === null || $area === '') {
            return null;
        }

        return self::areas()[$area] ?? $area;
    }

    public static function subtypeLabel(?string $area, ?string $subtype): ?string
    {
        if ($subtype === null || $subtype === '') {
            return null;
        }

        $map = self::subtypesFor($area);

        return $map[$subtype] ?? $subtype;
    }

    public static function isTriDharma(?string $area): bool
    {
        return $area !== null && in_array($area, self::triDharmaAreas(), true);
    }

    public static function isValidArea(?string $area): bool
    {
        return $area !== null && $area !== '' && array_key_exists($area, self::areas());
    }

    public static function isValidSubtype(?string $area, ?string $subtype): bool
    {
        if ($subtype === null || $subtype === '') {
            return true;
        }

        return array_key_exists($subtype, self::subtypesFor($area));
    }

    /**
     * @return array<string, string>
     */
    public static function sorts(): array
    {
        return config('document_classification.sorts', []);
    }
}
