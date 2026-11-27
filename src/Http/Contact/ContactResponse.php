<?php

declare(strict_types=1);

namespace App\Http\Contact;

use App\Domain\Contact\Contact;
use DateTimeInterface;
use JsonSerializable;

final readonly class ContactResponse implements JsonSerializable
{
    private function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $phone,
        public string $createdAt,
    ) {
    }

    public static function fromContact(Contact $contact): self
    {
        return new self(
            id: $contact->id,
            name: $contact->name->value,
            email: $contact->email->value,
            phone: $contact->phone?->value,
            createdAt: $contact->createdAt->format(DateTimeInterface::ATOM),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
