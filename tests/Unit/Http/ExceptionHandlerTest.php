<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Domain\Contact\ContactField;
use App\Domain\Contact\ContactValidationException;
use App\Domain\Contact\DuplicateContactException;
use App\Http\ExceptionHandler;
use App\Http\InvalidCsrfTokenException;
use App\Http\InvalidRequestException;
use App\Http\MethodNotAllowedException;
use App\Http\Response;
use App\Http\RouteNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

final class ExceptionHandlerTest extends TestCase
{
    private array $reportedErrors = [];
    private ExceptionHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ExceptionHandler(function (Throwable $error): void {
            $this->reportedErrors[] = $error;
        });
    }

    #[Test]
    public function answersBadRequestForAnUnreadableRequest(): void
    {
        $response = $this->handler->toResponse(new InvalidRequestException());

        self::assertSame(400, $response->status);
        self::assertSame(['error' => 'El pedido no tiene un formato válido.'], $this->bodyOf($response));
    }

    #[Test]
    public function answersForbiddenForAnInvalidCsrfToken(): void
    {
        $response = $this->handler->toResponse(new InvalidCsrfTokenException());

        self::assertSame(403, $response->status);
        self::assertSame(['error' => 'La sesión expiró. Recargá la página e intentá de nuevo.'], $this->bodyOf($response));
    }

    #[Test]
    public function answersNotFoundForAnUnknownRoute(): void
    {
        $response = $this->handler->toResponse(new RouteNotFoundException());

        self::assertSame(404, $response->status);
        self::assertSame(['error' => 'Recurso no encontrado.'], $this->bodyOf($response));
    }

    #[Test]
    public function answersMethodNotAllowedWithTheAllowedMethods(): void
    {
        $response = $this->handler->toResponse(new MethodNotAllowedException(['GET', 'HEAD']));

        self::assertSame(405, $response->status);
        self::assertSame('GET, HEAD', $response->headers['Allow']);
        self::assertSame(['error' => 'Método no permitido.'], $this->bodyOf($response));
    }

    #[Test]
    public function answersConflictWithTheDuplicatedFields(): void
    {
        $response = $this->handler->toResponse(new DuplicateContactException(ContactField::Email));

        self::assertSame(409, $response->status);
        self::assertSame(['errors' => ['email' => 'Ya existe un contacto con ese email.']], $this->bodyOf($response));
    }

    #[Test]
    public function answersUnprocessableContentWithTheErrorOfEachField(): void
    {
        $response = $this->handler->toResponse(new ContactValidationException(['name' => 'El nombre es obligatorio.']));

        self::assertSame(422, $response->status);
        self::assertSame(['errors' => ['name' => 'El nombre es obligatorio.']], $this->bodyOf($response));
    }

    #[Test]
    public function hidesTheDetailsOfAnUnexpectedErrorAndReportsIt(): void
    {
        $error = new RuntimeException('SQLSTATE: la base no responde');

        $response = $this->handler->toResponse($error);

        self::assertSame(500, $response->status);
        self::assertSame(['error' => 'Ocurrió un error inesperado. Intentá de nuevo en unos minutos.'], $this->bodyOf($response));
        self::assertSame([$error], $this->reportedErrors);
    }

    #[Test]
    public function doesNotReportTheExpectedErrors(): void
    {
        $this->handler->toResponse(new InvalidRequestException());

        self::assertSame([], $this->reportedErrors);
    }

    private function bodyOf(Response $response): array
    {
        return json_decode($response->body, true);
    }
}
