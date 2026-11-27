<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use DomainException;

final class ContactValidationException extends DomainException
{
    public function __construct(public readonly array $errors)
    {
        parent::__construct('The contact data is not valid.');
    }
}
