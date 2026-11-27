<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\MethodNotAllowedException;
use App\Http\Request;
use App\Http\Response;
use App\Http\RouteNotFoundException;
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
    public function findsTheHandlerOfTheMatchingRoute(): void
    {
        $response = $this->handle(new Request('GET', '/api/contacts'));

        self::assertSame(200, $response->status);
        self::assertSame(['handledBy' => 'list'], json_decode($response->body, true));
    }

    #[Test]
    public function distinguishesRoutesByMethod(): void
    {
        $response = $this->handle(new Request('POST', '/api/contacts'));

        self::assertSame(201, $response->status);
        self::assertSame(['handledBy' => 'create'], json_decode($response->body, true));
    }

    #[Test]
    public function answersHeadWithTheHandlerOfGet(): void
    {
        $response = $this->handle(new Request('HEAD', '/api/contacts'));

        self::assertSame(['handledBy' => 'list'], json_decode($response->body, true));
    }

    #[Test]
    public function rejectsAnUnknownPath(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $this->router->match(new Request('GET', '/api/unknown'));
    }

    #[Test]
    public function rejectsAKnownPathWithAnotherMethod(): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->router->match(new Request('DELETE', '/api/contacts'));
    }

    private function handle(Request $request): Response
    {
        $handler = $this->router->match($request);

        return $handler($request);
    }
}
