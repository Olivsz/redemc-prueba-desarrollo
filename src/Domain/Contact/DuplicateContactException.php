<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use DomainException;

final class DuplicateContactException extends DomainException
{
    private const array MESSAGES = [
        'email' => 'Ya existe un contacto con ese email.',
        'phone' => 'Ya existe un contacto con ese teléfono.',
    ];

    public readonly array $fields;

    public function __construct(ContactField ...$fields)
    {
        $this->fields = $fields;

        parent::__construct('The contact is already registered.');
    }

    public function errors(): array
    {
        $errors = [];

        foreach ($this->fields as $field) {
            $errors[$field->value] = self::MESSAGES[$field->value];
        }

        return $errors;
    }
}
