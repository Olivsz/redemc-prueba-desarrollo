<?php

declare(strict_types=1);

namespace App\Http;

use JsonSerializable;

final readonly class Response
{
    private const array JSON_HEADERS = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Cache-Control' => 'no-store',
    ];

    private function __construct(
        public int $status,
        public string $body,
        public array $headers,
    ) {
    }

    public static function json(int $status, array|JsonSerializable $data, array $headers = []): self
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return new self($status, $body, [...self::JSON_HEADERS, ...$headers]);
    }

    public static function html(string $html): self
    {
        return new self(200, $html, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }
}
