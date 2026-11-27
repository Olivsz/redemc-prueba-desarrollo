<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactField;
use App\Domain\Contact\ContactRepository;
use App\Domain\Contact\DuplicateContactException;
use App\Domain\Contact\Email;
use App\Domain\Contact\NewContact;
use App\Domain\Contact\PersonName;
use App\Domain\Contact\UruguayanMobilePhone;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;

final readonly class PdoContactRepository implements ContactRepository
{
    private const int MYSQL_DUPLICATE_ENTRY = 1062;
    private const string PHONE_UNIQUE_KEY = 'uq_contacts_phone';
    private const string COLUMNS = 'id, name, email, phone, created_at';

    public function __construct(private PDO $connection)
    {
    }

    public function existsWithEmail(Email $email): bool
    {
        return $this->exists('SELECT 1 FROM contacts WHERE email = ? LIMIT 1', $email->value);
    }

    public function existsWithPhone(UruguayanMobilePhone $phone): bool
    {
        return $this->exists('SELECT 1 FROM contacts WHERE phone = ? LIMIT 1', $phone->value);
    }

    public function add(NewContact $contact): Contact
    {
        $this->insert($contact);

        return $this->findById((int) $this->connection->lastInsertId());
    }

    public function findLatest(int $limit, int $offset): array
    {
        $statement = $this->connection->prepare(
            'SELECT ' . self::COLUMNS . ' FROM contacts ORDER BY id DESC LIMIT ? OFFSET ?',
        );
        $statement->bindValue(1, $limit, PDO::PARAM_INT);
        $statement->bindValue(2, $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map($this->toContact(...), $statement->fetchAll());
    }

    public function countAll(): int
    {
        return (int) $this->connection->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
    }

    private function exists(string $sql, string $value): bool
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute([$value]);

        return $statement->fetchColumn() !== false;
    }

    private function insert(NewContact $contact): void
    {
        $statement = $this->connection->prepare('INSERT INTO contacts (name, email, phone) VALUES (?, ?, ?)');

        try {
            $statement->execute([$contact->name->value, $contact->email->value, $contact->phone?->value]);
        } catch (PDOException $exception) {
            $this->ensureIsNotADuplicate($exception);

            throw $exception;
        }
    }

    private function ensureIsNotADuplicate(PDOException $exception): void
    {
        if ($this->isDuplicateEntry($exception)) {
            throw new DuplicateContactException($this->duplicatedFieldIn($exception));
        }
    }

    private function isDuplicateEntry(PDOException $exception): bool
    {
        return ($exception->errorInfo[1] ?? null) === self::MYSQL_DUPLICATE_ENTRY;
    }

    private function duplicatedFieldIn(PDOException $exception): ContactField
    {
        return str_contains($exception->getMessage(), self::PHONE_UNIQUE_KEY)
            ? ContactField::Phone
            : ContactField::Email;
    }

    private function findById(int $id): Contact
    {
        $statement = $this->connection->prepare('SELECT ' . self::COLUMNS . ' FROM contacts WHERE id = ?');
        $statement->execute([$id]);

        return $this->toContact($statement->fetch());
    }

    private function toContact(array $row): Contact
    {
        return new Contact(
            id: $row['id'],
            name: PersonName::fromString($row['name']),
            email: Email::fromString($row['email']),
            phone: UruguayanMobilePhone::fromOptionalString($row['phone'] ?? ''),
            createdAt: new DateTimeImmutable($row['created_at'], new DateTimeZone('UTC')),
        );
    }
}
