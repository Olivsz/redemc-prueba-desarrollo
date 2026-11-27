<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use DateTimeImmutable;

final readonly class Contact
{
    public function __construct(
        public int $id,
        public PersonName $name,
        public Email $email,
        public ?UruguayanMobilePhone $phone,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
