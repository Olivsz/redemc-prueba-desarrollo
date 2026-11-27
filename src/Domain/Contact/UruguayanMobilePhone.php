<?php

declare(strict_types=1);

namespace App\Domain\Contact;

final readonly class UruguayanMobilePhone
{
    private const string NINE_DIGITS_STARTING_WITH_09 = '/^09[0-9]{7}$/D';

    private function __construct(public string $value)
    {
    }

    public static function fromString(string $phone): self
    {
        if (preg_match(self::NINE_DIGITS_STARTING_WITH_09, $phone) !== 1) {
            throw new InvalidContactDataException(
                'El teléfono debe ser un celular uruguayo de 9 dígitos que empiece con 09, sin espacios ni guiones (por ejemplo: 097680102).',
            );
        }

        return new self($phone);
    }

    public static function fromOptionalString(string $phone): ?self
    {
        if (trim($phone) === '') {
            return null;
        }

        return self::fromString($phone);
    }
}
