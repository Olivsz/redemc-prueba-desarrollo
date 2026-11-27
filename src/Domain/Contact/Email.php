<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use App\Domain\Whitespace;

final readonly class Email
{
    private const int MAX_LENGTH = 254;
    // La parte local llega hasta 64 caracteres (RFC 5321) y cada parte del dominio, hasta 63 (RFC 1035).
    private const string FORMAT = '/^(?=[^@]{1,64}@)[A-Za-z0-9_%+-]+(\.[A-Za-z0-9_%+-]+)*@([A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,63}$/D';

    private function __construct(public string $value)
    {
    }

    public static function fromString(string $email): self
    {
        $trimmed = Whitespace::trim($email);

        self::ensureIsNotEmpty($trimmed);
        self::ensureIsWellFormed($trimmed);

        return new self(strtolower($trimmed));
    }

    private static function ensureIsNotEmpty(string $email): void
    {
        if ($email === '') {
            throw new InvalidContactDataException('El email es obligatorio.');
        }
    }

    private static function ensureIsWellFormed(string $email): void
    {
        if (!self::isWellFormed($email)) {
            throw new InvalidContactDataException('El email no tiene un formato válido.');
        }
    }

    private static function isWellFormed(string $email): bool
    {
        return strlen($email) <= self::MAX_LENGTH
            && preg_match(self::FORMAT, $email) === 1;
    }
}
