<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes[$path]['GET'] = $handler;
        // HEAD es un GET sin cuerpo (Apache lo descarta). Lo usan, por ejemplo, los monitores de disponibilidad.
        $this->routes[$path]['HEAD'] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes[$path]['POST'] = $handler;
    }

    public function match(Request $request): callable
    {
        $routesOfPath = $this->routesOf($request->path);

        return $routesOfPath[$request->method] ?? throw new MethodNotAllowedException(array_keys($routesOfPath));
    }

    private function routesOf(string $path): array
    {
        return $this->routes[$path] ?? throw new RouteNotFoundException();
    }
}
