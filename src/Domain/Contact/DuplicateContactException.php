<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use DomainException;

final class DuplicateContactException extends DomainException
{
    public function __construct(public readonly ContactField $field)
    {
        parent::__construct("A contact with the same {$field->value} already exists.");
    }
}
