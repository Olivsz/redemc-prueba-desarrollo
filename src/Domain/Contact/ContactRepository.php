<?php

declare(strict_types=1);

namespace App\Domain\Contact;

interface ContactRepository
{
    public function existsWithEmail(Email $email): bool;

    public function existsWithPhone(UruguayanMobilePhone $phone): bool;

    /**
     * @throws DuplicateContactException si la base rechaza el email o el teléfono por repetido.
     */
    public function add(NewContact $contact): Contact;

    /**
     * Contactos ordenados del más reciente al más antiguo.
     */
    public function findLatest(int $limit, int $offset): array;

    public function countAll(): int;
}
