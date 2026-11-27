<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Contact;

use App\Domain\Contact\ContactValidationException;
use App\Domain\Contact\NewContact;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NewContactTest extends TestCase
{
    #[Test]
    public function buildsANewContactFromValidInput(): void
    {
        $contact = NewContact::fromInput('  Ana   María ', 'ANA@mail.com', '097680102');

        self::assertSame('Ana María', $contact->name->value);
        self::assertSame('ana@mail.com', $contact->email->value);
        self::assertSame('097680102', $contact->phone?->value);
    }

    #[Test]
    public function hasNoPhoneWhenItIsNotProvided(): void
    {
        $contact = NewContact::fromInput('Ana', 'ana@mail.com', '');

        self::assertNull($contact->phone);
    }

    #[Test]
    public function reportsEveryInvalidFieldAtOnce(): void
    {
        $errors = $this->errorsOf(fn () => NewContact::fromInput('   ', 'ana@', '097 680 102'));

        self::assertSame(
            [
                'name' => 'El nombre es obligatorio.',
                'email' => 'El email no tiene un formato válido.',
                'phone' => 'El teléfono debe ser un celular uruguayo de 9 dígitos que empiece con 09, sin espacios ni guiones (por ejemplo: 097680102).',
            ],
            $errors,
        );
    }

    #[Test]
    public function reportsOnlyTheInvalidFields(): void
    {
        $errors = $this->errorsOf(fn () => NewContact::fromInput('Ana', 'ana.mail.com', ''));

        self::assertSame(['email' => 'El email no tiene un formato válido.'], $errors);
    }

    private function errorsOf(callable $action): array
    {
        try {
            $action();
        } catch (ContactValidationException $exception) {
            return $exception->errors();
        }

        self::fail('Expected a ContactValidationException.');
    }
}
