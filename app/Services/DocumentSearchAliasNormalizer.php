<?php

namespace App\Services;

final class DocumentSearchAliasNormalizer
{
    /**
     * Map a free-text query onto documents.cooperation_area when it matches
     * a known Tri Dharma alias. Remaining text is returned for LIKE search.
     *
     * @return array{area: ?string, text: string}
     */
    public function resolve(?string $query): array
    {
        $normalized = $this->normalize((string) $query);

        if ($normalized === '') {
            return ['area' => null, 'text' => ''];
        }

        $aliases = $this->sortedAliases();

        foreach ($aliases as $phrase => $area) {
            if ($normalized === $phrase) {
                return ['area' => $area, 'text' => ''];
            }
        }

        foreach ($aliases as $phrase => $area) {
            if (! str_contains($normalized, $phrase)) {
                continue;
            }

            $remainder = trim(preg_replace('/\s+/', ' ', str_replace($phrase, ' ', $normalized)) ?? '');

            return ['area' => $area, 'text' => $remainder];
        }

        return ['area' => null, 'text' => $normalized];
    }

    /**
     * @return array<string, string> phrase => area, longest first
     */
    private function sortedAliases(): array
    {
        $flat = [];

        foreach (config('document_classification.search_aliases', []) as $area => $phrases) {
            foreach ($phrases as $phrase) {
                $key = $this->normalize((string) $phrase);
                if ($key === '') {
                    continue;
                }
                $flat[$key] = (string) $area;
            }
        }

        uksort($flat, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        return $flat;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }
}
