<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Contact;

use App\Domain\Contact\InvalidContactDataException;
use App\Domain\Contact\UruguayanMobilePhone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UruguayanMobilePhoneTest extends TestCase
{
    private const string INVALID_PHONE_MESSAGE =
        'El teléfono debe ser un celular uruguayo de 9 dígitos que empiece con 09, sin espacios ni guiones (por ejemplo: 097680102).';

    #[Test]
    #[DataProvider('validPhones')]
    public function acceptsNineDigitsStartingWith09(string $input): void
    {
        self::assertSame($input, UruguayanMobilePhone::fromString($input)->value);
    }

    public static function validPhones(): iterable
    {
        yield 'example from the requirements' => ['097680102'];
        yield 'another mobile' => ['091234567'];
    }

    #[Test]
    #[DataProvider('invalidPhones')]
    public function rejectsAnythingElse(string $input): void
    {
        $this->expectException(InvalidContactDataException::class);
        $this->expectExceptionMessage(self::INVALID_PHONE_MESSAGE);

        UruguayanMobilePhone::fromString($input);
    }

    public static function invalidPhones(): iterable
    {
        yield 'eight digits' => ['97680102'];
        yield 'ten digits' => ['0976801020'];
        yield 'starts with 08' => ['087680102'];
        yield 'inner spaces' => ['097 680 102'];
        yield 'hyphens' => ['097-680-102'];
        yield 'country code with plus' => ['+59897680102'];
        yield 'country code without plus' => ['59897680102'];
        yield 'leading space' => [' 097680102'];
        yield 'trailing space' => ['097680102 '];
        yield 'trailing line break' => ["097680102\n"];
        yield 'letter O instead of zero' => ['O97680102'];
    }

    #[Test]
    #[DataProvider('blankPhones')]
    public function treatsABlankOptionalPhoneAsNoPhone(string $input): void
    {
        self::assertNull(UruguayanMobilePhone::fromOptionalString($input));
    }

    public static function blankPhones(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'only non-breaking spaces' => ["\u{00A0}\u{00A0}"];
    }

    #[Test]
    public function validatesAFilledOptionalPhone(): void
    {
        self::assertSame('097680102', UruguayanMobilePhone::fromOptionalString('097680102')?->value);

        $this->expectException(InvalidContactDataException::class);
        $this->expectExceptionMessage(self::INVALID_PHONE_MESSAGE);

        UruguayanMobilePhone::fromOptionalString('097 680 102');
    }
}
