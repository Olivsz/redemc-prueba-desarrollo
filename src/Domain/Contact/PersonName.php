<?php

declare(strict_types=1);

namespace App\Domain\Contact;

final readonly class PersonName
{
    private const int MAX_LENGTH = 100;
    private const string LETTERS_AND_SINGLE_SPACES = '/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+( [A-Za-zÁÉÍÓÚáéíóúÑñÜü]+)*$/uD';

    private function __construct(public string $value)
    {
    }

    public static function fromString(string $name): self
    {
        $normalized = self::normalizeSpaces($name);

        if ($normalized === '') {
            throw new InvalidContactDataException('El nombre es obligatorio.');
        }

        if (mb_strlen($normalized) > self::MAX_LENGTH) {
            throw new InvalidContactDataException(sprintf('El nombre no puede superar los %d caracteres.', self::MAX_LENGTH));
        }

        if (preg_match(self::LETTERS_AND_SINGLE_SPACES, $normalized) !== 1) {
            throw new InvalidContactDataException('El nombre solo puede contener letras y espacios.');
        }

        return new self($normalized);
    }

    private static function normalizeSpaces(string $name): string
    {
        return trim(preg_replace('/ {2,}/', ' ', $name));
    }
}
