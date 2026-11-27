<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use App\Domain\Whitespace;

final readonly class PersonName
{
    private const int MAX_LENGTH = 100;
    private const string LETTERS_AND_SINGLE_SPACES = '/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+( [A-Za-zÁÉÍÓÚáéíóúÑñÜü]+)*$/uD';

    private function __construct(public string $value)
    {
    }

    public static function fromString(string $name): self
    {
        $normalized = Whitespace::collapse($name);

        self::ensureIsNotEmpty($normalized);
        self::ensureDoesNotExceedMaxLength($normalized);
        self::ensureContainsOnlyLettersAndSpaces($normalized);

        return new self($normalized);
    }

    private static function ensureIsNotEmpty(string $name): void
    {
        if ($name === '') {
            throw new InvalidContactDataException('El nombre es obligatorio.');
        }
    }

    private static function ensureDoesNotExceedMaxLength(string $name): void
    {
        if (mb_strlen($name) > self::MAX_LENGTH) {
            throw new InvalidContactDataException(
                sprintf('El nombre no puede superar los %d caracteres.', self::MAX_LENGTH),
            );
        }
    }

    private static function ensureContainsOnlyLettersAndSpaces(string $name): void
    {
        if (preg_match(self::LETTERS_AND_SINGLE_SPACES, $name) !== 1) {
            throw new InvalidContactDataException('El nombre solo puede contener letras y espacios.');
        }
    }
}
