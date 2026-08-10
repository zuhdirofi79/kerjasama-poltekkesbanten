<?php

namespace App\Enums;

enum PartnershipApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NeedsRevision = 'needs_revision';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Diajukan',
            self::UnderReview => 'Sedang Ditelaah',
            self::NeedsRevision => 'Perlu Perbaikan',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Closed => 'Ditutup',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Submitted => 'bg-sky-100 text-sky-800',
            self::UnderReview => 'bg-amber-100 text-amber-800',
            self::NeedsRevision => 'bg-orange-100 text-orange-800',
            self::Approved => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-red-100 text-red-800',
            self::Closed => 'bg-slate-100 text-slate-700',
        };
    }
}
