<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Services\AccessRequestService;

class DocumentPolicy
{
    public function __construct(
        private readonly AccessRequestService $accessRequestService,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function view(User $user, Document $document): bool
    {
        return $this->accessRequestService->userCanViewDocument($user, $document);
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function update(User $user, Document $document): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return $user->isSuperadmin1();
    }

    public function requestAccess(User $user, Document $document): bool
    {
        return $user->is_active && $user->isVisitor();
    }
}
