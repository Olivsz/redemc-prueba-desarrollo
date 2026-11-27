<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactRepository;
use App\Domain\Contact\Email;
use App\Domain\Contact\NewContact;
use App\Domain\Contact\UruguayanMobilePhone;
use DateTimeImmutable;

final class InMemoryContactRepository implements ContactRepository
{
    private array $contacts = [];
    private int $nextId = 1;

    public function existsWithEmail(Email $email): bool
    {
        return array_any($this->contacts, fn (Contact $contact) => $contact->email->value === $email->value);
    }

    public function existsWithPhone(UruguayanMobilePhone $phone): bool
    {
        return array_any($this->contacts, fn (Contact $contact) => $contact->phone?->value === $phone->value);
    }

    public function add(NewContact $contact): Contact
    {
        $saved = new Contact($this->nextId++, $contact->name, $contact->email, $contact->phone, new DateTimeImmutable());
        $this->contacts[] = $saved;

        return $saved;
    }

    public function findLatest(int $limit, int $offset): array
    {
        return array_slice(array_reverse($this->contacts), $offset, $limit);
    }

    public function countAll(): int
    {
        return count($this->contacts);
    }
}
