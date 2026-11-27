<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\RegisterContact;
use App\Domain\Contact\ContactField;
use App\Domain\Contact\ContactRepository;
use App\Domain\Contact\ContactValidationException;
use App\Domain\Contact\DuplicateContactException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryContactRepository;

final class RegisterContactTest extends TestCase
{
    private InMemoryContactRepository $contacts;
    private RegisterContact $registerContact;

    protected function setUp(): void
    {
        $this->contacts = new InMemoryContactRepository();
        $this->registerContact = new RegisterContact($this->contacts);
    }

    #[Test]
    public function registersAValidContact(): void
    {
        $contact = $this->registerContact->execute('Ana María', 'ana@mail.com', '097680102');

        self::assertSame(1, $contact->id);
        self::assertSame('Ana María', $contact->name->value);
        self::assertSame('ana@mail.com', $contact->email->value);
        self::assertSame('097680102', $contact->phone?->value);
        self::assertSame(1, $this->contacts->countAll());
    }

    #[Test]
    public function doesNotSaveAContactWithInvalidData(): void
    {
        $errors = $this->validationErrorsOf(fn () => $this->registerContact->execute('', 'ana@mail.com', ''));

        self::assertSame(['name' => 'El nombre es obligatorio.'], $errors);
        self::assertSame(0, $this->contacts->countAll());
    }

    #[Test]
    public function rejectsAnEmailThatIsAlreadyRegisteredRegardlessOfCase(): void
    {
        $this->registerContact->execute('Ana', 'ana@mail.com', '');

        $errors = $this->duplicateErrorsOf(fn () => $this->registerContact->execute('Otra Ana', 'ANA@Mail.com', ''));

        self::assertSame(['email' => 'Ya existe un contacto con ese email.'], $errors);
    }

    #[Test]
    public function rejectsAPhoneThatIsAlreadyRegistered(): void
    {
        $this->registerContact->execute('Ana', 'ana@mail.com', '097680102');

        $errors = $this->duplicateErrorsOf(fn () => $this->registerContact->execute('Luis', 'luis@mail.com', '097680102'));

        self::assertSame(['phone' => 'Ya existe un contacto con ese teléfono.'], $errors);
    }

    #[Test]
    public function reportsBothDuplicatesAtOnce(): void
    {
        $this->registerContact->execute('Ana', 'ana@mail.com', '097680102');

        $errors = $this->duplicateErrorsOf(fn () => $this->registerContact->execute('Ana', 'ana@mail.com', '097680102'));

        self::assertSame(
            [
                'email' => 'Ya existe un contacto con ese email.',
                'phone' => 'Ya existe un contacto con ese teléfono.',
            ],
            $errors,
        );
    }

    #[Test]
    public function allowsSeveralContactsWithoutPhone(): void
    {
        $this->registerContact->execute('Ana', 'ana@mail.com', '');
        $this->registerContact->execute('Luis', 'luis@mail.com', '');

        self::assertSame(2, $this->contacts->countAll());
    }

    #[Test]
    public function reportsADuplicateDetectedByTheDatabaseWhileSaving(): void
    {
        $contacts = $this->createStub(ContactRepository::class);
        $contacts->method('add')->willThrowException(new DuplicateContactException(ContactField::Email));

        $errors = $this->duplicateErrorsOf(fn () => new RegisterContact($contacts)->execute('Ana', 'ana@mail.com', ''));

        self::assertSame(['email' => 'Ya existe un contacto con ese email.'], $errors);
    }

    private function validationErrorsOf(callable $action): array
    {
        try {
            $action();
        } catch (ContactValidationException $exception) {
            return $exception->errors;
        }

        self::fail('Expected a ContactValidationException.');
    }

    private function duplicateErrorsOf(callable $action): array
    {
        try {
            $action();
        } catch (DuplicateContactException $exception) {
            return $exception->errors();
        }

        self::fail('Expected a DuplicateContactException.');
    }
}
