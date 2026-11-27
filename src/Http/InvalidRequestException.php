<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class InvalidRequestException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El pedido no tiene un formato válido.');
    }
}
