<?php

declare(strict_types=1);

namespace App\Http;

use App\Domain\Contact\ContactValidationException;
use App\Domain\Contact\DuplicateContactException;
use Closure;
use Throwable;

final readonly class ExceptionHandler
{
    private const string UNEXPECTED_ERROR_MESSAGE = 'Ocurrió un error inesperado. Intentá de nuevo en unos minutos.';

    public function __construct(private Closure $reportError)
    {
    }

    public function toResponse(Throwable $error): Response
    {
        return match (true) {
            $error instanceof InvalidRequestException => Response::json(400, ['error' => $error->getMessage()]),
            $error instanceof InvalidCsrfTokenException => Response::json(403, ['error' => $error->getMessage()]),
            $error instanceof RouteNotFoundException => Response::json(404, ['error' => $error->getMessage()]),
            $error instanceof MethodNotAllowedException => $this->methodNotAllowed($error),
            $error instanceof DuplicateContactException => Response::json(409, ['errors' => $error->errors()]),
            $error instanceof ContactValidationException => Response::json(422, ['errors' => $error->errors]),
            default => $this->unexpected($error),
        };
    }

    private function methodNotAllowed(MethodNotAllowedException $error): Response
    {
        return Response::json(
            405,
            ['error' => $error->getMessage()],
            ['Allow' => implode(', ', $error->allowedMethods)],
        );
    }

    private function unexpected(Throwable $error): Response
    {
        ($this->reportError)($error);

        return Response::json(500, ['error' => self::UNEXPECTED_ERROR_MESSAGE]);
    }
}
