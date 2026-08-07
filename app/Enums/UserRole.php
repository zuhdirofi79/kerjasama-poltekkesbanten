<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin1 = 'superadmin1';
    case Superadmin2 = 'superadmin2';
    case Admin = 'admin';
    case Visitor = 'visitor';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin1 => 'Superadmin 1',
            self::Superadmin2 => 'Superadmin 2',
            self::Admin => 'Admin',
            self::Visitor => 'Visitor',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::Superadmin1, self::Superadmin2, self::Admin], true);
    }

    public function canManageAccessRequests(): bool
    {
        return in_array($this, [self::Superadmin1, self::Superadmin2], true);
    }

    public function canManageUsers(): bool
    {
        return $this === self::Superadmin1;
    }

    public function canViewAuditLogs(): bool
    {
        return in_array($this, [self::Superadmin1, self::Superadmin2], true);
    }
}
