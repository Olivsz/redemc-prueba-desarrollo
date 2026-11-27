<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactField;
use App\Domain\Contact\DuplicateContactException;
use App\Domain\Contact\Email;
use App\Domain\Contact\NewContact;
use App\Domain\Contact\UruguayanMobilePhone;
use App\Infrastructure\Persistence\DatabaseConfig;
use App\Infrastructure\Persistence\MySqlConnectionFactory;
use App\Infrastructure\Persistence\PdoContactRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PdoContactRepositoryTest extends TestCase
{
    private PDO $connection;
    private PdoContactRepository $contacts;

    protected function setUp(): void
    {
        $this->connection = MySqlConnectionFactory::create(DatabaseConfig::fromEnvironment(getenv()));
        $this->connection->beginTransaction();
        $this->contacts = new PdoContactRepository($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
    }

    #[Test]
    public function addsAContactAndReturnsItWithItsIdAndCreationDate(): void
    {
        $contact = $this->contacts->add(NewContact::fromInput('Ana María', 'ana.test@mail.com', '091111111'));

        self::assertGreaterThan(0, $contact->id);
        self::assertSame('Ana María', $contact->name->value);
        self::assertSame('ana.test@mail.com', $contact->email->value);
        self::assertSame('091111111', $contact->phone?->value);
        self::assertEqualsWithDelta(new DateTimeImmutable()->getTimestamp(), $contact->createdAt->getTimestamp(), 60);
    }

    #[Test]
    public function storesAMissingPhoneAsNull(): void
    {
        $contact = $this->contacts->add(NewContact::fromInput('Ana', 'ana.test@mail.com', ''));

        self::assertNull($contact->phone);
        self::assertNull($this->storedPhoneOf($contact));
    }

    #[Test]
    public function allowsSeveralContactsWithoutPhone(): void
    {
        $this->contacts->add(NewContact::fromInput('Ana', 'ana.test@mail.com', ''));
        $this->contacts->add(NewContact::fromInput('Luis', 'luis.test@mail.com', ''));

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function findsRegisteredEmailsAndPhones(): void
    {
        $this->contacts->add(NewContact::fromInput('Ana', 'ana.test@mail.com', '091111111'));

        self::assertTrue($this->contacts->existsWithEmail(Email::fromString('ana.test@mail.com')));
        self::assertTrue($this->contacts->existsWithPhone(UruguayanMobilePhone::fromString('091111111')));
        self::assertFalse($this->contacts->existsWithEmail(Email::fromString('nadie.test@mail.com')));
        self::assertFalse($this->contacts->existsWithPhone(UruguayanMobilePhone::fromString('099999999')));
    }

    #[Test]
    public function rejectsADuplicateEmail(): void
    {
        $this->contacts->add(NewContact::fromInput('Ana', 'ana.test@mail.com', ''));

        $exception = $this->duplicateOf(fn () => $this->contacts->add(NewContact::fromInput('Otra', 'ana.test@mail.com', '')));

        self::assertSame(ContactField::Email, $exception->field);
    }

    #[Test]
    public function rejectsADuplicatePhone(): void
    {
        $this->contacts->add(NewContact::fromInput('Ana', 'ana.test@mail.com', '091111111'));

        $exception = $this->duplicateOf(fn () => $this->contacts->add(NewContact::fromInput('Luis', 'luis.test@mail.com', '091111111')));

        self::assertSame(ContactField::Phone, $exception->field);
    }

    #[Test]
    public function listsTheNewestContactsFirst(): void
    {
        $this->contacts->add(NewContact::fromInput('Primero', 'primero.test@mail.com', ''));
        $this->contacts->add(NewContact::fromInput('Segundo', 'segundo.test@mail.com', ''));
        $this->contacts->add(NewContact::fromInput('Tercero', 'tercero.test@mail.com', ''));

        self::assertSame(['Tercero', 'Segundo'], $this->namesOf($this->contacts->findLatest(2, 0)));
        self::assertSame(['Segundo'], $this->namesOf($this->contacts->findLatest(1, 1)));
    }

    #[Test]
    public function countsAllContacts(): void
    {
        $before = $this->contacts->countAll();

        $this->contacts->add(NewContact::fromInput('Ana', 'ana.test@mail.com', ''));
        $this->contacts->add(NewContact::fromInput('Luis', 'luis.test@mail.com', ''));

        self::assertSame($before + 2, $this->contacts->countAll());
    }

    private function storedPhoneOf(Contact $contact): ?string
    {
        $statement = $this->connection->prepare('SELECT phone FROM contacts WHERE id = ?');
        $statement->execute([$contact->id]);

        return $statement->fetchColumn();
    }

    private function duplicateOf(callable $action): DuplicateContactException
    {
        try {
            $action();
        } catch (DuplicateContactException $exception) {
            return $exception;
        }

        self::fail('Expected a DuplicateContactException.');
    }

    private function namesOf(array $contacts): array
    {
        return array_map(fn (Contact $contact) => $contact->name->value, $contacts);
    }
}
