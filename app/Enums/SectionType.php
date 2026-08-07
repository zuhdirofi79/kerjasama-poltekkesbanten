<?php

namespace App\Enums;

enum SectionType: string
{
    case Domestic = 'domestic';
    case International = 'international';

    public function label(): string
    {
        return match ($this) {
            self::Domestic => 'Kerjasama Dalam Negeri',
            self::International => 'Kerjasama Luar Negeri',
        };
    }
}
