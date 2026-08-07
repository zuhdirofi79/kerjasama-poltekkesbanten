<?php

namespace App\Policies;

use App\Models\InstitutionFolder;
use App\Models\User;

class InstitutionFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function view(User $user, InstitutionFolder $institutionFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function create(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function update(User $user, InstitutionFolder $institutionFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function delete(User $user, InstitutionFolder $institutionFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function restore(User $user, InstitutionFolder $institutionFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function forceDelete(User $user, InstitutionFolder $institutionFolder): bool
    {
        return $user->isSuperadmin1();
    }
}
