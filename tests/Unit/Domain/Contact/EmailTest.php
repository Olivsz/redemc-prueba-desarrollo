<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Contact;

use App\Domain\Contact\Email;
use App\Domain\Contact\InvalidContactDataException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    #[DataProvider('validEmails')]
    public function acceptsAWellFormedEmail(string $input, string $expected): void
    {
        self::assertSame($expected, Email::fromString($input)->value);
    }

    public static function validEmails(): iterable
    {
        yield 'simple' => ['ana@mail.com', 'ana@mail.com'];
        yield 'dots, plus and subdomains' => ['ana.perez+test@mail.com.uy', 'ana.perez+test@mail.com.uy'];
        yield 'hyphen inside the domain' => ['ana@mi-empresa.com', 'ana@mi-empresa.com'];
        yield 'uppercase is lowered' => ['Ana_Perez@Mail.COM', 'ana_perez@mail.com'];
        yield 'surrounding spaces are trimmed' => ['  ana@mail.com  ', 'ana@mail.com'];
        yield 'exactly 254 characters' => [str_repeat('a', 245) . '@mail.com', str_repeat('a', 245) . '@mail.com'];
    }

    #[Test]
    #[DataProvider('emptyEmails')]
    public function rejectsAnEmptyEmail(string $input): void
    {
        $this->expectException(InvalidContactDataException::class);
        $this->expectExceptionMessage('El email es obligatorio.');

        Email::fromString($input);
    }

    public static function emptyEmails(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
    }

    #[Test]
    #[DataProvider('malformedEmails')]
    public function rejectsAMalformedEmail(string $input): void
    {
        $this->expectException(InvalidContactDataException::class);
        $this->expectExceptionMessage('El email no tiene un formato válido.');

        Email::fromString($input);
    }

    public static function malformedEmails(): iterable
    {
        yield 'without at' => ['ana.mail.com'];
        yield 'without domain extension' => ['ana@mail'];
        yield 'two ats' => ['ana@@mail.com'];
        yield 'consecutive dots' => ['ana..perez@mail.com'];
        yield 'starts with a dot' => ['.ana@mail.com'];
        yield 'dot before the at' => ['ana.@mail.com'];
        yield 'domain starts with a hyphen' => ['ana@-mail.com'];
        yield 'domain ends with a hyphen' => ['ana@mail-.com'];
        yield 'space inside' => ['ana perez@mail.com'];
        yield 'one-letter extension' => ['ana@mail.c'];
        yield 'numeric extension' => ['ana@mail.123'];
        yield 'accented letter' => ['maría@mail.com'];
        yield 'longer than 254 characters' => [str_repeat('a', 246) . '@mail.com'];
    }
}
