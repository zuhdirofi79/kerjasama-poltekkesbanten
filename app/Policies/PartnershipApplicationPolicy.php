<?php

namespace App\Policies;

use App\Models\PartnershipApplication;
use App\Models\User;

class PartnershipApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role?->canManagePartnershipApplications() ?? false;
    }

    public function view(User $user, PartnershipApplication $application): bool
    {
        return $user->role?->canManagePartnershipApplications() ?? false;
    }

    public function updateStatus(User $user, PartnershipApplication $application): bool
    {
        return $user->role?->canManagePartnershipApplications() ?? false;
    }

    public function downloadAttachment(User $user, PartnershipApplication $application): bool
    {
        return $user->role?->canManagePartnershipApplications() ?? false;
    }
}
