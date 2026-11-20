<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class GameModulePolicy
{
    /**
     * Enabling or disabling modules publishes a new mobile configuration: admins only, tutors are read-only.
     */
    public function manage(User $user): bool
    {
        return $user->is_active && $user->isAdmin();
    }
}
