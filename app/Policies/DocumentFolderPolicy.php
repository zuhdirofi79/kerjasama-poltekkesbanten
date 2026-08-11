<?php

namespace App\Policies;

use App\Models\DocumentFolder;
use App\Models\User;

class DocumentFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function view(User $user, DocumentFolder $documentFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function create(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function update(User $user, DocumentFolder $documentFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function move(User $user, DocumentFolder $documentFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function delete(User $user, DocumentFolder $documentFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function restore(User $user, DocumentFolder $documentFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function forceDelete(User $user, DocumentFolder $documentFolder): bool
    {
        return $user->isSuperadmin1();
    }
}
