<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isSuperadmin1() || $actor->isSuperadmin2();
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return true;
        }

        return $actor->isSuperadmin1();
    }

    public function create(User $actor): bool
    {
        return $actor->isSuperadmin1();
    }

    public function update(User $actor, User $target): bool
    {
        // Nobody except SA1 (self) may mutate Superadmin1.
        if ($target->isSuperadmin1()) {
            return $actor->isSuperadmin1() && $actor->id === $target->id;
        }

        // SA1 may manage SA2 and admins.
        if ($actor->isSuperadmin1()) {
            return true;
        }

        // Users may update their own limited profile (handled in controller fields).
        if ($actor->id === $target->id) {
            return true;
        }

        // SA2 and Admin cannot mutate other privileged accounts.
        return false;
    }

    public function delete(User $actor, User $target): bool
    {
        if ($target->isSuperadmin1()) {
            return false;
        }

        if ($actor->id === $target->id) {
            return false;
        }

        return $actor->isSuperadmin1();
    }

    public function restore(User $actor, User $target): bool
    {
        return $actor->isSuperadmin1() && ! $target->isSuperadmin1();
    }

    public function forceDelete(User $actor, User $target): bool
    {
        return false;
    }

    public function changeRole(User $actor, User $target): bool
    {
        if ($target->isSuperadmin1()) {
            return false;
        }

        return $actor->isSuperadmin1();
    }

    public function deactivate(User $actor, User $target): bool
    {
        if ($target->isSuperadmin1()) {
            return false;
        }

        return $actor->isSuperadmin1() && $actor->id !== $target->id;
    }
}
