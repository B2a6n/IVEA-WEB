<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Data\PasswordResetData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
        ];
    }

    public function toData(): PasswordResetData
    {
        return new PasswordResetData(
            token: $this->string('token')->toString(),
            email: $this->string('email')->trim()->toString(),
            password: $this->string('password')->toString(),
        );
    }
}
