<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Contact;

use App\Domain\Contact\InvalidContactDataException;
use App\Domain\Contact\PersonName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PersonNameTest extends TestCase
{
    #[Test]
    #[DataProvider('validNames')]
    public function acceptsLettersAndSpaces(string $input, string $expected): void
    {
        self::assertSame($expected, PersonName::fromString($input)->value);
    }

    public static function validNames(): iterable
    {
        yield 'single word' => ['Ana', 'Ana'];
        yield 'accents and ñ' => ['María José Núñez', 'María José Núñez'];
        yield 'uppercase accents' => ['ÁNGEL ÑANDÚ', 'ÁNGEL ÑANDÚ'];
        yield 'diaeresis' => ['Agüero', 'Agüero'];
        yield 'surrounding spaces are trimmed' => ['  Ana  ', 'Ana'];
        yield 'repeated spaces are collapsed' => ['Ana    María', 'Ana María'];
        yield 'exactly 100 characters' => [str_repeat('a', 100), str_repeat('a', 100)];
    }

    #[Test]
    #[DataProvider('emptyNames')]
    public function rejectsAnEmptyName(string $input): void
    {
        $this->expectException(InvalidContactDataException::class);
        $this->expectExceptionMessage('El nombre es obligatorio.');

        PersonName::fromString($input);
    }

    public static function emptyNames(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['     '];
    }

    #[Test]
    #[DataProvider('namesWithInvalidCharacters')]
    public function rejectsCharactersOtherThanLettersAndSpaces(string $input): void
    {
        $this->expectException(InvalidContactDataException::class);
        $this->expectExceptionMessage('El nombre solo puede contener letras y espacios.');

        PersonName::fromString($input);
    }

    public static function namesWithInvalidCharacters(): iterable
    {
        yield 'digits' => ['Ana3'];
        yield 'apostrophe' => ["O'Connor"];
        yield 'hyphen' => ['María-José'];
        yield 'underscore' => ['Ana_María'];
        yield 'dot' => ['Ana.'];
        yield 'html' => ['<script>'];
        yield 'tab between words' => ["Ana\tMaría"];
    }

    #[Test]
    public function rejectsANameLongerThan100Characters(): void
    {
        $this->expectException(InvalidContactDataException::class);
        $this->expectExceptionMessage('El nombre no puede superar los 100 caracteres.');

        PersonName::fromString(str_repeat('a', 101));
    }
}
