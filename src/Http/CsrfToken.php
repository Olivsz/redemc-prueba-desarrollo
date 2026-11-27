<?php

declare(strict_types=1);

namespace App\Http;

final class CsrfToken
{
    public const string HEADER = 'X-CSRF-Token';
    private const string SESSION_KEY = 'csrf_token';
    private const int RANDOM_BYTES = 32;

    public function value(): string
    {
        return $_SESSION[self::SESSION_KEY] ??= bin2hex(random_bytes(self::RANDOM_BYTES));
    }

    public function isValid(?string $token): bool
    {
        $expected = $_SESSION[self::SESSION_KEY] ?? null;

        return $expected !== null
            && $token !== null
            && hash_equals($expected, $token);
    }
}
