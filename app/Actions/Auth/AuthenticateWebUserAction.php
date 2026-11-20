<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\LoginCredentialsData;
use App\Exceptions\WebLoginDeniedException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class AuthenticateWebUserAction
{
    /**
     * Logs an active admin or tutor into the web guard. The password is checked before the account state so
     * the specific refusal reasons never reveal anything to someone who does not know the password.
     *
     * @throws WebLoginDeniedException
     */
    public function execute(LoginCredentialsData $credentials): User
    {
        $guard = Auth::guard('web');
        $provider = $guard->getProvider();

        $user = $provider->retrieveByCredentials(['email' => $credentials->email]);

        if (! $user instanceof User) {
            // Spend a hash computation anyway so response time does not reveal whether the email exists.
            Hash::make($credentials->password);

            throw WebLoginDeniedException::invalidCredentials();
        }

        if (! $provider->validateCredentials($user, ['password' => $credentials->password])) {
            throw WebLoginDeniedException::invalidCredentials();
        }

        if (! $user->is_active) {
            throw WebLoginDeniedException::inactiveAccount();
        }

        if (! $user->canAccessWebPortal()) {
            throw WebLoginDeniedException::notPortalRole();
        }

        $guard->login($user, $credentials->remember);
        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }
}
