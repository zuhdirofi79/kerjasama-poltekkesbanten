<?php

namespace App\Policies;

use App\Models\DocumentAccessRequest;
use App\Models\User;

class DocumentAccessRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role?->canManageAccessRequests() ?? false;
    }

    public function view(User $user, DocumentAccessRequest $request): bool
    {
        if ($user->id === $request->user_id) {
            return true;
        }

        return $user->role?->canManageAccessRequests() ?? false;
    }

    public function approve(User $user, DocumentAccessRequest $request): bool
    {
        return $user->role?->canManageAccessRequests() ?? false;
    }

    public function reject(User $user, DocumentAccessRequest $request): bool
    {
        return $user->role?->canManageAccessRequests() ?? false;
    }

    public function revoke(User $user, DocumentAccessRequest $request): bool
    {
        return $user->role?->canManageAccessRequests() ?? false;
    }
}
