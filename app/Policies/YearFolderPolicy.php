<?php

namespace App\Policies;

use App\Models\User;
use App\Models\YearFolder;

class YearFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function view(User $user, YearFolder $yearFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function create(User $user): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function update(User $user, YearFolder $yearFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function delete(User $user, YearFolder $yearFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function restore(User $user, YearFolder $yearFolder): bool
    {
        return $user->canAccessAdminPanel();
    }

    public function forceDelete(User $user, YearFolder $yearFolder): bool
    {
        return $user->isSuperadmin1();
    }
}
