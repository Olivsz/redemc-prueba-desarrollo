<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Domain\Contact\ContactValidationException;
use App\Http\CsrfProtection;
use App\Http\CsrfToken;
use App\Http\ExceptionHandler;
use App\Http\HttpKernel;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HttpKernelTest extends TestCase
{
    private Router $router;
    private HttpKernel $kernel;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->router = new Router();
        $this->kernel = new HttpKernel(
            $this->router,
            new CsrfProtection(new CsrfToken()),
            new ExceptionHandler(fn () => null),
        );
    }

    #[Test]
    public function returnsTheResponseOfTheRoute(): void
    {
        $this->router->get('/api/contacts', fn () => Response::json(200, ['ok' => true]));

        $response = $this->kernel->handle(new Request('GET', '/api/contacts'));

        self::assertSame(200, $response->status);
    }

    #[Test]
    public function turnsTheExceptionsOfARouteIntoResponses(): void
    {
        $this->router->get('/api/contacts', fn () => throw new ContactValidationException(['name' => 'El nombre es obligatorio.']));

        $response = $this->kernel->handle(new Request('GET', '/api/contacts'));

        self::assertSame(422, $response->status);
    }

    #[Test]
    public function blocksAChangeWithoutCsrfTokenBeforeReachingTheRoute(): void
    {
        $this->router->post('/api/contacts', fn () => throw new RuntimeException('No debería ejecutarse.'));

        $response = $this->kernel->handle(new Request('POST', '/api/contacts'));

        self::assertSame(403, $response->status);
    }

    #[Test]
    public function answersInternalServerErrorForAnUnexpectedError(): void
    {
        $this->router->get('/api/contacts', fn () => throw new RuntimeException('SQLSTATE: la base no responde'));

        $response = $this->kernel->handle(new Request('GET', '/api/contacts'));

        self::assertSame(500, $response->status);
        self::assertStringNotContainsString('SQLSTATE', $response->body);
    }
}
