<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->get('/api/contacts', fn () => Response::json(200, ['handledBy' => 'list']));
        $this->router->post('/api/contacts', fn () => Response::json(201, ['handledBy' => 'create']));
    }

    #[Test]
    public function callsTheHandlerOfTheMatchingRoute(): void
    {
        $response = $this->router->dispatch(new Request('GET', '/api/contacts'));

        self::assertSame(200, $response->status);
        self::assertSame(['handledBy' => 'list'], json_decode($response->body, true));
    }

    #[Test]
    public function distinguishesRoutesByMethod(): void
    {
        $response = $this->router->dispatch(new Request('POST', '/api/contacts'));

        self::assertSame(201, $response->status);
        self::assertSame(['handledBy' => 'create'], json_decode($response->body, true));
    }

    #[Test]
    public function respondsNotFoundForAnUnknownPath(): void
    {
        $response = $this->router->dispatch(new Request('GET', '/api/unknown'));

        self::assertSame(404, $response->status);
        self::assertSame(['error' => 'Recurso no encontrado.'], json_decode($response->body, true));
    }

    #[Test]
    public function respondsMethodNotAllowedForAKnownPathWithAnotherMethod(): void
    {
        $response = $this->router->dispatch(new Request('DELETE', '/api/contacts'));

        self::assertSame(405, $response->status);
        self::assertSame('GET, POST', $response->headers['Allow']);
        self::assertSame(['error' => 'Método no permitido.'], json_decode($response->body, true));
    }
}
