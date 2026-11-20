<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\PasswordResetData;
use App\Enums\UserRole;
use App\Exceptions\PasswordResetFailedException;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class ResetUserPasswordAction
{
    /**
     * A reset revokes every existing access: "remember me" cookies (remember_token), open web sessions and the
     * mobile app's Sanctum tokens, so a compromised account is fully recovered.
     *
     * @throws PasswordResetFailedException
     */
    public function execute(PasswordResetData $data): void
    {
        $status = Password::broker()->reset(
            [
                'email' => $data->email,
                'token' => $data->token,
                'password' => $data->password,
                'is_active' => true,
                'role' => UserRole::webPortalValues(),
            ],
            function (User $user, string $password): void {
                DB::transaction(function () use ($user, $password): void {
                    $user->forceFill([
                        'password' => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    $user->tokens()->delete();
                    DB::table('sessions')->where('user_id', $user->getKey())->delete();
                });

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new PasswordResetFailedException;
        }
    }
}
