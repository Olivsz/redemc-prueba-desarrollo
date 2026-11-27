<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class MethodNotAllowedException extends RuntimeException
{
    public function __construct(public readonly array $allowedMethods)
    {
        parent::__construct('Método no permitido.');
    }
}
