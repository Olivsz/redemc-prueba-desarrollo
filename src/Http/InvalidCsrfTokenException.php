<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class InvalidCsrfTokenException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('La sesión expiró. Recargá la página e intentá de nuevo.');
    }
}
