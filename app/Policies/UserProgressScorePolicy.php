<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserProgressScorePolicy
{
    /**
     * An active user may sync their own scores; an active tutor may also sync for active students of
     * the groups they tutor (shared field devices).
     */
    public function syncFor(User $actor, User $target): bool
    {
        if (! $actor->is_active || ! $target->is_active) {
            return false;
        }

        if ($actor->is($target)) {
            return true;
        }

        return $actor->isTutor()
            && $target->isStudent()
            && $target->group_id !== null
            && $actor->tutoredGroups()->whereKey($target->group_id)->exists();
    }
}
