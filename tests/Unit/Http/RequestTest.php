<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\InvalidRequestException;
use App\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    #[Test]
    public function readsAQueryParameter(): void
    {
        $request = new Request('GET', '/api/contacts', query: ['page' => '2']);

        self::assertSame('2', $request->query('page'));
        self::assertNull($request->query('missing'));
    }

    #[Test]
    public function ignoresAQueryParameterThatIsNotText(): void
    {
        $request = new Request('GET', '/api/contacts', query: ['page' => ['2']]);

        self::assertNull($request->query('page'));
    }

    #[Test]
    public function readsHeadersRegardlessOfCase(): void
    {
        $request = new Request('POST', '/api/contacts', headers: ['X-CSRF-Token' => 'abc']);

        self::assertSame('abc', $request->header('x-csrf-token'));
        self::assertNull($request->header('missing'));
    }

    #[Test]
    public function decodesAJsonObjectBody(): void
    {
        $request = new Request('POST', '/api/contacts', body: '{"name": "Ana", "phone": ""}');

        self::assertSame(['name' => 'Ana', 'phone' => ''], $request->jsonBody());
    }

    #[Test]
    #[DataProvider('bodiesThatAreNotAJsonObject')]
    public function rejectsABodyThatIsNotAJsonObject(string $body): void
    {
        $request = new Request('POST', '/api/contacts', body: $body);

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('El pedido no tiene un formato válido.');

        $request->jsonBody();
    }

    public static function bodiesThatAreNotAJsonObject(): iterable
    {
        yield 'empty' => [''];
        yield 'broken json' => ['{"name": "Ana"'];
        yield 'plain text' => ['"Ana"'];
        yield 'json list' => ['["Ana"]'];
    }

    #[Test]
    public function isSecureWhenTheProxyForwardsAnHttpsRequest(): void
    {
        self::assertTrue(new Request('GET', '/', headers: ['X-Forwarded-Proto' => 'https'])->isSecure());
        self::assertFalse(new Request('GET', '/')->isSecure());
    }
}
