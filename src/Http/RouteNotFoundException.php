<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

final class RouteNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Recurso no encontrado.');
    }
}
