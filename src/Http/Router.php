<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes[$path]['GET'] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes[$path]['POST'] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $handler = $this->handlerFor($request);

        return $handler($request);
    }

    private function handlerFor(Request $request): callable
    {
        $routesOfPath = $this->routes[$request->path] ?? null;

        if ($routesOfPath === null) {
            return fn () => Response::json(404, ['error' => 'Recurso no encontrado.']);
        }

        return $routesOfPath[$request->method] ?? $this->methodNotAllowed(array_keys($routesOfPath));
    }

    private function methodNotAllowed(array $allowedMethods): callable
    {
        return fn () => Response::json(
            405,
            ['error' => 'Método no permitido.'],
            ['Allow' => implode(', ', $allowedMethods)],
        );
    }
}
