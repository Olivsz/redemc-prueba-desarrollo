<?php

declare(strict_types=1);

namespace App\Http;

use stdClass;

final readonly class Request
{
    private array $headers;

    public function __construct(
        public string $method,
        public string $path,
        private array $query = [],
        array $headers = [],
        private string $body = '',
    ) {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    public static function fromGlobals(): self
    {
        return new self(
            method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
            path: parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
            query: $_GET,
            headers: getallheaders(),
            body: (string) file_get_contents('php://input'),
        );
    }

    public function query(string $name): ?string
    {
        $value = $this->query[$name] ?? null;

        return is_string($value) ? $value : null;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function jsonBody(): array
    {
        $decoded = json_decode($this->body);

        $this->ensureIsAJsonObject($decoded);

        return (array) $decoded;
    }

    public function isSecure(): bool
    {
        return $this->header('X-Forwarded-Proto') === 'https';
    }

    private function ensureIsAJsonObject(mixed $decoded): void
    {
        if (!$decoded instanceof stdClass) {
            throw new InvalidRequestException();
        }
    }
}
