<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use DomainException;

final class ContactValidationException extends DomainException
{
    private const array DUPLICATE_MESSAGES = [
        'email' => 'Ya existe un contacto con ese email.',
        'phone' => 'Ya existe un contacto con ese teléfono.',
    ];

    public function __construct(public readonly array $errors)
    {
        parent::__construct('The contact data is not valid.');
    }

    public static function forDuplicates(ContactField ...$fields): self
    {
        $errors = [];

        foreach ($fields as $field) {
            $errors[$field->value] = self::DUPLICATE_MESSAGES[$field->value];
        }

        return new self($errors);
    }
}
