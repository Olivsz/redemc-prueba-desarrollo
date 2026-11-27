<?php

declare(strict_types=1);

namespace App\Domain\Contact;

enum ContactField: string
{
    case Name = 'name';
    case Email = 'email';
    case Phone = 'phone';
}
