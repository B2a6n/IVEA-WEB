<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

final class UserPolicy
{
    /**
     * Portal staff may open the record of an educando. Any other target answers 404, so the directory endpoints
     * do not reveal which ids belong to admins or tutors.
     */
    public function viewEducando(User $actor, User $target): Response
    {
        if (! $actor->canAccessWebPortal()) {
            return Response::deny();
        }

        return $target->isStudent() ? Response::allow() : Response::denyAsNotFound();
    }
}
