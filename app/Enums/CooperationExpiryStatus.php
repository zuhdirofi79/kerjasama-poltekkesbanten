<?php

namespace App\Enums;

enum CooperationExpiryStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::ExpiringSoon => 'Akan Berakhir',
            self::Expired => 'Kedaluwarsa',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-teal-100 text-teal-800',
            self::ExpiringSoon => 'bg-amber-100 text-amber-800',
            self::Expired => 'bg-red-100 text-red-800',
        };
    }
}
