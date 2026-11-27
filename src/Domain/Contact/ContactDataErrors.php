<?php

declare(strict_types=1);

namespace App\Domain\Contact;

final class ContactDataErrors
{
    private array $messages = [];

    public function capture(ContactField $field, callable $create): mixed
    {
        try {
            return $create();
        } catch (InvalidContactDataException $exception) {
            $this->messages[$field->value] = $exception->getMessage();

            return null;
        }
    }

    public function throwIfAny(): void
    {
        if ($this->messages !== []) {
            throw new ContactValidationException($this->messages);
        }
    }
}
