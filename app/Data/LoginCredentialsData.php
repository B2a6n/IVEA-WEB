<?php

declare(strict_types=1);

namespace App\Data;

/**
 * Web portal login attempt, already validated by LoginRequest.
 */
final readonly class LoginCredentialsData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
    ) {}
}
